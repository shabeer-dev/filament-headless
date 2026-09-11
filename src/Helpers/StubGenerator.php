<?php

namespace HeadlessKit\Helpers;

use Illuminate\Filesystem\Filesystem;

class StubGenerator
{
    public function __construct(protected Filesystem $files) {}

    /**
     * Render a stub template by replacing replacement variables.
     */
    public function render(string $stubPath, array $replacements = []): string
    {
        if (! $this->files->exists($stubPath)) {
            throw new \InvalidArgumentException("Stub not found at: {$stubPath}");
        }

        $content = $this->files->get($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
            $content = str_replace('{{ '.$key.' }}', $value, $content);
        }

        return $content;
    }

    /**
     * Render and write a file safely.
     */
    public function write(string $stubPath, string $destinationPath, array $replacements = [], bool $overwrite = false): bool
    {
        if ($this->files->exists($destinationPath) && ! $overwrite) {
            return false;
        }

        $this->files->ensureDirectoryExists(dirname($destinationPath));

        $rendered = $this->render($stubPath, $replacements);

        $this->files->put($destinationPath, $rendered);

        return true;
    }
}
