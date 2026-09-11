<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HeadlessDoctorCommand extends Command
{
    protected $signature = 'headless:doctor';

    protected $description = 'Run system, environment, and Laravel Boost diagnostics to ensure the Headless architecture is healthy';

    public function handle(Filesystem $files): int
    {
        $this->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('║       Headless Kit Health, Diagnostics & Boost Harmonization     ║');
        $this->info('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $checks = [];
        $hasErrors = false;

        // 1. PHP Version
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.4.0', '>=');
        $checks[] = [
            'Category' => 'Environment',
            'Check' => 'PHP Version (>= 8.4)',
            'Status' => $phpOk ? '<info>PASS</info>' : '<error>FAIL</error>',
            'Details' => "Current: {$phpVersion}",
        ];
        if (! $phpOk) {
            $hasErrors = true;
        }

        // 2. PHP Extensions
        $requiredExtensions = ['intl', 'exif', 'fileinfo', 'pdo'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $checks[] = [
                'Category' => 'PHP Extensions',
                'Check' => "Extension: {$ext}",
                'Status' => $loaded ? '<info>PASS</info>' : '<error>FAIL</error>',
                'Details' => $loaded ? 'Loaded' : 'Missing (Required for CMS & Media)',
            ];
            if (! $loaded) {
                $hasErrors = true;
            }
        }

        // Image driver check
        $hasImageDriver = extension_loaded('gd') || extension_loaded('imagick');
        $checks[] = [
            'Category' => 'PHP Extensions',
            'Check' => 'Image Driver (gd / imagick)',
            'Status' => $hasImageDriver ? '<info>PASS</info>' : '<error>FAIL</error>',
            'Details' => $hasImageDriver ? 'WebP conversion supported' : 'Missing gd and imagick',
        ];

        // 3. Storage Symlink
        $storageLinked = is_link(public_path('storage')) || is_dir(public_path('storage'));
        $checks[] = [
            'Category' => 'Filesystem',
            'Check' => 'Public Storage Symlink',
            'Status' => $storageLinked ? '<info>PASS</info>' : '<comment>WARN</comment>',
            'Details' => $storageLinked ? 'public/storage is linked' : 'Run: php artisan storage:link',
        ];

        // 4. Database & Media Table
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        $checks[] = [
            'Category' => 'Database',
            'Check' => 'Database Connection',
            'Status' => $dbConnected ? '<info>PASS</info>' : '<error>FAIL</error>',
            'Details' => $dbConnected ? 'Connected successfully' : 'Unable to connect to database',
        ];

        if ($dbConnected) {
            $hasMediaTable = Schema::hasTable('media');
            $checks[] = [
                'Category' => 'Database',
                'Check' => 'Spatie Media Table',
                'Status' => $hasMediaTable ? '<info>PASS</info>' : '<comment>WARN</comment>',
                'Details' => $hasMediaTable ? 'Table `media` exists' : 'Run media-library migration',
            ];
        }

        // 5. Wayfinder TypeScript Routes
        $wayfinderRoutesExist = $files->exists(resource_path('js/routes/index.ts'));
        $checks[] = [
            'Category' => 'Frontend',
            'Check' => 'Wayfinder TypeScript Routes',
            'Status' => $wayfinderRoutesExist ? '<info>PASS</info>' : '<comment>WARN</comment>',
            'Details' => $wayfinderRoutesExist ? 'Generated at js/routes/index.ts' : 'Run: php artisan wayfinder:generate',
        ];

        // 6. Laravel Boost Integration
        $boostJsonPath = base_path('boost.json');
        if ($files->exists($boostJsonPath)) {
            $boostData = json_decode($files->get($boostJsonPath), true) ?? [];
            $hasSkill = in_array('headless-architecture', $boostData['skills'] ?? []);
            $checks[] = [
                'Category' => 'Laravel Boost',
                'Check' => 'Boost Skills Sync (headless-architecture)',
                'Status' => $hasSkill ? '<info>PASS</info>' : '<comment>WARN</comment>',
                'Details' => $hasSkill ? 'Tracked in boost.json' : 'Run: php artisan headless:install',
            ];
        }

        // 7. Testing (Pest)
        $hasPest = $files->exists(base_path('tests/Pest.php')) || $files->exists(base_path('vendor/bin/pest'));
        $checks[] = [
            'Category' => 'Testing',
            'Check' => 'Pest Testing Framework',
            'Status' => $hasPest ? '<info>PASS</info>' : '<comment>INFO</comment>',
            'Details' => $hasPest ? 'Pest configured' : 'Pest not detected (Boost standard)',
        ];

        // 8. Code Style (Pint)
        $hasPint = $files->exists(base_path('vendor/bin/pint'));
        $checks[] = [
            'Category' => 'Code Style',
            'Check' => 'Laravel Pint',
            'Status' => $hasPint ? '<info>PASS</info>' : '<comment>WARN</comment>',
            'Details' => $hasPint ? 'Pint available' : 'Install laravel/pint for Boost compliance',
        ];

        $this->table(['Category', 'Check', 'Status', 'Details'], $checks);
        $this->newLine();

        if ($hasErrors) {
            $this->error('✘ Some critical requirements failed. Please resolve them before deploying to production.');

            return self::FAILURE;
        }

        $this->info('✔ System diagnostics look great! Your headless architecture environment is healthy.');
        $this->newLine();

        return self::SUCCESS;
    }
}
