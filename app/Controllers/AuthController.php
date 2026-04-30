<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class AuthController extends BaseController {
    
    /**
     * Oturum açma (Login) sayfasını gösterir.
     */
    public function login(): void {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/emlak/public/property/index'); 
            return;
        }

        // BaseController üzerinden login view dosyasını ekrana (html) basar
        $this->render('auth/login', [
            'pageTitle' => 'Giriş Yap | Emlak CRM'
        ]);
    }

    /**
     * Form gönderildiğinde (POST) e-posta ve şifreyi kontrol edip sistemi başlatır.
     */
    public function authenticate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // CSRF'den vs korumak için SADECE POST isteklerini kabul et.
            $this->redirect('/emlak/public/auth/login');
            return;
        }

        // Giriş verilerini güvenlice al. (Trim ve varsayılan olarak sanitize)
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password'] ?? '');

        if (!$email || empty($password)) {
            $_SESSION['error'] = 'Lütfen tüm alanları doldurun.';
            $this->redirect('/emlak/public/auth/login');
            return;
        }

        // Veritabanından kullanıcıyı bul (PDO ile)
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch();

        // Şifre hash'ini kontrol et (Yeni nesil password_verify kullanarak)
        // DİKKAT: Geliştirme aşamasında şifreyi md5 veya plain text girdiyseniz burada ona göre kıyaslama yapın. 
        // Canlıda her zaman password_hash()!
        if ($user && password_verify($password, $user['password'])) {
            // Şifre doğrulandı, ilgili session'ları (SaaS için en önemlileri) kaydet
            session_regenerate_id(true); // Session Hijacking (Çalma) koruması

            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['name']      = (string) $user['name'];
            $_SESSION['role']      = (string) $user['role'];      // Enum rolü (admin, consultant)
            
            // SAAS GÜVENLİĞİ: Bu kullanıcının sahip olduğu ofis numarası (Bütün sorguları limitleyecek olan ID)
            $_SESSION['tenant_id'] = (int) $user['tenant_id'];

            // Giriş başarılı, panele yönlendir.
            $this->redirect('/emlak/public/property/index');

        } else {
            // E-posta yanlış olsa da güvenlik amaçlı her zaman "bilgiler yanlış" hata mesajını göster.
            $_SESSION['error'] = 'E-posta veya şifre hatalı.';
            $this->redirect('/emlak/public/auth/login');
        }
    }

    public function logout(): void {
        session_destroy();
        $this->redirect('/emlak/public/auth/login');
    }
}
