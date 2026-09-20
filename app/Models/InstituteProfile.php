<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstituteProfile extends Model
{
    protected $fillable = [
        'legal_name',
        'brand_line1',
        'brand_line2',
        'tagline',
        'footer_tagline',
        'address',
        'city',
        'district',
        'province',
        'country',
        'phone',
        'email',
        'website',
        'pan',
        'vat',
        'fonepay_terminal',
        'fonepay_address',
        'qr_path',
        'logo_path',
        'authorized_signatory',
        'notes',
    ];

    public static function current(): self
    {
        $profile = static::query()->orderBy('id')->first();
        if ($profile) {
            return $profile;
        }

        $org = config('academy.org', []);

        return static::query()->create([
            'legal_name' => $org['legal_name'] ?? 'Barefoot Martial Arts Academy',
            'brand_line1' => $org['brand_line1'] ?? 'BAREFOOT',
            'brand_line2' => $org['brand_line2'] ?? 'MARTIAL ARTS ACADEMY',
            'tagline' => $org['tagline'] ?? null,
            'footer_tagline' => $org['footer_tagline'] ?? null,
            'address' => $org['address'] ?? null,
            'phone' => $org['phone'] ?? null,
            'email' => $org['email'] ?? null,
            'website' => $org['website'] ?? null,
            'pan' => $org['pan'] ?? null,
            'vat' => $org['vat'] ?? null,
            'fonepay_terminal' => $org['fonepay_terminal'] ?? null,
            'fonepay_address' => $org['fonepay_address'] ?? null,
            'qr_path' => $org['qr_path'] ?? 'images/qr_final.jpeg',
            'logo_path' => config('academy.logo_path', 'images/logo.png'),
            'authorized_signatory' => $org['authorized_signatory'] ?? null,
            'country' => 'Nepal',
        ]);
    }

    /**
     * Full location line for PDFs / contact blocks.
     */
    public function locationLine(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->district,
            $this->province,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Values used to override config('academy.org').
     *
     * @return array<string, mixed>
     */
    public function toOrgArray(): array
    {
        $location = $this->locationLine();

        return [
            'legal_name' => $this->legal_name,
            'brand_line1' => $this->brand_line1,
            'brand_line2' => $this->brand_line2,
            'tagline' => $this->tagline,
            'footer_tagline' => $this->footer_tagline,
            'address' => $location !== '' ? $location : $this->address,
            'city' => $this->city,
            'district' => $this->district,
            'province' => $this->province,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'pan' => $this->pan,
            'vat' => $this->vat,
            'fonepay_terminal' => $this->fonepay_terminal,
            'fonepay_address' => $this->fonepay_address,
            'qr_path' => $this->qr_path,
            'logo_path' => $this->logo_path,
            'authorized_signatory' => $this->authorized_signatory,
        ];
    }
}
