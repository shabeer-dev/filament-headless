<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

abstract class ContentPageController extends Controller
{
    /**
     * The Eloquent model class to query for page content.
     */
    abstract protected function model(): string;

    /**
     * The Inertia component name to render (e.g. 'Home', 'About').
     */
    abstract protected function component(): string;

    /**
     * Hook to transform the cached content before passing to the frontend.
     */
    protected function transformContent(?array $content): ?array
    {
        return $content;
    }

    public function __invoke(): Response
    {
        $key = Str::snake(class_basename($this->model())).'_content_'.app()->getLocale();

        $content = Cache::rememberForever($key, function () {
            $data = ($this->model())::with(['media', 'seo'])->first();

            return $data?->toArray();
        });

        $content = $this->transformContent($content);

        return Inertia::render($this->component(), ['content' => $content]);
    }
}
