<?php
declare(strict_types=1);

/**
 * Yerel (/emlak/public) ve üretim (DocumentRoot = public) ortamlarında aynı yolu üretir.
 * Eski sabit önek: /emlak/public — APP_WEB_BASE ile değiştirilir.
 */
if (!function_exists('web_url')) {
    function web_url(string $url): string {
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }
        $base = defined('APP_WEB_BASE') ? APP_WEB_BASE : '';
        $p = $url;
        if (strpos($p, '/emlak/public') === 0) {
            $p = substr($p, strlen('/emlak/public'));
        }
        if ($p === '') {
            return $base !== '' ? $base . '/' : '/';
        }
        if (isset($p[0]) && $p[0] !== '/' && strpos($p, '?') !== 0) {
            $p = '/' . $p;
        }
        return $base . $p;
    }
}

if (!function_exists('property_image_url')) {
    /**
     * Veritabanındaki image_path: tam https URL veya eski /uploads/... (Supabase kökü varsa public URL'ye çevrilir).
     */
    function property_image_url(string $storedPath): string {
        $storedPath = trim($storedPath);
        if ($storedPath === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $storedPath) === 1) {
            return $storedPath;
        }

        $local = str_starts_with($storedPath, '/') ? $storedPath : '/' . $storedPath;

        if (str_starts_with($local, '/uploads/')) {
            $mapped = property_image_legacy_uploads_to_supabase_url($local);
            if ($mapped !== '') {
                return $mapped;
            }
        }

        return web_url('/emlak/public' . $local);
    }
}

if (!function_exists('property_image_legacy_uploads_to_supabase_url')) {
    /**
     * Eski DB: /uploads/properties/dosya.jpg → Supabase public URL.
     * Bucket içinde aynı göreli yol kullanıldıysa (ör. properties/dosya.jpg) görsel açılır.
     */
    function property_image_legacy_uploads_to_supabase_url(string $pathStartingWithUploads): string {
        $base = rtrim((string) (getenv('SUPABASE_URL') ?: ''), '/');
        if ($base === '') {
            return '';
        }
        $bucket = (string) (getenv('SUPABASE_STORAGE_BUCKET') ?: 'ilan-fotograflari');
        $tail = preg_replace('#^/uploads/#', '', $pathStartingWithUploads);
        $tail = ltrim((string) $tail, '/');
        if ($tail === '') {
            return '';
        }
        $parts = array_filter(explode('/', str_replace('\\', '/', $tail)), static fn ($p) => $p !== '' && $p !== '.' && $p !== '..');
        $encoded = implode('/', array_map('rawurlencode', $parts));
        return $base . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encoded;
    }
}
