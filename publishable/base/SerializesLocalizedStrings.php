<?php

namespace App\Models\Concerns;

trait SerializesLocalizedStrings
{
    /**
     * Override toArray to ensure translatable fields are serialized as flat strings
     * in the current application locale, rather than as a nested array of translations.
     *
     * Falls back to the configured fallback_locale when the current locale's
     * translation is missing or empty.
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        if (method_exists($this, 'getTranslatableAttributes')) {
            $locale = app()->getLocale();
            $fallbackLocale = config('app.fallback_locale', 'en');

            foreach ($this->getTranslatableAttributes() as $field) {
                if (! array_key_exists($field, $attributes)) {
                    continue;
                }

                $value = $this->getTranslation($field, $locale, true);

                if ($this->isEmptyTranslation($value) && $locale !== $fallbackLocale) {
                    $value = $this->getTranslation($field, $fallbackLocale, false);
                }

                $attributes[$field] = $value;
            }
        }

        return $attributes;
    }

    private function isEmptyTranslation(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_array($value) && count($value) === 0) {
            return true;
        }

        return false;
    }
}
