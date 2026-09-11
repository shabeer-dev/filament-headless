<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales & Directionality
    |--------------------------------------------------------------------------
    | Define all locales supported by the application, their display names,
    | native spellings, text directions ('ltr' or 'rtl'), and flags.
    |
    | Adding a new language here automatically registers it across Filament,
    | the locale routing middleware, cache clearing observers, and frontend.
    */
    'locales' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
            'flag' => 'US',
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'dir' => 'rtl',
            'flag' => 'AE',
        ],
        'es' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'dir' => 'ltr',
            'flag' => 'ES',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default & Fallback Locales
    |--------------------------------------------------------------------------
    | The default locale when no locale is requested, and the fallback locale
    | used by SerializesLocalizedStrings when a translated field is empty.
    */
    'default_locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Routing Options
    |--------------------------------------------------------------------------
    | Configure how locale route prefixes are structured.
    */
    'routing' => [
        // Automatically redirect visitors at root '/' to their preferred browser language
        'auto_detect_browser_locale' => true,
        // The route parameter name used for locale matching
        'route_parameter' => 'locale',
        // Regex pattern for validating route locale prefix
        'route_pattern' => '[a-zA-Z]{2}',
    ],
];
