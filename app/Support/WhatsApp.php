<?php

namespace App\Support;

class WhatsApp
{
    public static function waMeUrl(?string $phone, string $message): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
