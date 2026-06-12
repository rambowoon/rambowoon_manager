<?php
namespace RamboWoon;

class ImageTrimService {
    private const SUPPORTED_TYPES = [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF,
        IMAGETYPE_WEBP,
    ];

    public static function isSupportedFile(string $fileName): bool {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true);
    }

    public static function trimFile(string $filePath, int $tolerance = 12): array {
        $info = @getimagesize($filePath);
        if (!$info || !in_array((int)$info[2], self::SUPPORTED_TYPES, true)) {
            return ['status' => 'error', 'message' => 'Unsupported image'];
        }

        $image = self::loadImage($filePath, (int)$info[2]);
        if (!$image) {
            return ['status' => 'error', 'message' => 'Cannot load image'];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 1 || $height < 1) {
            imagedestroy($image);
            return ['status' => 'error', 'message' => 'Invalid image size'];
        }

        $box = self::findTrimBox($image, $width, $height, $tolerance);
        if (!$box) {
            imagedestroy($image);
            return ['status' => 'skipped', 'message' => 'Image has no visible pixels'];
        }

        if ($box['left'] === 0 && $box['top'] === 0 && $box['right'] === $width - 1 && $box['bottom'] === $height - 1) {
            imagedestroy($image);
            return ['status' => 'skipped', 'message' => 'Nothing to trim'];
        }

        $newWidth = $box['right'] - $box['left'] + 1;
        $newHeight = $box['bottom'] - $box['top'] + 1;
        $trimmed = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array((int)$info[2], [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($trimmed, false);
            imagesavealpha($trimmed, true);
            $transparent = imagecolorallocatealpha($trimmed, 0, 0, 0, 127);
            imagefilledrectangle($trimmed, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopy($trimmed, $image, 0, 0, $box['left'], $box['top'], $newWidth, $newHeight);

        $tempPath = tempnam(dirname($filePath), 'trim_');
        if ($tempPath === false) {
            imagedestroy($trimmed);
            imagedestroy($image);
            return ['status' => 'error', 'message' => 'Cannot create temp file'];
        }

        $saved = self::saveImage($trimmed, $tempPath, (int)$info[2]);
        imagedestroy($trimmed);
        imagedestroy($image);

        if (!$saved) {
            @unlink($tempPath);
            return ['status' => 'error', 'message' => 'Cannot save image'];
        }

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        if (!@rename($tempPath, $filePath)) {
            if (!@copy($tempPath, $filePath)) {
                @unlink($tempPath);
                return ['status' => 'error', 'message' => 'Cannot replace original file'];
            }
            @unlink($tempPath);
        }

        clearstatcache(true, $filePath);
        return [
            'status' => 'success',
            'oldWidth' => $width,
            'oldHeight' => $height,
            'newWidth' => $newWidth,
            'newHeight' => $newHeight,
            'removedX' => $width - $newWidth,
            'removedY' => $height - $newHeight,
            'size' => @filesize($filePath) ?: 0,
        ];
    }

    private static function loadImage(string $filePath, int $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                $image = @imagecreatefrompng($filePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                return $image;
            case IMAGETYPE_GIF:
                $image = @imagecreatefromgif($filePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                return $image;
            case IMAGETYPE_WEBP:
                $image = @imagecreatefromwebp($filePath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                return $image;
        }
        return false;
    }

    private static function saveImage($image, string $filePath, int $type): bool {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagejpeg($image, $filePath, 95);
            case IMAGETYPE_PNG:
                return @imagepng($image, $filePath, 6);
            case IMAGETYPE_GIF:
                return @imagegif($image, $filePath);
            case IMAGETYPE_WEBP:
                return @imagewebp($image, $filePath, 95);
        }
        return false;
    }

    private static function findTrimBox($image, int $width, int $height, int $tolerance): ?array {
        $tolerance = max(0, min(255, $tolerance));
        $background = self::pixelAt($image, 0, 0);

        $left = $width;
        $top = $height;
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (!self::isTrimPixel($image, $x, $y, $background, $tolerance)) {
                    if ($x < $left) $left = $x;
                    if ($x > $right) $right = $x;
                    if ($y < $top) $top = $y;
                    if ($y > $bottom) $bottom = $y;
                }
            }
        }

        if ($right < $left || $bottom < $top) {
            return null;
        }

        return ['left' => $left, 'top' => $top, 'right' => $right, 'bottom' => $bottom];
    }

    private static function isTrimPixel($image, int $x, int $y, array $background, int $tolerance): bool {
        $pixel = self::pixelAt($image, $x, $y);

        if ($pixel['a'] >= 120) {
            return true;
        }

        if ($background['a'] >= 120) {
            return false;
        }

        return abs($pixel['r'] - $background['r']) <= $tolerance
            && abs($pixel['g'] - $background['g']) <= $tolerance
            && abs($pixel['b'] - $background['b']) <= $tolerance
            && abs($pixel['a'] - $background['a']) <= $tolerance;
    }

    private static function pixelAt($image, int $x, int $y): array {
        $rgba = imagecolorat($image, $x, $y);
        return [
            'a' => ($rgba & 0x7F000000) >> 24,
            'r' => ($rgba >> 16) & 0xFF,
            'g' => ($rgba >> 8) & 0xFF,
            'b' => $rgba & 0xFF,
        ];
    }
}
