<?php

namespace HeadlessKit\Support;

class LocaleManager
{
    /**
     * Get list of supported locale codes (e.g. ['en', 'ar', 'es']).
     *
     * @return array<string>
     */
    public static function getSupportedLocales(): array
    {
        $locales = config('headless-kit.locales', []);

        if (empty($locales)) {
            return config('app.locales', ['en']);
        }

        return array_keys($locales);
    }

    /**
     * Get the default/fallback application locale.
     */
    public static function getDefaultLocale(): string
    {
        return config('headless-kit.default_locale', config('app.locale', 'en'));
    }

    /**
     * Get the fallback locale when a translation is missing or empty.
     */
    public static function getFallbackLocale(): string
    {
        return config('headless-kit.fallback_locale', config('app.fallback_locale', 'en'));
    }

    /**
     * Determine if a given locale (or current locale) has RTL direction.
     */
    public static function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $dir = config("headless-kit.locales.{$locale}.dir");

        if ($dir !== null) {
            return $dir === 'rtl';
        }

        // Fallback standard RTL languages
        return in_array($locale, ['ar', 'fa', 'he', 'ur']);
    }

    /**
     * Get text direction for a given locale ('ltr' or 'rtl').
     */
    public static function getDirection(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * Get all configured locale options with full metadata.
     *
     * @return array<string, array{name: string, native: string, dir: string, flag: string}>
     */
    public static function getLocaleOptions(): array
    {
        return config('headless-kit.locales', [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'dir' => 'ltr',
                'flag' => 'US',
            ],
        ]);
    }

    /**
     * Validate if a given locale string is officially supported.
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::getSupportedLocales());
    }
}
