<?php
/**
 * Otomatik Veritabanı Kurucu (Migration Runner)
 * database/migrations dizininde bulunan tüm .sql uzantılı dosyaları okur
 * ve sırasıyla "Database" bağlantısını kullanarak veritabanında çalıştırır.
 */

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Veritabanı bağlantısı başarılı!\n";
    echo "Migration işlemleri başlatılıyor...\n";
    echo str_repeat("-", 40) . "\n";

    $migrationsDir = __DIR__ . '/../database/migrations/';

    if (!is_dir($migrationsDir)) {
         throw new \Exception("Migration dizini bulunamadı: " . $migrationsDir);
    }

    // scandir tabanlı güvenilir yöntem: .sql ile biten dosyaları bul
    $files = scandir($migrationsDir);
    $sqlFiles = array_filter($files, function($file) {
         return strpos($file, '.sql') !== false;
    });

    // Dosyaları isim sırasına göre sırala (01_, 02_ vb düzenine uymak için)
    sort($sqlFiles);

    foreach ($sqlFiles as $filename) {
        $filePath = $migrationsDir . $filename;
        
        if (is_file($filePath)) {
            echo "[" . date('H:i:s') . "] Dosya okunuyor: {$filename} ... ";
            
            $sqlContent = file_get_contents($filePath);
            
            // Eğer dosya boşsa atla
            if (trim($sqlContent) === '') {
                 echo "BOŞ DOSYA (YOK SAYILDI)\n";
                 continue;
            }

            try {
                // PDO ile SQL komutunu çalıştır
                $db->exec($sqlContent);
                echo "BAŞARILI!\n";
            } catch (\PDOException $e) {
                echo "HATA!\n";
                echo "-> Detay: " . $e->getMessage() . "\n";
                // İşlemi durdurmak yerine diğer dosyalara geçiyoruz, PostgreSQL hata sonrasında devam edebilsin diye
            }
        }
    }

    echo str_repeat("-", 40) . "\n";
    echo "Tüm migration dosyaları tarandı. Kurulum tamamlandı.\n";

} catch (\Exception $e) {
    die("Kritik Hata: " . $e->getMessage() . "\n");
}
