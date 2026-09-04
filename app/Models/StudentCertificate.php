<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCertificate extends Model
{
    public const TYPE_GENERAL = 'general';

    public const TYPE_BELT = 'belt';

    public const TYPE_EVENT = 'event';

    protected $fillable = [
        'student_id',
        'title',
        'certificate_type',
        'file_url',
        'public_id',
        'resource_type',
        'original_filename',
        'issued_on',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issued_on' => 'date',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_GENERAL => 'Normal certificate',
            self::TYPE_BELT => 'Belt certificate',
        ];
    }

    public static function attachTypeOptions(): array
    {
        return [
            self::TYPE_GENERAL => 'Normal certificate',
            self::TYPE_BELT => 'Belt certificate',
            self::TYPE_EVENT => 'Event certificate',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->certificate_type] ?? 'Certificate';
    }

    public function isBelt(): bool
    {
        return $this->certificate_type === self::TYPE_BELT;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        if ($this->resource_type === 'raw') {
            return false;
        }

        return in_array($this->resource_type, ['image', ''], true)
            || preg_match('/\.(jpe?g|png|gif|webp)$/i', $this->file_url ?? '');
    }

    public function isPdf(): bool
    {
        if ($this->resource_type === 'raw') {
            return true;
        }

        $name = $this->original_filename ?: $this->file_url;

        return (bool) preg_match('/\.pdf($|\?)/i', (string) $name);
    }

    /**
     * URL that downloads with a proper .pdf / file name.
     */
    public function downloadUrl(): ?string
    {
        $name = $this->original_filename
            ?: (($this->isPdf() ? ($this->title ?: 'certificate').'.pdf' : 'certificate'));

        return \App\Services\CloudinaryService::downloadableUrl($this->file_url, $name);
    }
}
