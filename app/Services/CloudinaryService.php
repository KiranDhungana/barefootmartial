<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
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
     * @return array{url: string, public_id: string, resource_type: string}
     */
    public function uploadImage(UploadedFile $file, string $folderSuffix = 'media'): array
    {
        return $this->upload($file, $folderSuffix, 'image');
    }

    /**
     * Upload image or PDF (certificates, documents).
     *
     * @return array{url: string, public_id: string, resource_type: string}
     */
    public function uploadFile(UploadedFile $file, string $folderSuffix = 'certificates'): array
    {
        $mime = (string) $file->getMimeType();
        $resourceType = str_starts_with($mime, 'image/') ? 'image' : 'raw';

        return $this->upload($file, $folderSuffix, $resourceType);
    }

    /**
     * @return array{url: string, public_id: string, resource_type: string}
     */
    private function upload(UploadedFile $file, string $folderSuffix, string $resourceType): array
    {
        $folder = trim((string) config('cloudinary.folder', 'barefoot'), '/').'/'.$folderSuffix;

        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => $resourceType,
            'overwrite' => false,
        ]);

        $url = $result['secure_url'] ?? $result['url'] ?? null;
        $publicId = $result['public_id'] ?? null;

        if (! $url || ! $publicId) {
            throw new RuntimeException('Cloudinary upload did not return a URL.');
        }

        return [
            'url' => $url,
            'public_id' => $publicId,
            'resource_type' => $result['resource_type'] ?? $resourceType,
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
