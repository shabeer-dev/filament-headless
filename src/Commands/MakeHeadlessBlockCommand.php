<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeHeadlessBlockCommand extends Command
{
    protected $signature = 'headless:make-block
                            {name : The name of the block (e.g. Testimonials, VideoCallout, PricingTable)}
                            {--force : Overwrite existing block files}';

    protected $description = 'Generate a new modular Filament Builder block schema and matching React block component';

    public function handle(Filesystem $files): int
    {
        $rawName = trim($this->argument('name'));
        $className = Str::studly($rawName);
        $blockKey = Str::snake($rawName);
        $title = Str::headline($rawName);

        $force = (bool) $this->option('force');

        // 1. Filament Block Schema Class
        $filamentBlockDir = app_path('Filament/Blocks');
        $files->ensureDirectoryExists($filamentBlockDir);
        $filamentBlockPath = "{$filamentBlockDir}/{$className}Block.php";

        if ($files->exists($filamentBlockPath) && ! $force) {
            $this->error("Filament block already exists at {$filamentBlockPath}. Use --force to overwrite.");

            return self::FAILURE;
        }

        $filamentStub = <<<PHP
<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;

class {$className}Block
{
    public static function make(): Block
    {
        return Block::make('{$blockKey}')
            ->label('{$title}')
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                TextInput::make('title')->label('Heading')->required(),
                Textarea::make('description')->label('Description')->rows(3),
                Repeater::make('items')
                    ->label('Items')
                    ->schema([
                        TextInput::make('title')->required(),
                        Textarea::make('description')->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
PHP;

        $files->put($filamentBlockPath, $filamentStub);
        $this->line("  <info>✔</info> Filament Block: app/Filament/Blocks/{$className}Block.php");

        // 2. React Block Component
        $reactBlockDir = resource_path('js/components/blocks');
        $files->ensureDirectoryExists($reactBlockDir);
        $reactBlockPath = "{$reactBlockDir}/{$className}Block.tsx";

        if ($files->exists($reactBlockPath) && ! $force) {
            $this->error("React block already exists at {$reactBlockPath}. Use --force to overwrite.");

            return self::FAILURE;
        }

        $reactStub = <<<TSX
import React from 'react';

export default function {$className}Block({ data }: { data: any }) {
    if (!data) return null;

    return (
        <section className="py-20 bg-background border-b border-outline/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                {data.title && (
                    <h2 className="text-3xl sm:text-4xl font-bold text-text-white mb-4">
                        {data.title}
                    </h2>
                )}
                {data.description && (
                    <p className="max-w-2xl mx-auto text-on-surface-variant text-base sm:text-lg mb-12">
                        {data.description}
                    </p>
                )}

                {data.items && data.items.length > 0 && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                        {data.items.map((item: any, idx: number) => (
                            <div key={idx} className="p-6 rounded-xl bg-surface border border-outline/15">
                                <h3 className="text-lg font-bold text-text-white mb-2">{item.title}</h3>
                                <p className="text-sm text-on-surface-variant">{item.description}</p>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
TSX;

        $files->put($reactBlockPath, $reactStub);
        $this->line("  <info>✔</info> React Block: resources/js/components/blocks/{$className}Block.tsx");

        $this->newLine();
        $this->info("✨ Block [{$title}] created successfully!");
        $this->newLine();
        $this->line('<comment>Next Steps:</comment>');
        $this->line(" 1. Add <info>{$className}Block::make()</info> to your Builder catalog schema.");
        $this->line(' 2. In <info>resources/js/components/blocks/BlockRenderer.tsx</info>, import and register:');
        $this->line("    <comment>'{$blockKey}': {$className}Block</comment>");
        $this->newLine();

        return self::SUCCESS;
    }
}
