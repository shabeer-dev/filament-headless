<?php

namespace App\Filament\Helpers;

use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use Happenv\FilamentTranslatable\Forms\Component\Translations;

class FormFields
{
    public static function routeSelect(string $name, string $label = 'Route'): Select
    {
        return Select::make($name)->label($label)->options([
            'home' => 'Home Page',
            'about' => 'About Page',
            'contact' => 'Contact Page',
            'services' => 'Services Page',
        ])->searchable();
    }

    public static function icon(string $name = 'icon'): TextInput
    {
        return TextInput::make($name)
            ->placeholder('e.g. check_circle, star, shield')
            ->helperText('Material Symbols icon name (browse at fonts.google.com/icons)');
    }

    public static function colSpanSelect(string $name = 'col_span'): Select
    {
        return Select::make($name)->options([
            '4' => '1/3 Width (4 Cols)',
            '6' => 'Half Width (6 Cols)',
            '8' => '2/3 Width (8 Cols)',
            '12' => 'Full Width (12 Cols)',
        ])->default('4');
    }

    public static function heroSectionTab(): Tab
    {
        return Tab::make('Hero Section')->schema([
            TextInput::make('hero_title')->label('Hero Title'),
            TextInput::make('hero_highlighted')->label('Hero Highlighted'),
            TextInput::make('hero_label')->label('Hero Label'),
            Forms\Components\Textarea::make('hero_description')->label('Hero Description')->rows(3)->columnSpanFull(),
            TextInput::make('hero_cta_primary')->label('Primary Action Text'),
            TextInput::make('hero_cta_primary_route')->label('Primary Action Route (e.g. contact)'),
            TextInput::make('hero_cta_secondary')->label('Secondary Action Text'),
            TextInput::make('hero_cta_secondary_route')->label('Secondary Action Route'),
            Forms\Components\SpatieMediaLibraryFileUpload::make('hero')
                ->disk('public')
                ->visibility('public')
                ->dehydrated(false)
                ->collection('hero')
                ->label('Hero Background Image/Video')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function overviewTab(): Tab
    {
        return Tab::make('Overview')->schema([
            TextInput::make('overview_title')->label('Heading'),
            TextInput::make('overview_subtitle')->label('Subtitle'),
            Forms\Components\Textarea::make('overview_description')->label('Description')->rows(3)->columnSpanFull(),
            Fieldset::make('Right Block Configuration')->schema([
                Forms\Components\Radio::make('overview_right_block_type')
                    ->label('Content Type')
                    ->options([
                        'none' => 'None',
                        'image' => 'Image',
                        'content' => 'Content Card',
                    ])
                    ->default('none')
                    ->inline()
                    ->reactive()
                    ->columnSpanFull(),
                TextInput::make('overview_right_block_title')
                    ->visible(fn (Get $get) => $get('overview_right_block_type') === 'content')
                    ->label('Card Title'),
                self::icon('overview_right_block_icon')
                    ->visible(fn (Get $get) => $get('overview_right_block_type') === 'content')
                    ->label('Card Icon'),
                Forms\Components\Textarea::make('overview_right_block_description')
                    ->visible(fn (Get $get) => $get('overview_right_block_type') === 'content')
                    ->label('Card Description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('overview')
                    ->disk('public')
                    ->visibility('public')
                    ->dehydrated(false)
                    ->visible(fn (Get $get) => $get('overview_right_block_type') === 'image')
                    ->collection('overview')
                    ->label('Overview Image')
                    ->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function footerCtaTab(): Tab
    {
        return Tab::make('Footer CTA')->schema([
            TextInput::make('footer_cta_title')->label('Section Heading')->columnSpanFull(),
            TextInput::make('footer_cta_button')->label('Button Text'),
            TextInput::make('footer_cta_route')->label('Button Target Route (e.g. contact)'),
            Forms\Components\SpatieMediaLibraryFileUpload::make('footer_cta_bg')
                ->disk('public')
                ->visibility('public')
                ->dehydrated(false)
                ->collection('footer_cta_bg')
                ->label('Background Image')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function seoSection(): Section
    {
        return Section::make('SEO & Social Preview Settings')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                Group::make()->relationship('seo')->schema([
                    Translations::make('translations')->schema([
                        TextInput::make('title')
                            ->label('Meta Title')
                            ->live(onBlur: true)
                            ->helperText('50-60 characters recommended.'),
                        Forms\Components\Textarea::make('description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->live(onBlur: true)
                            ->helperText('150-160 characters recommended.'),
                        Forms\Components\TagsInput::make('keywords')->label('Keywords'),
                    ]),
                    Forms\Components\FileUpload::make('og_image')
                        ->label('OpenGraph / Social Image')
                        ->image()
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->live(),

                    // Live Google SERP Preview
                    Placeholder::make('serp_preview')
                        ->label('Google Search Preview (Live Simulation)')
                        ->content(function (Get $get): HtmlString {
                            $title = $get('title') ?? config('app.name', 'Site Title');
                            $desc = $get('description') ?? 'Search engine description preview will appear here as you type in the meta description field...';
                            $url = url('/');
                            $titleLen = mb_strlen($title);
                            $descLen = mb_strlen($desc);

                            $titleBadge = ($titleLen >= 45 && $titleLen <= 65)
                                ? '<span style="color:#10b981;font-size:11px;font-weight:600;">Optimal ('.$titleLen.' chars)</span>'
                                : '<span style="color:#f59e0b;font-size:11px;font-weight:600;">'.$titleLen.' chars (50-60 recommended)</span>';

                            $descBadge = ($descLen >= 120 && $descLen <= 165)
                                ? '<span style="color:#10b981;font-size:11px;font-weight:600;">Optimal ('.$descLen.' chars)</span>'
                                : '<span style="color:#f59e0b;font-size:11px;font-weight:600;">'.$descLen.' chars (150-160 recommended)</span>';

                            return new HtmlString('
                                <div style="background:#ffffff;color:#202124;padding:16px;border-radius:8px;border:1px solid #dadce0;font-family:Arial,sans-serif;max-width:600px;">
                                    <div style="font-size:12px;color:#202124;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                                        <span style="display:inline-block;width:16px;height:16px;background:#4285f4;border-radius:50%;text-align:center;line-height:16px;color:#fff;font-size:10px;font-weight:bold;">G</span>
                                        <span>'.$url.'</span>
                                    </div>
                                    <div style="font-size:18px;color:#1a0dab;font-weight:normal;line-height:1.3;margin-bottom:4px;text-decoration:none;">
                                        '.htmlspecialchars($title).'
                                    </div>
                                    <div style="font-size:13px;color:#4d5156;line-height:1.4;">
                                        '.htmlspecialchars($desc).'
                                    </div>
                                    <div style="margin-top:10px;display:flex;gap:12px;border-top:1px solid #f1f3f4;padding-top:8px;">
                                        <div>Title: '.$titleBadge.'</div>
                                        <div>Description: '.$descBadge.'</div>
                                    </div>
                                </div>
                            ');
                        })->columnSpanFull(),

                    // Live Social Card Preview
                    Placeholder::make('social_preview')
                        ->label('Social Card (OpenGraph / Twitter / Facebook) Preview')
                        ->content(function (Get $get): HtmlString {
                            $title = $get('title') ?? config('app.name', 'Site Title');
                            $desc = $get('description') ?? 'Social card description preview will display here.';
                            $domain = parse_url(config('app.url', 'https://example.com'), PHP_URL_HOST) ?? 'example.com';
                            $img = $get('og_image');
                            $imgUrl = $img ? asset('storage/'.$img) : 'https://placehold.co/1200x630/1e1f22/c9a84c?text=OpenGraph+Image';

                            return new HtmlString('
                                <div style="background:#18181b;color:#f4f4f5;border-radius:12px;border:1px solid #3f3f46;overflow:hidden;max-width:520px;font-family:system-ui,-apple-system,sans-serif;">
                                    <div style="height:240px;background:#09090b;background-image:url('.$imgUrl.');background-size:cover;background-position:center;"></div>
                                    <div style="padding:14px 16px;">
                                        <div style="font-size:11px;color:#a1a1aa;text-transform:uppercase;font-weight:600;margin-bottom:4px;">'.$domain.'</div>
                                        <div style="font-size:16px;font-weight:700;color:#ffffff;line-height:1.3;margin-bottom:4px;">'.htmlspecialchars($title).'</div>
                                        <div style="font-size:13px;color:#a1a1aa;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">'.htmlspecialchars($desc).'</div>
                                    </div>
                                </div>
                            ');
                        })->columnSpanFull(),
                ]),
            ])->collapsed()->columnSpanFull();
    }
}
