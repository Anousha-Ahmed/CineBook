<?php

namespace App\Support;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

final class Qr
{
    public static function dataUri(string $text, int $size = 220): string
    {
        $qr = new QrCode(data: $text, size: $size, margin: 8);

        return (new PngWriter())->write($qr)->getDataUri();
    }
}
