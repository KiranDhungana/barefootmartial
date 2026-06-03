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
     * Base64 data URI for a JPEG/PNG under public/storage (ID cards).
     */
    public static function publicStorageDataUri(string $relativePath, string $mime = 'image/jpeg'): ?string
    {
        if (! self::canEmbedImages()) {
            return null;
        }

        $full = public_path('storage/'.$relativePath);
        if (! is_file($full)) {
            return null;
        }

        $contents = file_get_contents($full);
        if ($contents === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
