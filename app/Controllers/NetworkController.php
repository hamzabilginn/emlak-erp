<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class NetworkController extends BaseController {

    public function __construct() {
        // SaaS Güvenliği: Eğer login olmamışsa (tenant_id oturumu yoksa) yönlendir.
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {    
            $this->redirect('/emlak/public/auth/login');
            exit;
        }
    }

    /**
     * Ortak Havuz (Paslaşma Ağı) - Diğer tenantların paylaşıma açtığı aktif ilanlar.
     */
    public function index(): void {
        $db = Database::getInstance()->getConnection();
        $currentTenantId = (int) $_SESSION['tenant_id'];

        // Diğer ofislerin (tenant_id != current) 'is_shared_pool' = true olan satış/kiralık ilanları.
        // Kapak: ShowcaseController ile aynı alt sorgu (property_images).
        $sql = "SELECT p.*, t.name AS office_name, t.phone AS office_phone,
                (SELECT pi.image_path FROM property_images pi
                 WHERE pi.property_id = p.id
                 ORDER BY pi.is_cover DESC, pi.id ASC LIMIT 1) AS cover_image
                FROM properties p
                INNER JOIN tenants t ON p.tenant_id = t.id
                WHERE p.is_shared_pool = TRUE
                AND p.tenant_id != :tid
                AND p.status IN ('for_sale', 'for_rent')
                ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':tid' => $currentTenantId]);
        
        $sharedProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('network/index', [
            'pageTitle' => 'Ortak Havuz (Paslaşma Ağı)',
            'sharedProperties' => $sharedProperties
        ]);
    }
}
