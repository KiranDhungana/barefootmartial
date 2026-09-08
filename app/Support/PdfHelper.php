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

    /**
     * Absolute path under public/ for DomPDF image embedding.
     */
    public static function publicImagePath(string $relativePath): ?string
    {
        if (! self::canEmbedImages()) {
            return null;
        }

        $path = public_path(ltrim($relativePath, '/\\'));

        return is_file($path) ? $path : null;
    }

    /**
     * Base64 data URI for a file under public/ (e.g. images/qr_final.jpeg).
     */
    public static function publicImageDataUri(string $relativePath): ?string
    {
        $path = self::publicImagePath($relativePath);
        if (! $path) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * Simple English amount-in-words for invoice totals (NPR).
     */
    public static function amountInWords(float $amount): string
    {
        $amount = round($amount, 2);
        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        $words = self::numberToWords($rupees).' Rupees';
        if ($paisa > 0) {
            $words .= ' and '.self::numberToWords($paisa).' Paisa';
        }

        return $words.' Only';
    }

    private static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $chunk = function (int $n) use ($ones, $tens): string {
            $out = '';
            if ($n >= 100) {
                $out .= $ones[(int) floor($n / 100)].' Hundred';
                $n %= 100;
                if ($n > 0) {
                    $out .= ' ';
                }
            }
            if ($n >= 20) {
                $out .= $tens[(int) floor($n / 10)];
                if ($n % 10) {
                    $out .= '-'.$ones[$n % 10];
                }
            } elseif ($n > 0) {
                $out .= $ones[$n];
            }

            return $out;
        };

        $parts = [];
        $crore = (int) floor($number / 10000000);
        $number %= 10000000;
        $lakh = (int) floor($number / 100000);
        $number %= 100000;
        $thousand = (int) floor($number / 1000);
        $number %= 1000;

        if ($crore) {
            $parts[] = $chunk($crore).' Crore';
        }
        if ($lakh) {
            $parts[] = $chunk($lakh).' Lakh';
        }
        if ($thousand) {
            $parts[] = $chunk($thousand).' Thousand';
        }
        if ($number) {
            $parts[] = $chunk($number);
        }

        return implode(' ', $parts);
    }
}
