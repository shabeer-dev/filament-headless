<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class HeadlessAgentContextCommand extends Command
{
    protected $signature = 'headless:agent-context
                            {--output=.agents/headless-context.md : Output markdown file path}';

    protected $description = 'Generate an up-to-date Markdown schema and route digest for AI agents to prevent architectural drift';

    public function handle(Filesystem $files): int
    {
        $outputPath = base_path($this->option('output'));
        $files->ensureDirectoryExists(dirname($outputPath));

        $this->info('Scanning headless architecture and compiling AI context digest...');

        $models = [];
        $modelFiles = $files->glob(app_path('Models/Content/*.php'));

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\Content\\'.basename($file, '.php');
            if (! class_exists($className)) {
                require_once $file;
            }

            if (! class_exists($className)) {
                continue;
            }

            $instance = new $className;
            $table = $instance->getTable();
            $translatable = property_exists($instance, 'translatable') ? $instance->translatable : [];
            $casts = method_exists($instance, 'getCasts') ? $instance->getCasts() : [];

            // Get media collections if available
            $collections = [];
            if (method_exists($instance, 'registerMediaCollections')) {
                $instance->registerMediaCollections();
                foreach ($instance->mediaCollections ?? [] as $col) {
                    $collections[] = $col->name;
                }
            }

            $models[] = [
                'class' => class_basename($className),
                'table' => $table,
                'translatable' => $translatable,
                'casts' => array_keys($casts),
                'media' => $collections,
            ];
        }

        // Build Markdown Output
        $timestamp = date('Y-m-d H:i:s');
        $md = "# 🗺️ Headless Architecture Ground-Truth Digest\n\n";
        $md .= "> Generated on: `{$timestamp}` via `php artisan headless:agent-context`.\n";
        $md .= "> This file provides AI agents with the authoritative structural schema of content pages.\n\n";

        $md .= "## 1. Registered Content Models & Singletons\n\n";

        foreach ($models as $m) {
            $md .= "### `{$m['class']}` (Table: `{$m['table']}`)\n";
            $md .= '- **Translatable Fields:** `'.implode('`, `', $m['translatable'])."`\n";
            if (! empty($m['casts'])) {
                $md .= '- **Array/JSON Casts:** `'.implode('`, `', $m['casts'])."`\n";
            }
            if (! empty($m['media'])) {
                $md .= '- **Spatie Media Collections:** `'.implode('`, `', $m['media'])."`\n";
            }
            $md .= "\n";
        }

        $md .= "## 2. Universal Architectural Rules\n";
        $md .= "1. **Singleton Form Only**: Never create Filament table views for content models. Always use `ManageSingletonPage` mounting record ID `1`.\n";
        $md .= "2. **JSON Translatable Columns**: All translatable attributes in database migrations must be `json`.\n";
        $md .= "3. **Auto-Serialization**: Translatable fields are automatically flattened by `SerializesLocalizedStrings` for the active locale. React expects flat strings, not multilingual dictionaries.\n";
        $md .= "4. **Media Bindings**: Media is handled via Spatie Media Library collections; never store upload file paths in database strings.\n";
        $md .= "5. **Type-Safe Routing**: Use Laravel Wayfinder route imports (`@/routes`), never raw hardcoded URL strings.\n";

        $files->put($outputPath, $md);

        $this->newLine();
        $this->info("✔ AI Agent Context generated at: <comment>{$outputPath}</comment>");
        $this->newLine();

        return self::SUCCESS;
    }
}
