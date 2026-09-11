<?php

namespace App\Observers;

use HeadlessKit\Support\LocaleManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentObserver
{
    /**
     * Clear cache for the given model across all configured supported locales.
     */
    protected function clearCache($model): void
    {
        $className = class_basename($model);
        $key = Str::snake($className).'_content';

        foreach (LocaleManager::getSupportedLocales() as $locale) {
            Cache::forget($key.'_'.$locale);
        }
    }

    public function created($model): void
    {
        $this->clearCache($model);
    }

    public function updated($model): void
    {
        $this->clearCache($model);
    }

    public function deleted($model): void
    {
        $this->clearCache($model);
    }

    public function restored($model): void
    {
        $this->clearCache($model);
    }

    public function forceDeleted($model): void
    {
        $this->clearCache($model);
    }
}
