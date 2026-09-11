<?php

namespace HeadlessKit;

use HeadlessKit\Commands\HeadlessAgentContextCommand;
use HeadlessKit\Commands\HeadlessDoctorCommand;
use HeadlessKit\Commands\HeadlessLintCommand;
use HeadlessKit\Commands\InstallHeadlessKitCommand;
use HeadlessKit\Commands\MakeHeadlessBlockCommand;
use HeadlessKit\Commands\MakeHeadlessPageCommand;
use HeadlessKit\Commands\MakeHeadlessSectionCommand;
use Illuminate\Support\ServiceProvider;

class HeadlessKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/headless-kit.php', 'headless-kit');

        // Register CLI generator and tooling commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallHeadlessKitCommand::class,
                HeadlessDoctorCommand::class,
                MakeHeadlessPageCommand::class,
                MakeHeadlessSectionCommand::class,
                MakeHeadlessBlockCommand::class,
                HeadlessLintCommand::class,
                HeadlessAgentContextCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/headless-kit.php' => config_path('headless-kit.php'),
            ], 'headless-config');

            // Publish base backend infrastructure
            $this->publishes([
                __DIR__.'/../publishable/base/SingletonResource.php' => app_path('Filament/Resources/SingletonResource.php'),
                __DIR__.'/../publishable/base/ManageSingletonPage.php' => app_path('Filament/Pages/ManageSingletonPage.php'),
                __DIR__.'/../publishable/base/FormFields.php' => app_path('Filament/Helpers/FormFields.php'),
                __DIR__.'/../publishable/base/ContentPageController.php' => app_path('Http/Controllers/ContentPageController.php'),
                __DIR__.'/../publishable/base/SetLocale.php' => app_path('Http/Middleware/SetLocale.php'),
                __DIR__.'/../publishable/base/HasSEO.php' => app_path('Models/Concerns/HasSEO.php'),
                __DIR__.'/../publishable/base/InteractsWithOptimizedMedia.php' => app_path('Models/Concerns/InteractsWithOptimizedMedia.php'),
                __DIR__.'/../publishable/base/SerializesLocalizedStrings.php' => app_path('Models/Concerns/SerializesLocalizedStrings.php'),
                __DIR__.'/../publishable/base/SeoMetadata.php' => app_path('Models/SeoMetadata.php'),
                __DIR__.'/../publishable/base/ContentObserver.php' => app_path('Observers/ContentObserver.php'),
                __DIR__.'/../publishable/base/Blocks/BlockCatalog.php' => app_path('Filament/Blocks/BlockCatalog.php'),
            ], 'headless-base');

            // Publish frontend essentials & block renderer
            $this->publishes([
                __DIR__.'/../publishable/frontend/AppLayout.tsx' => resource_path('js/layouts/AppLayout.tsx'),
                __DIR__.'/../publishable/frontend/SeoMeta.tsx' => resource_path('js/components/SeoMeta.tsx'),
                __DIR__.'/../publishable/frontend/base-content.ts' => resource_path('js/types/headless-content.ts'),
                __DIR__.'/../publishable/frontend/blocks' => resource_path('js/components/blocks'),
            ], 'headless-frontend');

            // Publish AI Agent rules and skills
            $this->publishes([
                __DIR__.'/../publishable/agents/rules/headless-architecture.md' => base_path('.agents/rules/headless-architecture.md'),
                __DIR__.'/../publishable/agents/skills/headless-architecture/SKILL.md' => base_path('.agents/skills/headless-architecture/SKILL.md'),
                __DIR__.'/../publishable/agents/skills/headless-page-workflow/SKILL.md' => base_path('.agents/skills/headless-page-workflow/SKILL.md'),
            ], 'headless-agents');
        }
    }
}
