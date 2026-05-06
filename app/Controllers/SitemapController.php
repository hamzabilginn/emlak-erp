<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class SitemapController {
    public function index() {
        // XML header'ını ayarla
        header("Content-Type: application/xml; charset=utf-8");
        
        $db = Database::getInstance()->getConnection();
        $baseUrl = "https://emlak-erp.onrender.com";

        // 1. Tüm Aktif Ofisleri (Tenants) Çek
        $tenants = $db->query("SELECT id FROM tenants WHERE status = 'active'")->fetchAll();

        // 2. Tüm Aktif İlanları (Properties) Çek
        $properties = $db->query("SELECT id, title, city, district, tenant_id FROM properties WHERE status IN ('for_sale', 'for_rent')")->fetchAll();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // --- ANA SAYFA ---
        echo '<url><loc>' . $baseUrl . '/</loc><priority>1.0</priority></url>';

        // --- OFİS VİTRİNLERİ ---
        foreach ($tenants as $t) {
            echo '<url><loc>' . $baseUrl . '/vitrin?tenant=' . $t['id'] . '</loc><priority>0.8</priority></url>';
        }

        // --- İLAN DETAY SAYFALARI ---
        foreach ($properties as $p) {
            $slug = trim((string) ($p['slug'] ?? ''));
            if ($slug === '') {
                $slug = preg_replace('/[^a-z0-9]+/u', '-', mb_strtolower(trim(($p['title'] ?? '') . ' ' . ($p['city'] ?? '') . ' ' . ($p['district'] ?? '')), 'UTF-8'));
                $slug = trim($slug, '-');
                $slug = $slug === '' ? 'ilan' : $slug;
            }
            echo '<url><loc>' . $baseUrl . '/ilan/' . rawurlencode($slug) . '-' . $p['id'] . '</loc><priority>0.6</priority></url>';
        }

        echo '</urlset>';
    }
}