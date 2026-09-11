<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;

class HeadlessLintCommand extends Command
{
    protected $signature = 'headless:lint';

    protected $description = 'Audit all content models, Filament resources, and React pages to enforce headless architectural purity';

    public function handle(Filesystem $files): int
    {
        $this->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('║             Headless Architecture Conformance Linter             ║');
        $this->info('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $violations = [];
        $totalChecked = 0;

        // 1. Audit Content Models
        $modelFiles = $files->glob(app_path('Models/Content/*.php'));
        foreach ($modelFiles as $file) {
            $totalChecked++;
            $className = 'App\\Models\\Content\\'.basename($file, '.php');
            if (! class_exists($className)) {
                require_once $file;
            }

            if (! class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            $traits = class_uses_recursive($className);
            $content = $files->get($file);

            // Trait checks
            $requiredTraits = [
                'App\Models\Concerns\HasSEO',
                'Spatie\Translatable\HasTranslations',
                'App\Models\Concerns\InteractsWithOptimizedMedia',
                'App\Models\Concerns\SerializesLocalizedStrings',
            ];

            foreach ($requiredTraits as $trait) {
                if (! in_array($trait, $traits)) {
                    $violations[] = [
                        'Target' => basename($file),
                        'Type' => 'Model Trait',
                        'Issue' => 'Missing trait: '.class_basename($trait),
                        'Fix' => "Add `use \\{$trait};` to {$className}",
                    ];
                }
            }

            // Observer check
            if (! str_contains($content, 'ObservedBy(ContentObserver::class)')) {
                $violations[] = [
                    'Target' => basename($file),
                    'Type' => 'Model Observer',
                    'Issue' => 'Missing #[ObservedBy(ContentObserver::class)] attribute',
                    'Fix' => 'Add `#[ObservedBy(ContentObserver::class)]` above class declaration',
                ];
            }

            // Translatable property check
            if (! $ref->hasProperty('translatable')) {
                $violations[] = [
                    'Target' => basename($file),
                    'Type' => 'Translatability',
                    'Issue' => 'Missing `public array $translatable` declaration',
                    'Fix' => 'Define translatable attributes array',
                ];
            }
        }

        // 2. Audit Filament Resources
        $resourceFiles = $files->glob(app_path('Filament/Resources/Content/**/*Resource.php'));
        foreach ($resourceFiles as $file) {
            $totalChecked++;
            $content = $files->get($file);

            if (! str_contains($content, 'extends SingletonResource')) {
                $violations[] = [
                    'Target' => basename($file),
                    'Type' => 'Filament Resource',
                    'Issue' => 'Resource does not extend SingletonResource',
                    'Fix' => 'Change `extends Resource` to `extends SingletonResource`',
                ];
            }
        }

        // 3. Audit React Pages
        $pageFiles = $files->glob(resource_path('js/pages/*.tsx'));
        foreach ($pageFiles as $file) {
            $baseName = basename($file, '.tsx');
            if (in_array($baseName, ['Error', 'Sitemap'])) {
                continue;
            }

            $totalChecked++;
            $content = $files->get($file);

            if (! str_contains($content, '.layout = (page') && ! str_contains($content, '.layout = page =>')) {
                $violations[] = [
                    'Target' => basename($file),
                    'Type' => 'React Page',
                    'Issue' => 'Missing standard AppLayout assignment',
                    'Fix' => "Add `{$baseName}.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;`",
                ];
            }
        }

        if (empty($violations)) {
            $this->info("✔ Perfect architecture! Checked {$totalChecked} components with 0 violations found.");
            $this->newLine();

            return self::SUCCESS;
        }

        $this->warn('⚠ Found '.count($violations)." architectural drift issues across {$totalChecked} components:");
        $this->newLine();
        $this->table(['Target File', 'Category', 'Issue Description', 'Recommended Fix'], $violations);
        $this->newLine();

        return self::FAILURE;
    }
}
