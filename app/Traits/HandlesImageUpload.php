<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUpload
{
    /**
     * Upload and compress image if it's a photo, or just store if it's a PDF.
     *
     * @return string|null Path of the stored file
     */
    protected function uploadCompressedFile(UploadedFile $file, string $path = 'bukti_transaksi')
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $filename = Str::uuid().'.'.$extension;

        // If it's a PDF, just store it normally - ALWAYS USE 'local' (private) for security
        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            return $file->storeAs($path, $filename, 'local');
        }

        // Check if it's an image that GD can handle
        $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($mimeType, $allowedImages)) {
            return $file->storeAs($path, $filename, 'local');
        }

        // Check if GD extension is loaded
        if (! extension_loaded('gd')) {
            return $file->storeAs($path, $filename, 'local');
        }

        // Start compression logic using GD
        try {
            $sourcePath = $file->getRealPath();
            $info = getimagesize($sourcePath);

            if (! $info) {
                return $file->storeAs($path, $filename, 'local');
            }

            $width = $info[0];
            $height = $info[1];

            // Maximum width for receipts (more than enough for OCR)
            $maxDimension = 1600;
            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) ($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) ($width * ($maxDimension / $height));
                }
            }

            // Create canvas
            $imageResized = imagecreatetruecolor($newWidth, $newHeight);

            // Load source image
            switch ($mimeType) {
                case 'image/jpeg':
                    $imageSource = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $imageSource = imagecreatefrompng($sourcePath);
                    // Handle transparency
                    imagealphablending($imageResized, false);
                    imagesavealpha($imageResized, true);
                    break;
                case 'image/webp':
                    $imageSource = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return $file->storeAs($path, $filename, 'local');
            }

            // Resize
            imagecopyresampled($imageResized, $imageSource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Output to buffer
            ob_start();
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($imageResized, null, 75); // 75% quality
                    break;
                case 'image/png':
                    imagepng($imageResized, null, 7); // Compression level 7
                    break;
                case 'image/webp':
                    imagewebp($imageResized, null, 75);
                    break;
            }
            $compressedContent = ob_get_clean();

            // Store compressed content
            $fullPath = $path.'/'.$filename;
            Storage::disk('local')->put($fullPath, $compressedContent);

            // Free memory
            imagedestroy($imageSource);
            imagedestroy($imageResized);

            return $fullPath;

        } catch (\Throwable $e) {
            \Log::error('Image compression failed: '.$e->getMessage());

            // Fallback to normal store if compression fails
            return $file->storeAs($path, $filename, 'local');
        }
    }
}
