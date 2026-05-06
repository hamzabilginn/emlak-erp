<?php
namespace App\Controllers;

use Config\Database;
use PDO;
use Exception;

class RegisterController extends BaseController {
    
    public function index(): void {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/emlak/public/property/index');
            return;
        }

        // Render register page using render method but we will handle layout in BaseController
        $this->render('register/index', [
            'pageTitle' => 'Yeni Şube Kaydı | Emlak CRM'
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/register/index');
            return;
        }

        $tenantName = trim($_POST['tenant_name'] ?? '');
        $adminName = trim($_POST['admin_name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password'] ?? '');

        if (empty($tenantName) || empty($adminName) || !$email || empty($password)) {
            $_SESSION['error'] = 'Lütfen tüm alanları doldurun.';
            $this->redirect('/emlak/public/register/index');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
            $this->redirect('/emlak/public/register/index');
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Email kontrolü
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Bu e-posta adresi zaten kullanımda.';
            $this->redirect('/emlak/public/register/index');
            return;
        }

        try {
            $db->beginTransaction();

            // 1. Tenant (Şube) Oluştur
            $stmt = $db->prepare("INSERT INTO tenants (name, status) VALUES (:name, 'active') RETURNING id");
            $stmt->execute([':name' => $tenantName]);
            $tenantId = $stmt->fetchColumn();

            // 2. Admin Kullanıcısını Oluştur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (tenant_id, name, email, password, role) VALUES (:tenant_id, :name, :email, :password, 'admin')");
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':name' => $adminName,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            $db->commit();

            $_SESSION['success'] = 'Şubeniz başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.';
            $this->redirect('/emlak/public/auth/login');

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
            $this->redirect('/emlak/public/register/index');
        }
    }
}
