<?php
namespace App\Services;

class FileUploadService {

    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const MAX_SIZE = 5242880; // 5MB
    const UPLOAD_DIR = __DIR__ . '/../../uploads/';

    public static function validateFile($file) {
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No file selected'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error: ' . self::getUploadError($file['error'])];
        }

        if ($file['size'] > self::MAX_SIZE) {
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, WebP, GIF allowed'];
        }

        return ['success' => true];
    }

    public static function uploadFile($file, $subdirectory = 'general') {
        $validation = self::validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Create directory if not exists
        $uploadDir = self::UPLOAD_DIR . $subdirectory;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => '/uploads/' . $subdirectory . '/' . $filename,
                'filepath' => $filepath
            ];
        }

        return ['success' => false, 'error' => 'Failed to save file'];
    }

    public static function deleteFile($filepath) {
        $fullPath = __DIR__ . '/../../..' . $filepath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public static function resizeImage($filepath, $width, $height) {
        if (!extension_loaded('gd')) {
            return ['success' => false, 'error' => 'GD extension not available'];
        }

        $ext = pathinfo($filepath, PATHINFO_EXTENSION);

        // Load image based on type
        switch (strtolower($ext)) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($filepath);
                break;
            case 'png':
                $source = imagecreatefrompng($filepath);
                break;
            case 'gif':
                $source = imagecreatefromgif($filepath);
                break;
            case 'webp':
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return ['success' => false, 'error' => 'Unsupported image type'];
        }

        if (!$source) {
            return ['success' => false, 'error' => 'Failed to load image'];
        }

        // Get original dimensions
        $origWidth = imagesx($source);
        $origHeight = imagesy($source);

        // Calculate new dimensions maintaining aspect ratio
        $ratio = $origWidth / $origHeight;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        // Create resized image
        $resized = imagecreatetruecolor((int)$width, (int)$height);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, (int)$width, (int)$height, $origWidth, $origHeight);

        // Save resized image
        switch (strtolower($ext)) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($resized, $filepath, 85);
                break;
            case 'png':
                imagepng($resized, $filepath);
                break;
            case 'gif':
                imagegif($resized, $filepath);
                break;
            case 'webp':
                imagewebp($resized, $filepath, 85);
                break;
        }

        imagedestroy($source);
        imagedestroy($resized);

        return ['success' => true, 'message' => 'Image resized successfully'];
    }

    private static function getUploadError($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'Partial upload',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Extension not allowed'
        ];

        return $errors[$code] ?? 'Unknown error';
    }
}
?>
