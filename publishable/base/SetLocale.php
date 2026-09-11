<?php

namespace App\Http\Middleware;

use Closure;
use HeadlessKit\Support\LocaleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set application locale dynamically
     * based on configured supported locales.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $segment = $request->segment(1);
        $supported = LocaleManager::getSupportedLocales();

        if ($segment && in_array($segment, $supported)) {
            $locale = $segment;
        } else {
            $locale = LocaleManager::getDefaultLocale();
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
