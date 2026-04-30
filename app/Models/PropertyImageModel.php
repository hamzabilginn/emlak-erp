<?php
namespace App\Models;

class PropertyImageModel extends BaseModel {
    protected string $table = 'property_images';

    /**
     * İlana ait tüm resimleri getirir, cover olanları en başa alır.
     */
    public function getImagesByPropertyId(int $propertyId): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE property_id = :pid ORDER BY is_cover DESC, id ASC");
        $stmt->execute([':pid' => $propertyId]);
        return $stmt->fetchAll();
    }

    /**
     * İlanın kapak resmini getirir (yoksa varsayılan veya ilkini dönebilirsiniz).
     */
    public function getCoverImage(int $propertyId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE property_id = :pid AND is_cover = TRUE LIMIT 1");
        $stmt->execute([':pid' => $propertyId]);
        $cover = $stmt->fetch();
        
        if (!$cover) {
            // Eğer kapak seçilmemişse, ilk resmi getir.
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE property_id = :pid ORDER BY id ASC LIMIT 1");
            $stmt->execute([':pid' => $propertyId]);
            $cover = $stmt->fetch();
        }
        
        return $cover ?: null;
    }
    
    /**
     * Resim kaydı ekler. Eğer ilanın ilk resmiyse cover yapar.
     */
    public function addImage(int $propertyId, string $imagePath): bool {
        // Kontrol et: Bu ilanın mevcut resmi var mı?
        $existing = $this->getImagesByPropertyId($propertyId);
        $isCover = empty($existing) ? 'true' : 'false';

        // BaseModel tarafında insert için tenant_id gerekli mi? Tabloda yok, doğrudan prepare kullanıyoruz.
        // BaseModel.php'deki default insert tenant_id deniyor. Kendi insert'imizi yazıyoruz.
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (property_id, image_path, is_cover) VALUES (:pid, :path, {$isCover})");
        return $stmt->execute([
            ':pid' => $propertyId,
            ':path' => $imagePath
        ]);
    }
    
    /**
     * İlana ait belirli bir resmi kapak olarak ayarlar. Diğerlerini cover'dan çıkarır.
     */
    public function setCover(int $propertyId, int $imageId): bool {
        try {
            $this->db->beginTransaction();
            // Aynı property'ye ait tüm resimlerin cover'ını false yap
            $stmt = $this->db->prepare("UPDATE {$this->table} SET is_cover = FALSE WHERE property_id = :pid");
            $stmt->execute([':pid' => $propertyId]);
            
            // Seçileni cover yap
            $stmt2 = $this->db->prepare("UPDATE {$this->table} SET is_cover = TRUE WHERE id = :id AND property_id = :pid");
            $stmt2->execute([':id' => $imageId, ':pid' => $propertyId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * İlana ait resmi siler. Eğer silinen resim cover idiyse ve kalan resim varsa diğerini cover yapar.
     */
    public function deleteImage(int $imageId, int $propertyId): bool {
        // Silinecek resmi bilgileri (dosyayı klasörden silmek için path lazım)
        $stmt = $this->db->prepare("SELECT image_path, is_cover FROM {$this->table} WHERE id = :id AND property_id = :pid");
        $stmt->execute([':id' => $imageId, ':pid' => $propertyId]);
        $img = $stmt->fetch();

        if ($img) {
            // Veritabanından sil
            $stmtDel = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $deleted = $stmtDel->execute([':id' => $imageId]);
            
            if ($deleted) {
                // Dosyayı sunucudan sil (Emlak köküne göre)
                $filePath = __DIR__ . '/../../public/' . ltrim($img['image_path'], '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                
                // Eğer sildiğimiz cover ise, kalanlardan herhangi birini cover yap
                if ($img['is_cover']) {
                    $stmtNext = $this->db->prepare("UPDATE {$this->table} SET is_cover = TRUE WHERE property_id = :pid LIMIT 1");
                    $stmtNext->execute([':pid' => $propertyId]);
                }
                
                return true;
            }
        }
        return false;
    }
}
