<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class HomeController extends BaseController {
    
    public function index(): void {
        $db = Database::getInstance()->getConnection();
        
        // Aktif ofisleri ve o ofise ait satılık/kiralık ilan sayılarını getiriyoruz.
        $sql = "SELECT t.id, t.name, 
                (SELECT COUNT(*) FROM properties p WHERE p.tenant_id = t.id AND p.status = 'for_sale') as for_sale_count,
                (SELECT COUNT(*) FROM properties p WHERE p.tenant_id = t.id AND p.status = 'for_rent') as for_rent_count
                FROM tenants t 
                WHERE t.status = 'active' 
                ORDER BY t.name ASC";
        
        $stmt = $db->query($sql);
        $tenants = $stmt->fetchAll();

        $featuredStmt = $db->query("SELECT p.id, p.slug, p.title, p.city, p.district, p.price, p.status, p.category, p.tenant_id, (SELECT image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_cover DESC, id ASC LIMIT 1) AS cover_image FROM properties p WHERE p.status IN ('for_sale', 'for_rent') ORDER BY p.id DESC LIMIT 4");
        $featuredProperties = $featuredStmt->fetchAll();

        // BaseController render metodu ile sayfayı gösteriyoruz (layout içine sarılmaz)
        $data = [
            'pageTitle' => 'Emlak Platformu - Güvenilir Emlak Ofisleri',
            'metaDescription' => 'Ankara emlakçı, Keçiören emlakçı arayışınız için en güncel ilanlar. Tüm emlak ofislerini tek platformda görüntüleyin ve kiralık, satılık ev arayışınızı kolaylaştırın.',
            'tenants' => $tenants,
            'featuredProperties' => $featuredProperties
        ];
        
        // Bu controller'ın render edilmesi için view'ın doğrudan çalıştırılmasını sağlıyoruz,
        // zira BaseController'daki render() metodu "layouts/main.php" sarabiliyor, 
        // home/index için bu layoutu es geçmek isteyebiliriz. Biz BaseController'da 'home' da hariç tutmalıyız veya doğrudan include etmeliyiz.
        // public/index.php üzerinden çalışacağı için:
        extract($data);
        require_once dirname(dirname(__DIR__)) . '/resources/views/home/index.php';
    }
}
