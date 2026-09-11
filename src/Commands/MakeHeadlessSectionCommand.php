<?php

namespace HeadlessKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeHeadlessSectionCommand extends Command
{
    protected $signature = 'headless:make-section
                            {page : The page name (e.g. Home, About, Services)}
                            {name : The section name (e.g. Testimonials, PricingTable, Team)}
                            {--force : Overwrite existing partial component}';

    protected $description = 'Scaffold a modular section partial component and Filament tab schema snippet for an existing page';

    public function handle(Filesystem $files): int
    {
        $pageName = Str::studly(trim($this->argument('page')));
        $rawSection = trim($this->argument('name'));
        $sectionClass = Str::studly($rawSection);
        $sectionField = Str::snake($rawSection);
        $sectionTitle = Str::headline($rawSection);

        $targetPartial = resource_path("js/pages/{$pageName}/partials/{$sectionClass}Section.tsx");
        $files->ensureDirectoryExists(dirname($targetPartial));

        if ($files->exists($targetPartial) && ! $this->option('force')) {
            $this->error("Partial component already exists at: {$targetPartial}. Use --force to overwrite.");

            return self::FAILURE;
        }

        $componentStub = <<<TSX
import Section from '@/components/ui/Section';
import SectionHeader from '@/components/ui/SectionHeader';
import type { Page{$pageName} } from '@/types/content';

export default function {$sectionClass}Section({ content }: { content: Page{$pageName} }) {
    return (
        <Section className="py-20 bg-surface-container-low border-t border-outline/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    title="{$sectionTitle}"
                    description="Custom {$sectionTitle} description."
                    className="mb-12 text-center"
                />

                {/* Section Content */}
                <div className="rounded-xl bg-surface border border-outline/15 p-8 text-center text-on-surface-variant">
                    <p>{$sectionTitle} content renders here.</p>
                </div>
            </div>
        </Section>
    );
}
TSX;

        $files->put($targetPartial, $componentStub);

        $this->newLine();
        $this->info("✔ Created React Partial: <comment>resources/js/pages/{$pageName}/partials/{$sectionClass}Section.tsx</comment>");
        $this->newLine();

        $this->line("<info>Filament Tab Schema Snippet</info> (Add to <comment>Page{$pageName}Resource.php</comment>):");
        $this->line('--------------------------------------------------------------------------------');
        $this->line("Tab::make('{$sectionTitle}')->schema([");
        $this->line("    TextInput::make('{$sectionField}_title')->label('Section Heading'),");
        $this->line("    Forms\Components\Textarea::make('{$sectionField}_description')->label('Section Description')->rows(3),");
        $this->line("    Forms\Components\Repeater::make('{$sectionField}_items')->label('Items')->schema([");
        $this->line("        TextInput::make('title')->required(),");
        $this->line("        Forms\Components\Textarea::make('description')->rows(2),");
        $this->line('    ])->columns(2)->columnSpanFull(),');
        $this->line(']),');
        $this->line('--------------------------------------------------------------------------------');
        $this->newLine();

        return self::SUCCESS;
    }
}
