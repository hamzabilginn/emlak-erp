<?php
/**
 * Test Verisi Ekleme Scripti (Seeder)
 * PostgreSQL veritabanımızı sahte (mock) ama kullanışlı "Tenant" ve "User" verileriyle doldurur.
 */

require_once __DIR__ . '/../config/Database.php';

// String FQCN: IDE (Intelephense P1009) uyumu; PHP 8+ $class::statikCagri desteklenir
$dbClass = 'Config\\Database';

try {
    $db = $dbClass::getInstance()->getConnection();
    if (!$db instanceof \PDO) {
        fwrite(STDERR, "Veritabanı PDO bağlantısı alınamadı.\n");
        exit(1);
    }
    echo "Veritabanı bağlantısı başarılı!\n";
    echo "Test verileri ekleniyor...\n";
    echo str_repeat("-", 40) . "\n";

    // 1. Yeni bir ofis (Tenant) oluştur (Eğer daha önce eklenmemişse)
    $checkTenant = $db->query("SELECT id FROM tenants WHERE name = 'Reyhan Gayrimenkul'")->fetch();
    $tenantId = null;

    if ($checkTenant) {
        $tenantId = $checkTenant['id'];
        echo "[BİLGİ] 'Reyhan Gayrimenkul' zaten mevcut. (ID: $tenantId)\n";
    } else {
        // PostgreSQL'de son eklenen ID'yi güvenli şekilde almak için "RETURNING id" kullanılır.
        $stmt = $db->prepare("INSERT INTO tenants (name, status) VALUES (:name, 'active') RETURNING id");
        $stmt->execute([':name' => 'Reyhan Gayrimenkul']);
        $tenant = $stmt->fetch();
        $tenantId = $tenant['id'];
        echo "[BAŞARILI] Ofis eklendi: Reyhan Gayrimenkul (Tenant ID: $tenantId)\n";
    }

    // 2. Bu Tenant'a ait Admin yetkili bir kullanıcı oluştur
    $checkUser = $db->query("SELECT id FROM users WHERE email = 'admin@reyhan.com'")->fetch();

    if ($checkUser) {
        echo "[BİLGİ] 'admin@reyhan.com' kullanıcısı zaten mevcut.\n";
    } else {
        // Güvenli şifreleme! (Asla clear text şifre girmemeliyiz)
        $passwordHash = password_hash('123456', PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (tenant_id, name, email, password, role) 
                VALUES (:tenant_id, 'Admin', 'admin@reyhan.com', :password, 'admin') RETURNING id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':password' => $passwordHash
        ]);
        $user = $stmt->fetch();
        
        echo "[BAŞARILI] Admin kullanıcısı eklendi. (ID: {$user['id']}) Şifre: 123456\n";
    }

    echo str_repeat("-", 40) . "\n";
    echo "Seeder işlemi başarıyla tamamlandı. Artık login yapabilirsiniz!\n";

} catch (\PDOException $e) {
    die("Kritik Veritabanı Hatası: " . $e->getMessage() . "\n");
} catch (\Exception $e) {
    die("Sistem Hatası: " . $e->getMessage() . "\n");
}
