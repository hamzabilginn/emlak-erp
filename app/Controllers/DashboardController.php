<?php
namespace App\Controllers;

use Config\Database;
use PDO;

/**
 * Dashboard (Ana Pano) Controller
 * Giriş yaptıktan sonra ilk açılışta veya menüdeki Dashboard bağlantısına tıklandığında 
 * anlık istatistikleri ve yaklaşan randevuları getirir.
 */
class DashboardController extends BaseController {
    
    public function index(): void {
        // Oturum güvenlik kontrolü
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {
            $this->redirect('/emlak/public/auth/login');
            return;
        }

        $db = Database::getInstance()->getConnection();
        $tenantId = (int)$_SESSION['tenant_id'];

        // 1. Toplam Aktif İlan (for_sale veya for_rent olanlar)
        $stmt = $db->prepare("SELECT COUNT(id) FROM properties WHERE tenant_id = :t AND status IN ('for_sale', 'for_rent')");
        $stmt->execute([':t' => $tenantId]);
        $totalActiveProperties = $stmt->fetchColumn();

        // 2. Toplam Kayıtlı Müşteri
        $stmt = $db->prepare("SELECT COUNT(id) FROM customers WHERE tenant_id = :t");
        $stmt->execute([':t' => $tenantId]);
        $totalCustomers = $stmt->fetchColumn();

        // 3. Eşleşme Bekleyen Talepler (Sadece Alıcı veya Kiracı Olan Müşteriler)
        $stmt = $db->prepare("SELECT COUNT(id) FROM customers WHERE tenant_id = :t AND type IN ('buyer', 'tenant')");
        $stmt->execute([':t' => $tenantId]);
        $pendingMatches = $stmt->fetchColumn();

        // 4. Bu Haftaki Randevular (PostgreSQL week interval)
        $stmt = $db->prepare("SELECT COUNT(id) FROM viewings 
                              WHERE tenant_id = :t AND 
                              viewing_date >= date_trunc('week', CURRENT_DATE) AND 
                              viewing_date < date_trunc('week', CURRENT_DATE) + interval '1 week'");
        $stmt->execute([':t' => $tenantId]);
        $weeklyViewings = $stmt->fetchColumn();

        // 5. Yaklaşan Yer Gösterme Randevuları (Dashboard tablo - Gelecek Zamanlı İlk 5 Kayıt)
        // INNER JOIN sayesinde isimleri çekeriz.
        $sql = "SELECT v.*, c.name as customer_name, c.phone as customer_phone, p.district, p.city 
                FROM viewings v 
                JOIN customers c ON v.customer_id = c.id 
                JOIN properties p ON v.property_id = p.id 
                WHERE v.tenant_id = :t AND v.status = 'Bekliyor' AND v.viewing_date >= CURRENT_TIMESTAMP
                ORDER BY v.viewing_date ASC LIMIT 5";
        $stmt = $db->prepare($sql);
        $stmt->execute([':t' => $tenantId]);
        $upcomingViewings = $stmt->fetchAll();

        // Günlük Ajanda
        $stmt = $db->prepare("SELECT * FROM tasks WHERE tenant_id = :t ORDER BY task_date ASC, is_completed ASC, id DESC LIMIT 20");
        $stmt->execute([':t' => $tenantId]);
        $tasks = $stmt->fetchAll();

        // Tüm verileri hazırladığımız 'dashboard/index.php' view'ına Render ile paslarız.
        $this->render('dashboard/index', [
            'tasks' => $tasks,
            'pageTitle' => 'Ofis Ana Paneli (Dashboard)',
            'totalActiveProperties' => $totalActiveProperties,
            'totalCustomers' => $totalCustomers,
            'pendingMatches' => $pendingMatches,
            'weeklyViewings' => $weeklyViewings,
            'upcomingViewings' => $upcomingViewings
        ]);
    }

    public function addTask(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['tenant_id'])) {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        $description = trim($_POST['description'] ?? '');
        $taskDate = trim($_POST['task_date'] ?? date('Y-m-d'));

        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Açıklama boş olamaz.']);
            return;
        }

        $db = \Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO tasks (tenant_id, task_date, description, is_completed) VALUES (:t, :date, :desc, false) RETURNING id");
        $stmt->execute([
            ':t' => $_SESSION['tenant_id'],
            ':date' => $taskDate,
            ':desc' => $description
        ]);

        $newId = $stmt->fetchColumn();

        echo json_encode(['success' => true, 'id' => $newId]);
    }

    public function completeTask(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['tenant_id'])) {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $completed = filter_var($_POST['is_completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $db = \Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tasks SET is_completed = :c WHERE id = :id AND tenant_id = :t");
        $stmt->execute([
            ':c' => $completed ? 'true' : 'false',
            ':id' => $id,
            ':t' => $_SESSION['tenant_id']
        ]);

        echo json_encode(['success' => true]);
    }

}