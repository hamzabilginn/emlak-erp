<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class SitemapController {
    
    public function index(): void {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.id, p.updated_at, p.tenant_id
                FROM properties p
                JOIN tenants t ON p.tenant_id = t.id
                WHERE p.status IN ('for_sale', 'for_rent') AND t.status = 'active'";
        $stmt = $db->query($sql);
        $properties = $stmt->fetchAll();

        header('Content-Type: application/xml; charset=utf-8');
        
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        // Ana Sayfa
        $homeUrl = $xml->addChild('url');
        $homeUrl->addChild('loc', \web_url('/emlak/public/'));
        $homeUrl->addChild('priority', '1.0');
        
        // Vitrin/İlan Sayfaları
        foreach ($properties as $prop) {
            $url = $xml->addChild('url');
            $loc = \web_url('/emlak/public/showcase/show/' . $prop['id'] . '?tenant=' . $prop['tenant_id']);
            $url->addChild('loc', htmlspecialchars($loc));
            
            // updated_at PostgreSQL TIMESTAMP (Y-m-d H:i:s), sitemap için W3C Datetime'a çevirelim
            $date = date('Y-m-d\TH:i:sP', strtotime($prop['updated_at']));
            $url->addChild('lastmod', $date);
            $url->addChild('priority', '0.8');
        }

        echo $xml->asXML();
    }
    
    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');
        
        $sitemapUrl = \web_url('/emlak/public/sitemap.xml');
        
        echo "User-agent: *\n";
        echo "Disallow: /auth/\n";
        echo "Disallow: /dashboard/\n";
        echo "Disallow: /property/\n";
        echo "Disallow: /customer/\n";
        echo "Allow: /\n\n";
        echo "Sitemap: {$sitemapUrl}\n";
    }
}
