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

        // 1. Ofisleri çek
        $tenants = $db->query("SELECT id FROM tenants WHERE status = 'active'")->fetchAll();

        // 2. İlanları çek (DİKKAT: slug sütununu mutlaka ekledik)
        $properties = $db->query("SELECT id, title, city, district, tenant_id, slug FROM properties WHERE status IN ('for_sale', 'for_rent')")->fetchAll();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // --- 1. ANA SAYFA ---
        echo '<url><loc>' . $baseUrl . '/</loc><priority>1.0</priority></url>';

        // --- 2. OFİS VİTRİNLERİ ---
        foreach ($tenants as $t) {
            // SEO dostu vitrin linki
            echo '<url><loc>' . $baseUrl . '/vitrin/' . $t['id'] . '</loc><priority>0.8</priority></url>';
        }

        // --- 3. İLAN DETAY SAYFALARI ---
        foreach ($properties as $p) {
            // Eğer veritabanında slug varsa onu kullan, yoksa geçici bir slug üret
            $slug = !empty($p['slug']) ? $p['slug'] : 'ilan';
            
            // SEO Dostu Link Yapısı: /ilan/slug-id
            echo '<url><loc>' . $baseUrl . '/ilan/' . $slug . '-' . $p['id'] . '</loc><priority>0.6</priority></url>';
        }

        echo '</urlset>';
    }
}