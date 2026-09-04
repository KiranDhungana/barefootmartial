<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $url = config('cloudinary.url');
        if ($url) {
            Configuration::instance($url);
        } else {
            $cloud = config('cloudinary.cloud_name');
            $key = config('cloudinary.api_key');
            $secret = config('cloudinary.api_secret');
            if (! $cloud || ! $key || ! $secret) {
                throw new RuntimeException('Cloudinary credentials are not configured.');
            }
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $cloud,
                    'api_key' => $key,
                    'api_secret' => $secret,
                ],
                'url' => ['secure' => true],
            ]);
        }

        $this->cloudinary = new Cloudinary(Configuration::instance());
    }

    /**
     * @return array{url: string, public_id: string, resource_type: string, format: ?string, original_filename: string}
     */
    public function uploadImage(UploadedFile $file, string $folderSuffix = 'media'): array
    {
        return $this->upload($file, $folderSuffix, 'image');
    }

    /**
     * Upload image or PDF (certificates, documents).
     * PDFs are stored as raw with a `.pdf` public_id so downloads open correctly.
     *
     * @return array{url: string, public_id: string, resource_type: string, format: ?string, original_filename: string}
     */
    public function uploadFile(UploadedFile $file, string $folderSuffix = 'certificates'): array
    {
        $mime = (string) $file->getMimeType();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $isPdf = $mime === 'application/pdf' || $ext === 'pdf';

        if ($isPdf) {
            return $this->uploadPdf($file, $folderSuffix);
        }

        $resourceType = str_starts_with($mime, 'image/') ? 'image' : 'raw';

        return $this->upload($file, $folderSuffix, $resourceType);
    }

    /**
     * Force a Cloudinary URL to download as a named PDF/file.
     */
    public static function downloadableUrl(?string $url, string $filename = 'certificate.pdf'): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $filename = self::safeDownloadName($filename);

        // Already our app URL or non-Cloudinary — return as-is.
        if (! str_contains($url, 'res.cloudinary.com')) {
            return $url;
        }

        // Insert fl_attachment:filename after /upload/
        if (preg_match('#(/image|/raw|/video)/upload/#', $url) && ! str_contains($url, 'fl_attachment')) {
            $encoded = rawurlencode($filename);

            return preg_replace(
                '#(/(?:image|raw|video)/upload/)#',
                '$1fl_attachment:'.$encoded.'/',
                $url,
                1
            );
        }

        return $url;
    }

    public static function safeDownloadName(string $filename): string
    {
        $filename = basename(str_replace(['\\', '/'], '-', $filename));
        $filename = preg_replace('/[^\w.\- ()]+/u', '_', $filename) ?: 'certificate.pdf';

        if (! preg_match('/\.[a-z0-9]{2,5}$/i', $filename)) {
            $filename .= '.pdf';
        }

        return $filename;
    }

    /**
     * @return array{url: string, public_id: string, resource_type: string, format: ?string, original_filename: string}
     */
    private function uploadPdf(UploadedFile $file, string $folderSuffix): array
    {
        $folder = trim((string) config('cloudinary.folder', 'barefoot'), '/').'/'.$folderSuffix;
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = Str::slug($base) ?: 'certificate';
        // Extension MUST be part of public_id for raw assets, or downloads lack .pdf
        $publicId = $safe.'_'.time().'.pdf';

        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'public_id' => $publicId,
            'resource_type' => 'raw',
            'overwrite' => false,
            'type' => 'upload',
        ]);

        $url = $result['secure_url'] ?? $result['url'] ?? null;
        $storedPublicId = $result['public_id'] ?? ($folder.'/'.$publicId);

        if (! $url || ! $storedPublicId) {
            throw new RuntimeException('Cloudinary PDF upload did not return a URL.');
        }

        // Ensure delivery URL ends with .pdf when Cloudinary omitted it.
        if (! str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?: ''), '.pdf')) {
            $url = rtrim($url, '/').'.pdf';
        }

        $downloadName = ($base ?: 'certificate').'.pdf';

        return [
            'url' => self::downloadableUrl($url, $downloadName) ?: $url,
            'public_id' => $storedPublicId,
            'resource_type' => 'raw',
            'format' => 'pdf',
            'original_filename' => $downloadName,
        ];
    }

    /**
     * @return array{url: string, public_id: string, resource_type: string, format: ?string, original_filename: string}
     */
    private function upload(UploadedFile $file, string $folderSuffix, string $resourceType): array
    {
        $folder = trim((string) config('cloudinary.folder', 'barefoot'), '/').'/'.$folderSuffix;
        $original = $file->getClientOriginalName();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $base = pathinfo($original, PATHINFO_FILENAME);
        $safe = Str::slug($base) ?: 'file';

        $options = [
            'folder' => $folder,
            'resource_type' => $resourceType,
            'overwrite' => false,
            'use_filename' => true,
            'unique_filename' => true,
        ];

        // Keep extension on raw assets so OS/browsers recognize the file type.
        if ($resourceType === 'raw' && $ext !== '') {
            $options['public_id'] = $safe.'_'.time().'.'.$ext;
            unset($options['use_filename'], $options['unique_filename']);
        }

        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), $options);

        $url = $result['secure_url'] ?? $result['url'] ?? null;
        $publicId = $result['public_id'] ?? null;

        if (! $url || ! $publicId) {
            throw new RuntimeException('Cloudinary upload did not return a URL.');
        }

        $format = $result['format'] ?? ($ext ?: null);
        $downloadName = $original ?: ('file'.($ext ? '.'.$ext : ''));

        return [
            'url' => $url,
            'public_id' => $publicId,
            'resource_type' => $result['resource_type'] ?? $resourceType,
            'format' => $format,
            'original_filename' => $downloadName,
        ];
    }

    public function delete(string $publicId, string $resourceType = 'image'): void
    {
        if ($publicId === '') {
            return;
        }

        $this->cloudinary->uploadApi()->destroy($publicId, [
            'resource_type' => $resourceType ?: 'image',
        ]);
    }
}
