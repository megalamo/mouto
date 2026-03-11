<?php
namespace App\Services;

class CacheService
{
    public function __construct(
        private string $mainCacheDir // Passed from config
    ) {
    }

    public function save(string $file, string $data): void
    {
        $this->ensureDirectories();

        $f = fopen($this->mainCacheDir . '/' . $file, "w");
        if ($f) {
            fwrite($f, $data);
            fclose($f);
        }
    }

    public function load(string $file): string|false
    {
        $this->ensureDirectories();

        $path = $this->mainCacheDir . '/' . $file;
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    public function destroyPageCache(string $dir): void
    {
        $dir = rtrim($dir, '/') . '/';
        $fullPath = $this->mainCacheDir . '/' . $dir;

        if (!is_dir($fullPath))
            return;

        // Protection to ensure we don't delete root cache folders recursively by accident
        if ($fullPath !== $this->mainCacheDir . "/cache/" && $fullPath !== $this->mainCacheDir . "/search_cache/") {
            $dirContents = scandir($fullPath);
            foreach ($dirContents as $item) {
                if ($item === '.' || $item === '..')
                    continue;

                if (is_dir($fullPath . $item)) {
                    $this->destroyPageCache($dir . $item . '/');
                } elseif (file_exists($fullPath . $item)) {
                    unlink($fullPath . $item);
                }
            }
            rmdir($fullPath);
        }
    }

    private function ensureDirectories(): void
    {
        if (!is_dir($this->mainCacheDir)) {
            mkdir($this->mainCacheDir, 0777, true);
        }
        if (!is_dir($this->mainCacheDir . "/search_cache")) {
            mkdir($this->mainCacheDir . "/search_cache", 0777, true);
        }
        if (!is_dir($this->mainCacheDir . "/cache")) {
            mkdir($this->mainCacheDir . "/cache", 0777, true);
        }
    }
}