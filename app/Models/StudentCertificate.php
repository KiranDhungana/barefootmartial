<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCertificate extends Model
{
    public const TYPE_NORMAL = 'normal';

    /** @deprecated Use TYPE_NORMAL — kept for existing DB rows */
    public const TYPE_GENERAL = 'general';

    public const TYPE_BELT = 'belt';

    public const TYPE_EVENT = 'event';

    public const TYPE_ACHIEVEMENT = 'achievement';

    public const TYPE_CERTIFICATION = 'certification';

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

    /**
     * Types stored on student_certificates (excludes event — those attach to event_registrations).
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_NORMAL => 'Normal',
            self::TYPE_BELT => 'Belt',
            self::TYPE_ACHIEVEMENT => 'Achievement',
            self::TYPE_CERTIFICATION => 'Certifications',
        ];
    }

    /**
     * All attachable types in the ERP form (includes Event).
     */
    public static function attachTypeOptions(): array
    {
        return [
            self::TYPE_NORMAL => 'Normal',
            self::TYPE_EVENT => 'Event',
            self::TYPE_BELT => 'Belt',
            self::TYPE_ACHIEVEMENT => 'Achievement',
            self::TYPE_CERTIFICATION => 'Certifications',
        ];
    }

    public static function validationRule(bool $includeEvent = false): string
    {
        $types = array_keys(self::typeOptions());
        if ($includeEvent) {
            $types[] = self::TYPE_EVENT;
            $types[] = self::TYPE_GENERAL; // legacy
        } else {
            $types[] = self::TYPE_GENERAL;
        }

        return 'required|in:'.implode(',', array_unique($types));
    }

    public function normalizedType(): string
    {
        $type = $this->certificate_type ?: self::TYPE_NORMAL;

        return $type === self::TYPE_GENERAL ? self::TYPE_NORMAL : $type;
    }

    public function typeLabel(): string
    {
        $type = $this->normalizedType();

        return self::attachTypeOptions()[$type]
            ?? self::typeOptions()[$type]
            ?? 'Certificate';
    }

    public function isBelt(): bool
    {
        return $this->normalizedType() === self::TYPE_BELT;
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
        $name = $this->original_filename ?: $this->file_url;

        return (bool) preg_match('/\.pdf($|\?)/i', (string) $name)
            || ($this->resource_type === 'raw' && ! preg_match('/\.(jpe?g|png|gif|webp)$/i', (string) $name));
    }

    public function downloadUrl(): ?string
    {
        $name = $this->original_filename
            ?: (($this->isPdf() ? ($this->title ?: 'certificate').'.pdf' : 'certificate'));

        return \App\Services\CloudinaryService::downloadableUrl($this->file_url, $name);
    }
}
