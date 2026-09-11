<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Laravel\Prompts\Prompt;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;

class InstallHeadlessKitCommand extends Command
{
    protected $signature = 'headless:install
                            {--force : Overwrite existing files}
                            {--agents-only : Only publish AI agent rules and skills}
                            {--non-interactive : Run without interactive prompts}';

    protected $description = 'Install the Headless CMS & Inertia architecture base files, config, and AI agent rules';

    public function handle(Filesystem $files): int
    {
        $force = (bool) $this->option('force');
        $isInteractive = ! $this->option('non-interactive') && ! $this->option('no-interaction');

        // Prevent Windows terminal errors (such as "The system cannot find the path specified." from /dev/tty inspection)
        if (PHP_OS_FAMILY === 'Windows') {
            Prompt::fallbackWhen(true);

            try {
                $ref = new \ReflectionClass(\Laravel\Prompts\Terminal::class);
                $ref->setStaticPropertyValue('foregroundColor', [204, 204, 204]);
                $ref->setStaticPropertyValue('backgroundColor', [0, 0, 0]);
                $ref->setStaticPropertyValue('trueColorSupport', false);
            } catch (\Throwable $e) {
                // Ignore if reflection fails
            }
        }

        if ($isInteractive) {
            intro('🚀 Filament Headless CMS + Inertia React Starter Kit Installer');

            if ($this->option('agents-only')) {
                $this->call('vendor:publish', [
                    '--provider' => 'HeadlessKit\HeadlessKitServiceProvider',
                    '--tag' => 'headless-agents',
                    '--force' => $force,
                ]);
                $this->syncBoostGuidelines($files);
                outro('✔ AI Agent rules and skills published into .agents/');

                return self::SUCCESS;
            }

            // Interactive Prompts
            $selectedLanguages = multiselect(
                label: 'Select supported languages for this project:',
                options: [
                    'en' => 'English (LTR)',
                    'ar' => 'Arabic (RTL)',
                    'es' => 'Spanish (LTR)',
                    'fr' => 'French (LTR)',
                    'de' => 'German (LTR)',
                    'zh' => 'Chinese (LTR)',
                ],
                default: ['en', 'ar'],
                required: true,
            );

            $defaultLanguage = select(
                label: 'Choose default application language:',
                options: array_combine($selectedLanguages, array_map(fn ($lang) => strtoupper($lang), $selectedLanguages)),
                default: $selectedLanguages[0] ?? 'en',
            );

            $starterPages = multiselect(
                label: 'Select starter pages to scaffold immediately:',
                options: [
                    'Home' => 'Home Page (with Hero, Bento Grid, Stats, Trust Wall, Footer CTA)',
                    'About' => 'About Us (with Story, Mission, Team, Values)',
                    'Services' => 'Services / Offerings (with Grid, Process, FAQs)',
                    'Contact' => 'Contact Us (with Contact Information & Inquiries)',
                ],
                default: ['Home', 'About', 'Contact'],
            );

            $shouldProceed = confirm(
                label: 'Ready to install Headless foundation and configure architecture?',
                default: true,
            );

            if (! $shouldProceed) {
                info('Installation cancelled.');

                return self::SUCCESS;
            }
        }

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'HeadlessKit\HeadlessKitServiceProvider',
            '--tag' => 'headless-config',
            '--force' => $force,
        ]);

        // Publish base classes
        $this->call('vendor:publish', [
            '--provider' => 'HeadlessKit\HeadlessKitServiceProvider',
            '--tag' => 'headless-base',
            '--force' => $force,
        ]);

        // Publish frontend layouts & blocks
        $this->call('vendor:publish', [
            '--provider' => 'HeadlessKit\HeadlessKitServiceProvider',
            '--tag' => 'headless-frontend',
            '--force' => $force,
        ]);

        // Publish agent rules & skills
        $this->call('vendor:publish', [
            '--provider' => 'HeadlessKit\HeadlessKitServiceProvider',
            '--tag' => 'headless-agents',
            '--force' => $force,
        ]);

        // Auto-publish Spatie Media Library migration if not present
        if (empty($files->glob(database_path('migrations/*_create_media_table.php')))) {
            $this->callSilent('vendor:publish', [
                '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
                '--tag' => 'medialibrary-migrations',
            ]);
            $this->line('  <info>✔</info> Published Spatie Media Library migration');
        }

        // Auto-link storage if not linked
        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            $this->callSilent('storage:link');
            $this->line('  <info>✔</info> Created public storage symlink');
        }

        // Update config/headless-kit.php with chosen languages if interactive
        if (isset($selectedLanguages) && isset($defaultLanguage)) {
            $this->updatePackageConfig($files, $selectedLanguages, $defaultLanguage);
        }

        // Synchronize with Laravel Boost
        $this->syncBoostConfig($files);
        $this->syncBoostGuidelines($files);

        // Scaffold selected starter pages
        if (! empty($starterPages)) {
            foreach ($starterPages as $pageName) {
                $this->call('headless:make-page', [
                    'name' => $pageName,
                    '--force' => $force,
                ]);
            }
        }

        $this->newLine();
        $this->info('✔ Headless Kit foundation installed successfully!');
        $this->newLine();
        $this->line('<comment>Recommended Next Steps:</comment>');
        $this->line(' 1. Run the system health doctor: <info>php artisan headless:doctor</info>');
        $this->line(' 2. Run database migrations: <info>php artisan migrate</info>');
        $this->line(' 3. Run the architecture linter: <info>php artisan headless:lint</info>');
        $this->newLine();

        return self::SUCCESS;
    }

    protected function updatePackageConfig(Filesystem $files, array $languages, string $default): void
    {
        $configPath = config_path('headless-kit.php');
        if (! $files->exists($configPath)) {
            return;
        }

        $metadataMap = [
            'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'flag' => 'US'],
            'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'flag' => 'AE'],
            'es' => ['name' => 'Spanish', 'native' => 'Español', 'dir' => 'ltr', 'flag' => 'ES'],
            'fr' => ['name' => 'French', 'native' => 'Français', 'dir' => 'ltr', 'flag' => 'FR'],
            'de' => ['name' => 'German', 'native' => 'Deutsch', 'dir' => 'ltr', 'flag' => 'DE'],
            'zh' => ['name' => 'Chinese', 'native' => '中文', 'dir' => 'ltr', 'flag' => 'CN'],
        ];

        $configuredLocales = [];
        foreach ($languages as $lang) {
            $configuredLocales[$lang] = $metadataMap[$lang] ?? [
                'name' => ucfirst($lang),
                'native' => ucfirst($lang),
                'dir' => 'ltr',
                'flag' => strtoupper($lang),
            ];
        }

        $content = $files->get($configPath);

        // Build clean formatted PHP array code for locales
        $lines = ["[\n"];
        foreach ($configuredLocales as $code => $meta) {
            $lines[] = "        '{$code}' => [\n";
            $lines[] = "            'name' => '{$meta['name']}',\n";
            $lines[] = "            'native' => '{$meta['native']}',\n";
            $lines[] = "            'dir' => '{$meta['dir']}',\n";
            $lines[] = "            'flag' => '{$meta['flag']}',\n";
            $lines[] = "        ],\n";
        }
        $lines[] = '    ]';
        $localesExport = implode('', $lines);

        // Safely replace the 'locales' block using bracket counting
        $startPos = strpos($content, "'locales'");
        if ($startPos !== false) {
            $bracketStart = strpos($content, '[', $startPos);
            if ($bracketStart !== false) {
                $depth = 0;
                $bracketEnd = false;
                $len = strlen($content);
                for ($i = $bracketStart; $i < $len; $i++) {
                    if ($content[$i] === '[') {
                        $depth++;
                    } elseif ($content[$i] === ']') {
                        $depth--;
                        if ($depth === 0) {
                            $bracketEnd = $i;
                            break;
                        }
                    }
                }
                if ($bracketEnd !== false) {
                    $content = substr($content, 0, $bracketStart).$localesExport.substr($content, $bracketEnd + 1);
                }
            }
        }

        // Safely replace 'default_locale' matching everything up to newline
        $content = preg_replace(
            "/('default_locale'\s*=>\s*)(.*?)(\r?\n)/",
            "'default_locale' => '{$default}',\$3",
            $content
        );

        $files->put($configPath, $content);
    }

    protected function syncBoostConfig(Filesystem $files): void
    {
        $boostJsonPath = base_path('boost.json');
        if (! $files->exists($boostJsonPath)) {
            return;
        }

        $boostData = json_decode($files->get($boostJsonPath), true);
        if (! is_array($boostData)) {
            return;
        }

        $requiredPackages = [
            'filament/filament',
            'spatie/laravel-medialibrary',
            'spatie/laravel-translatable',
        ];

        $currentPackages = $boostData['packages'] ?? [];
        foreach ($requiredPackages as $pkg) {
            if (! in_array($pkg, $currentPackages)) {
                $currentPackages[] = $pkg;
            }
        }
        $boostData['packages'] = array_values(array_unique($currentPackages));

        $currentSkills = $boostData['skills'] ?? [];
        if (! in_array('headless-architecture', $currentSkills)) {
            $currentSkills[] = 'headless-architecture';
        }
        $boostData['skills'] = array_values(array_unique($currentSkills));

        $files->put($boostJsonPath, json_encode($boostData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line('  <info>✔</info> Synchronized packages and skills with boost.json');
    }

    protected function syncBoostGuidelines(Filesystem $files): void
    {
        $guidelinesStub = <<<MD

=== filament-headless-kit rules ===

# Filament Headless CMS & Inertia Guidelines

- **Singleton Pages**: Content pages (Home, About, Services, etc.) contain exactly one record in the database. All page resources MUST extend `App\Filament\Resources\SingletonResource` and `App\Filament\Pages\ManageSingletonPage`. Never create list/index tables for content pages.
- **Translatability**: User-facing text must be stored in `json` columns and declared in `public array \$translatable`. Form schemas must be wrapped in `Translations::make('translations')`.
- **Serialization**: Models must use `SerializesLocalizedStrings` so the frontend receives flat strings for the active locale with English fallback.
- **Media**: All images must use Spatie Media Library collections; never store raw upload paths as database strings.
- **Wayfinder Navigation**: Always use Wayfinder route functions (`@/routes` or `@/actions`), never raw hardcoded strings.
- **Pest Testing**: Verify pages with feature tests asserting 200 HTTP status and Inertia component rendering.
MD;

        foreach (['GEMINI.md', 'AGENTS.md'] as $filename) {
            $path = base_path($filename);
            if ($files->exists($path)) {
                $content = $files->get($path);
                if (! str_contains($content, '=== filament-headless-kit rules ===')) {
                    // Try to inject before closing </laravel-boost-guidelines> if present
                    if (str_contains($content, '</laravel-boost-guidelines>')) {
                        $content = str_replace('</laravel-boost-guidelines>', $guidelinesStub."\n\n</laravel-boost-guidelines>", $content);
                    } else {
                        $content .= "\n".$guidelinesStub."\n";
                    }
                    $files->put($path, $content);
                    $this->line("  <info>✔</info> Injected headless guidelines into {$filename}");
                }
            }
        }
    }
}
