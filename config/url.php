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
