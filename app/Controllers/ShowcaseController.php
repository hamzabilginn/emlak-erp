<?php
namespace App\Controllers;

use Config\Database;
use PDO;

/**
 * Showcase (Dijital Müşteri Vitrini) Controller
 * TenantMiddleware DIŞINDA çalışır (URL rotasında bu eklenmemiş olmalı).
 * Müşteriler ?tenant=XX şeklinde URL'den girip satılık/kiralık ilanları görebilir.
 */
class ShowcaseController {

    public function index(): void {
        $tenantId = (int)($_GET['tenant'] ?? 0);
        if ($tenantId <= 0) {
            echo "Geçersiz dükkan bağlantısı. Lütfen linki kontrol ediniz.";
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        // Dükkan Adını Al
        $stmt = $db->prepare("SELECT name FROM tenants WHERE id = :t LIMIT 1");
        $stmt->execute([':t' => $tenantId]);
        $tenantName = $stmt->fetchColumn();

        if (!$tenantName) {
            echo "Böyle bir dükkan sistemde bulunamadı.";
            exit;
        }

        // Dükkanın Sadece AKTİF ve Satılık/Kiralık olan ilanlarını al
        $stmt = $db->prepare("SELECT p.*, (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_cover DESC, id ASC LIMIT 1) as cover_image FROM properties p WHERE p.tenant_id = :t AND p.status IN ('for_sale', 'for_rent') ORDER BY p.id DESC");
        $stmt->execute([':t' => $tenantId]);
        $properties = $stmt->fetchAll();

        // Render işlemi (BaseController extend etmediğimiz için veya public view olduğu için basit include yapıyoruz)
        $data = [
            'tenantId' => $tenantId,
            'tenantName' => $tenantName,
            'properties' => $properties
        ];

        extract($data);
        require_once __DIR__ . '/../../resources/views/showcase/index.php';
    }

    public function show(int $id): void {
        $tenantId = (int)($_GET['tenant'] ?? 0);
        if ($tenantId <= 0) {
            echo "Geçersiz dükkan bağlantısı. Lütfen linki kontrol ediniz.";
            exit;
        }

        $db = \Config\Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT p.*, t.name as tenant_name, t.phone as tenant_phone FROM properties p JOIN tenants t ON p.tenant_id = t.id WHERE p.id = :id AND p.tenant_id = :t AND p.status IN ('for_sale', 'for_rent') LIMIT 1");
        $stmt->execute([':id' => $id, ':t' => $tenantId]);
        $property = $stmt->fetch();

        if (!$property) {
            echo "İlan bulunamadı veya yayından kaldırılmış.";
            exit;
        }

        $stmtImg = $db->prepare("SELECT * FROM property_images WHERE property_id = :id ORDER BY is_cover DESC, id ASC");
        $stmtImg->execute([':id' => $id]);
        $images = $stmtImg->fetchAll();

        $data = [
            'tenantId' => $tenantId,
            'tenantName' => $property['tenant_name'],
            'tenantPhone' => $property['tenant_phone'],
            'property' => $property,
            'images' => $images
        ];

        extract($data);
        require_once __DIR__ . '/../../resources/views/showcase/show.php';
    }
}