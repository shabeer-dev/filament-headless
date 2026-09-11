<?php

namespace HeadlessKit\Commands;

use HeadlessKit\Helpers\StubGenerator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeHeadlessPageCommand extends Command
{
    protected $signature = 'headless:make-page
                            {name : The name of the page (e.g. Services, About, Pricing)}
                            {--force : Overwrite existing files}
                            {--no-route : Skip automatically registering route in routes/web.php}
                            {--no-types : Skip appending TypeScript definitions}
                            {--no-test : Skip generating Pest feature test}';

    protected $description = 'Generate a complete 7-layer Headless Singleton Page (Migration, Model, Filament Resource, Controller, React Page, Partials, Types, and Pest Test)';

    public function handle(Filesystem $files): int
    {
        $rawName = trim($this->argument('name'));
        $className = Str::studly($rawName);
        $slug = Str::kebab($rawName);
        $tableName = 'page_'.Str::snake($rawName);
        $title = Str::headline($rawName);

        $force = (bool) $this->option('force');
        $generator = new StubGenerator($files);

        $replacements = [
            'CLASS_NAME' => $className,
            'SLUG' => $slug,
            'TABLE_NAME' => $tableName,
            'TITLE' => $title,
        ];

        $stubDir = __DIR__.'/../../stubs';

        $this->newLine();
        $this->info("🚀 Scaffolding Headless Page: <comment>{$title}</comment> (Table: {$tableName}, Route: /{$slug})");
        $this->newLine();

        // 1. Migration
        $migrationFiles = $files->glob(database_path("migrations/*_create_{$tableName}_table.php"));
        if (empty($migrationFiles)) {
            $timestamp = date('Y_m_d_His');
            $migrationPath = database_path("migrations/{$timestamp}_create_{$tableName}_table.php");
            $generator->write("{$stubDir}/backend/migration.stub", $migrationPath, $replacements, false);
            $this->line('  <info>✔</info> Migration: database/migrations/'.basename($migrationPath));
        } elseif ($force) {
            $migrationPath = $migrationFiles[0];
            $generator->write("{$stubDir}/backend/migration.stub", $migrationPath, $replacements, true);
            $this->line('  <info>✔</info> Overwrote Migration: database/migrations/'.basename($migrationPath));
        } else {
            $this->line('  <comment>↷</comment> Migration already exists: '.basename($migrationFiles[0]));
        }

        // 2. Model
        $modelPath = app_path("Models/Content/Page{$className}.php");
        $generator->write("{$stubDir}/backend/model.stub", $modelPath, $replacements, $force);
        $this->line("  <info>✔</info> Model: app/Models/Content/Page{$className}.php");

        // 3. Filament Singleton Resource & Manage Page
        $resourcePath = app_path("Filament/Resources/Content/Page{$className}/Page{$className}Resource.php");
        $managePagePath = app_path("Filament/Resources/Content/Page{$className}/Pages/ManagePage{$className}.php");
        $generator->write("{$stubDir}/backend/filament-resource.stub", $resourcePath, $replacements, $force);
        $generator->write("{$stubDir}/backend/filament-manage-page.stub", $managePagePath, $replacements, $force);
        $this->line("  <info>✔</info> Filament Resource: app/Filament/Resources/Content/Page{$className}/Page{$className}Resource.php");
        $this->line("  <info>✔</info> Filament Manage Page: app/Filament/Resources/Content/Page{$className}/Pages/ManagePage{$className}.php");

        // 4. Controller
        $controllerPath = app_path("Http/Controllers/Page/{$className}Controller.php");
        $generator->write("{$stubDir}/backend/controller.stub", $controllerPath, $replacements, $force);
        $this->line("  <info>✔</info> Controller: app/Http/Controllers/Page/{$className}Controller.php");

        // 5. Frontend React Page & Partials
        $reactPagePath = resource_path("js/pages/{$className}.tsx");
        $featuresPartialPath = resource_path("js/pages/{$className}/partials/FeaturesSection.tsx");
        $faqPartialPath = resource_path("js/pages/{$className}/partials/FaqSection.tsx");

        $generator->write("{$stubDir}/frontend/react-page.stub", $reactPagePath, $replacements, $force);
        $generator->write("{$stubDir}/frontend/partials/features.stub", $featuresPartialPath, $replacements, $force);
        $generator->write("{$stubDir}/frontend/partials/faq.stub", $faqPartialPath, $replacements, $force);

        $this->line("  <info>✔</info> React Page: resources/js/pages/{$className}.tsx");
        $this->line("  <info>✔</info> React Partial: resources/js/pages/{$className}/partials/FeaturesSection.tsx");
        $this->line("  <info>✔</info> React Partial: resources/js/pages/{$className}/partials/FaqSection.tsx");

        // 6. TypeScript Types
        if (! $this->option('no-types')) {
            $typesPath = resource_path('js/types/content.ts');
            if ($files->exists($typesPath)) {
                $currentTypes = $files->get($typesPath);
                if (! str_contains($currentTypes, "Page{$className}")) {
                    $renderedTypes = $generator->render("{$stubDir}/frontend/types.stub", $replacements);
                    $files->append($typesPath, "\n".$renderedTypes."\n");
                    $this->line('  <info>✔</info> TypeScript Interface appended to: resources/js/types/content.ts');
                }
            }
        }

        // 7. Route Registration
        if (! $this->option('no-route')) {
            $routesPath = base_path('routes/web.php');
            if ($files->exists($routesPath)) {
                $routesContent = $files->get($routesPath);
                $routeStatement = "    Route::get('/{$slug}', Page\\{$className}Controller::class)->name('{$slug}');";

                if (! str_contains($routesContent, "'{$slug}'") && ! str_contains($routesContent, "\"{$slug}\"")) {
                    // Try to inject inside {locale} group
                    if (str_contains($routesContent, "Route::prefix('{locale}')")) {
                        $pattern = "/(Route::prefix\('{locale}'\)[^\{]*\{[^;]*)(Route::get\('\/about',\s*Page\\\\AboutController::class\)->name\('about'\);)/s";
                        if (preg_match($pattern, $routesContent)) {
                            $routesContent = preg_replace(
                                $pattern,
                                "$1$2\n    Route::get('/{$slug}', Page\\{$className}Controller::class)->name('{$slug}');",
                                $routesContent
                            );
                            $files->put($routesPath, $routesContent);
                            $this->line('  <info>✔</info> Route automatically registered in routes/web.php under {locale} group');
                        } else {
                            $this->line("  <comment>ℹ Note:</comment> Add to routes/web.php: <info>{$routeStatement}</info>");
                        }
                    }
                }
            }
        }

        // 8. Pest Feature Test (Laravel Boost Standards)
        if (! $this->option('no-test')) {
            $testPath = base_path("tests/Feature/Pages/{$className}PageTest.php");
            $generator->write("{$stubDir}/backend/pest-test.stub", $testPath, $replacements, $force);
            $this->line("  <info>✔</info> Pest Feature Test: tests/Feature/Pages/{$className}PageTest.php");
        }

        $this->newLine();
        $this->info("✨ Page [{$title}] scaffolded successfully!");
        $this->line('Run migrations and run tests:');
        $this->line('  <comment>php artisan migrate</comment>');
        $this->line("  <comment>php artisan test --filter={$className}PageTest</comment>");
        $this->newLine();

        return self::SUCCESS;
    }
}
