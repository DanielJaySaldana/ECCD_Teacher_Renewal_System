<?php declare(strict_types=1);


namespace App\Lib;

final class Uploader
{
    /** @return array{ok:bool,error?:string,stored_path?:string,original_name?:string,mime?:string,size?:int} */
    public static function store(array $file, string $destDir, array $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'], int $maxBytes = 5_000_000): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed.'];
        }
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            return ['ok' => false, 'error' => 'File size must be under 5MB.'];
        }
        $original = (string)($file['name'] ?? 'file');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $labels = array_map(static fn (string $item): string => strtoupper($item), $allowedExt);
            return ['ok' => false, 'error' => 'Only ' . implode('/', $labels) . ' files are allowed.'];
        }

        if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            return ['ok' => false, 'error' => 'Server storage folder is not writable.'];
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;

        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp) || !move_uploaded_file($tmp, $destPath)) {
            return ['ok' => false, 'error' => 'Could not save uploaded file.'];
        }

        $mime = (string)($file['type'] ?? 'application/octet-stream');

        return [
            'ok' => true,
            'stored_path' => $destPath,
            'original_name' => $original,
            'mime' => $mime,
            'size' => $size,
        ];
    }
}