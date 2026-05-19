<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function svg(string $data, int $size = 180): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            data: $data,
            size: $size,
            margin: 4,
        );

        return $builder->build()->getString();
    }
}
