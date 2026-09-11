<?php

namespace App\Filament\Blocks;

use App\Filament\Helpers\FormFields;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

class BlockCatalog
{
    /**
     * Return all default modular blocks for use in Filament Builder fields.
     *
     * @return array<Block>
     */
    public static function all(): array
    {
        return [
            self::hero(),
            self::stats(),
            self::bentoGrid(),
            self::overview(),
            self::faq(),
            self::cta(),
        ];
    }

    public static function hero(): Block
    {
        return Block::make('hero')
            ->label('Hero Section')
            ->icon('heroicon-o-sparkles')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')->label('Hero Title')->required(),
                    TextInput::make('highlighted')->label('Highlighted Words'),
                    TextInput::make('label')->label('Badge / Pill Label'),
                    Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                    TextInput::make('cta_primary')->label('Primary Button Text'),
                    TextInput::make('cta_primary_route')->label('Primary Button Route (e.g. contact)'),
                    TextInput::make('cta_secondary')->label('Secondary Button Text'),
                    TextInput::make('cta_secondary_route')->label('Secondary Button Route'),
                    FileUpload::make('background_image')
                        ->label('Hero Media Background')
                        ->image()
                        ->disk('public')
                        ->directory('blocks/hero')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function stats(): Block
    {
        return Block::make('stats')
            ->label('Stats Counter Band')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                TextInput::make('headline')->label('Optional Heading'),
                Repeater::make('items')
                    ->label('Metrics')
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->schema([
                        TextInput::make('label')->label('Label')->required(),
                        TextInput::make('value')->label('Display Value (e.g. 50M)')->required(),
                        TextInput::make('target')->label('Target Number (for animation)')->numeric(),
                        TextInput::make('suffix')->label('Suffix (e.g. +, %)'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function bentoGrid(): Block
    {
        return Block::make('bento_grid')
            ->label('Bento / Features Grid')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                TextInput::make('title')->label('Section Heading')->required(),
                Textarea::make('description')->label('Section Description')->rows(2),
                Repeater::make('cards')
                    ->label('Grid Cards')
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->schema([
                        TextInput::make('title')->label('Card Title')->required(),
                        Textarea::make('description')->label('Card Description')->rows(2)->required(),
                        TextInput::make('badge')->label('Badge Text'),
                        FormFields::icon('icon')->label('Material Icon'),
                        FormFields::colSpanSelect('col_span')->label('Column Span'),
                        FileUpload::make('image')
                            ->label('Card Background / Visual')
                            ->image()
                            ->disk('public')
                            ->directory('blocks/bento')
                            ->visibility('public'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function overview(): Block
    {
        return Block::make('overview')
            ->label('Split Overview / Two-Column')
            ->icon('heroicon-o-view-columns')
            ->schema([
                TextInput::make('title')->label('Heading')->required(),
                TextInput::make('subtitle')->label('Subtitle'),
                Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
                Radio::make('layout')
                    ->label('Layout Alignment')
                    ->options(['media_left' => 'Media Left, Text Right', 'media_right' => 'Text Left, Media Right'])
                    ->default('media_right')
                    ->inline(),
                FileUpload::make('image')
                    ->label('Section Image')
                    ->image()
                    ->disk('public')
                    ->directory('blocks/overview')
                    ->visibility('public')
                    ->columnSpanFull(),
            ]);
    }

    public static function faq(): Block
    {
        return Block::make('faq')
            ->label('FAQ Accordion')
            ->icon('heroicon-o-question-mark-circle')
            ->schema([
                TextInput::make('title')->label('Section Heading')->default('Frequently Asked Questions'),
                Textarea::make('description')->label('Section Description')->rows(2),
                Repeater::make('items')
                    ->label('Questions & Answers')
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                    ->schema([
                        TextInput::make('question')->label('Question')->required(),
                        Textarea::make('answer')->label('Answer')->rows(3)->required(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public static function cta(): Block
    {
        return Block::make('cta')
            ->label('Call To Action Banner')
            ->icon('heroicon-o-megaphone')
            ->schema([
                TextInput::make('title')->label('Heading')->required(),
                TextInput::make('button_text')->label('Button Text')->required(),
                TextInput::make('route')->label('Target Route (e.g. contact)')->required(),
                FileUpload::make('background_image')
                    ->label('Background Image')
                    ->image()
                    ->disk('public')
                    ->directory('blocks/cta')
                    ->visibility('public'),
            ]);
    }
}
