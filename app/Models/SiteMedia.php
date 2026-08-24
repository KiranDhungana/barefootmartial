<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteMedia extends Model
{
    public const TYPE_GALLERY = 'gallery';

    public const TYPE_SLIDER = 'slider';

    protected $table = 'site_media';

    protected $fillable = [
        'type',
        'url',
        'public_id',
        'title',
        'subtitle',
        'cta_label',
        'cta_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function types(): array
    {
        return [
            self::TYPE_SLIDER => 'Home slider',
            self::TYPE_GALLERY => 'Gallery',
        ];
    }
}
