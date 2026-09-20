<?php

namespace App\Support;

use App\Models\InstituteProfile;

class AcademyOrg
{
    /**
     * Institute branding/contact used on invoices, receipts, bills, and PDFs.
     * DB profile overrides config/academy.php defaults.
     *
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $defaults = config('academy.org', []);

        try {
            $profile = InstituteProfile::current();
        } catch (\Throwable $e) {
            return $defaults;
        }

        $overrides = [];
        foreach ($profile->toOrgArray() as $key => $value) {
            if ($value !== null && $value !== '') {
                $overrides[$key] = $value;
            }
        }

        return array_merge($defaults, $overrides);
    }

    public static function getString(string $key, ?string $fallback = null): ?string
    {
        $value = self::get()[$key] ?? $fallback;

        return $value !== null && $value !== '' ? (string) $value : $fallback;
    }
}
