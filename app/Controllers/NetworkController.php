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

        // Diğer ofislerin (tenant_id != current) 'is_shared_pool' = true olan satış/kiralık ilanlarını getir.
        // tenant tablosundan ofis adı ve (varsa) telefonu JOIN ile birleştirilir.
        $sql = "SELECT p.*, t.name as office_name, t.phone as office_phone
                FROM properties p
                INNER JOIN tenants t ON p.tenant_id = t.id
                WHERE p.is_shared_pool = TRUE 
                
                AND p.status IN ('for_sale', 'for_rent')
                ORDER BY p.created_at DESC";
        
        $stmt = $db->prepare($sql);
        
        $stmt->execute();
        
        $sharedProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('network/index', [
            'pageTitle' => 'Ortak Havuz (Paslaşma Ağı)',
            'sharedProperties' => $sharedProperties
        ]);
    }
}
