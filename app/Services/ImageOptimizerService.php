<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;

class ImageOptimizerService
{
    public function storeProductImage(
        UploadedFile $file,
        string $title = 'product'
    ): string
    {
        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodePath(
            $file->getRealPath()
        );

        $image->scaleDown(width: 1600);

        $encoded = $image->encodeUsingFormat(
            Format::JPEG,
            quality: 80
        );

        $filename = str($title)
            ->slug()
            ->append('-')
            ->append(uniqid())
            ->append('.jpg');

        $path = 'products/' . $filename;

        Storage::disk('public')->put(
            $path,
            (string) $encoded
        );

        return $path;
    }
}