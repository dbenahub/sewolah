<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

/**
 * Inline a small stylesheet into <head> so the first paint does not wait for
 * an extra render-blocking request (important on slow mobile networks).
 */
class InlineCss
{
    public static function file(string $publicPath): ?HtmlString
    {
        $full = public_path($publicPath);
        if (! is_file($full)) {
            return null;
        }

        $key = 'inline-css:'.md5($full).':'.filemtime($full).':'.filesize($full);
        $css = Cache::rememberForever($key, fn () => self::minify((string) file_get_contents($full)));

        return new HtmlString($css);
    }

    /** Inline a Vite-built CSS entry (e.g. resources/css/app.css). */
    public static function vite(string $entry): ?HtmlString
    {
        if (is_file(public_path('hot'))) {
            return null; // dev server running — use @vite instead
        }

        foreach (['build/manifest.json', 'build/.vite/manifest.json'] as $manifestPath) {
            $manifestFile = public_path($manifestPath);
            if (! is_file($manifestFile)) {
                continue;
            }
            $manifest = json_decode((string) file_get_contents($manifestFile), true);
            $file = $manifest[$entry]['file'] ?? null;
            if ($file && is_file(public_path('build/'.$file))) {
                return self::file('build/'.$file);
            }
        }

        return null;
    }

    public static function minify(string $css): string
    {
        $css = preg_replace('!/\*.*?\*/!s', '', $css);          // comments
        $css = preg_replace('/\s+/', ' ', $css);                // collapse whitespace
        $css = preg_replace('/\s*([{};,>])\s*/', '$1', $css);   // around punctuation (not ':' — breaks selectors like "a :hover")
        $css = str_replace(';}', '}', $css);

        return trim(str_replace('</style', '<\/style', $css));
    }
}
