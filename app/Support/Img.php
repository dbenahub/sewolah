<?php

namespace App\Support;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;

/**
 * Responsive, pre-optimised image helper.
 *
 * Optimised variants live in public/images/opt as "{name}-{width}.webp" plus a
 * single "{name}-{fallbackWidth}.jpg" for browsers without WebP. When a variant
 * is missing we silently fall back to the original asset, so a missing file can
 * never break a page.
 */
class Img
{
    /** @var array<string, bool> */
    protected static array $exists = [];

    protected static function has(string $publicPath): bool
    {
        return self::$exists[$publicPath] ??= is_file(public_path($publicPath));
    }

    /**
     * @param  int[]  $widths
     * @return array{src: string, srcset: ?string}
     */
    public static function opt(string $name, array $widths, int $fallbackWidth, string $original): array
    {
        $set = [];
        foreach ($widths as $w) {
            $path = "images/opt/{$name}-{$w}.webp";
            if (self::has($path)) {
                $set[] = asset($path)." {$w}w";
            }
        }

        $fallback = "images/opt/{$name}-{$fallbackWidth}.jpg";

        return [
            'src' => self::has($fallback) ? asset($fallback) : asset($original),
            'srcset' => $set ? implode(', ', $set) : null,
        ];
    }

    /** Small logo (already sized for 2x displays). */
    public static function logo(string $name, string $original): array
    {
        return [
            'src' => self::has("images/opt/{$name}.png") ? asset("images/opt/{$name}.png") : asset($original),
            'webp' => self::has("images/opt/{$name}.webp") ? asset("images/opt/{$name}.webp") : null,
        ];
    }

    /**
     * Vehicle photo variants. Uses committed variants when available, otherwise
     * generates WebP copies of an uploaded photo once (requires GD with WebP).
     *
     * @return array{src: string, srcset: ?string}
     */
    public static function vehicle(Vehicle $vehicle): array
    {
        $base = pathinfo((string) $vehicle->image_path, PATHINFO_FILENAME);
        if ($base === '') {
            return ['src' => $vehicle->imageUrl(), 'srcset' => null];
        }

        $committed = self::opt('vehicle-'.$base, [400, 800], 800, '');
        if ($committed['srcset']) {
            return $committed;
        }

        $generated = self::generateVehicleVariants((string) $vehicle->image_path);

        return [
            'src' => $vehicle->imageUrl(),
            'srcset' => $generated ?: null,
        ];
    }

    /**
     * Create vehicles/opt/{base}-{w}.webp on the public disk. Returns a srcset
     * string or '' when the server cannot produce WebP.
     */
    public static function generateVehicleVariants(string $imagePath, array $widths = [400, 800]): string
    {
        try {
            $disk = Storage::disk('public');
            $base = pathinfo($imagePath, PATHINFO_FILENAME);
            if ($base === '' || ! $disk->exists($imagePath)) {
                return '';
            }

            $set = [];
            $source = null;
            foreach ($widths as $w) {
                $target = "vehicles/opt/{$base}-{$w}.webp";
                if (! $disk->exists($target)) {
                    if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
                        return '';
                    }
                    $source ??= @imagecreatefromstring($disk->get($imagePath));
                    if (! $source) {
                        return '';
                    }
                    $scaled = imagescale($source, min($w, imagesx($source)));
                    if (! $scaled) {
                        return '';
                    }
                    ob_start();
                    imagewebp($scaled, null, 72);
                    $disk->put($target, (string) ob_get_clean());
                    imagedestroy($scaled);
                }
                $set[] = $disk->url($target)." {$w}w";
            }
            if ($source) {
                imagedestroy($source);
            }

            return implode(', ', $set);
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }
}
