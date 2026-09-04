<?php

namespace App\Support;

class PdfHelper
{
    public static function canEmbedImages(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Absolute path for DomPDF when GD is available; null otherwise.
     */
    public static function logoPath(): ?string
    {
        if (! self::canEmbedImages()) {
            return null;
        }

        $path = public_path(config('academy.logo_path', 'images/logo.png'));

        return is_file($path) ? $path : null;
    }

    /**
     * Base64 data URI for a JPEG/PNG under public/storage, or a remote (Cloudinary) URL.
     */
    public static function publicStorageDataUri(string $relativePathOrUrl, string $mime = 'image/jpeg'): ?string
    {
        if (! self::canEmbedImages()) {
            return null;
        }

        $contents = null;

        if (str_starts_with($relativePathOrUrl, 'http://') || str_starts_with($relativePathOrUrl, 'https://')) {
            try {
                $contents = @file_get_contents($relativePathOrUrl);
            } catch (\Throwable $e) {
                return null;
            }
        } else {
            $full = public_path('storage/'.$relativePathOrUrl);
            if (! is_file($full)) {
                $full = storage_path('app/public/'.$relativePathOrUrl);
            }
            if (! is_file($full)) {
                return null;
            }
            $contents = file_get_contents($full);
        }

        if ($contents === false || $contents === null || $contents === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
