<?php
namespace NetaTrack\Helpers;

class Upload {
    public static function image(array $file, string $dir = 'general'): ?string {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed)) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('img_', true) . '.' . strtolower($ext);
        $path = APP_CONFIG['upload_path'] . '/' . $dir;
        if (!is_dir($path)) mkdir($path, 0755, true);

        if (move_uploaded_file($file['tmp_name'], "$path/$name")) {
            return "/uploads/$dir/$name";
        }
        return null;
    }

    public static function file(array $file, string $dir = 'docs'): ?string {
        $allowed = ['application/pdf', 'application/msword', 'image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed)) return null;
        if ($file['size'] > 20 * 1024 * 1024) return null;

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('file_', true) . '.' . strtolower($ext);
        $path = APP_CONFIG['upload_path'] . '/' . $dir;
        if (!is_dir($path)) mkdir($path, 0755, true);

        if (move_uploaded_file($file['tmp_name'], "$path/$name")) {
            return "/uploads/$dir/$name";
        }
        return null;
    }
}
