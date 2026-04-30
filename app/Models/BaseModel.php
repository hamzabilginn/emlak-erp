<?php
namespace App\Models;

use Config\Database;
use PDO;

/**
 * Base Model: Bütün modellerin (Customer, Property vb.) türetileceği çekirdek yapı.
 * SaaS Koruması: Bütün CRUD işlemlerinde (Insert, Select, Update, Delete) o an oturum açmış kullanıcının
 * $_SESSION['tenant_id'] verisi zorunlu olarak koşullara eklenir. Böylece dükkanlar birbirlerinin verisini göremez.
 */
abstract class BaseModel {
    protected ?PDO $db;
    protected string $table = ''; // Modelin işlem yapacağı tablo adı (Çocuk sınıfta tanımlanmalı)

    public function __construct() {
        // Ortak PDO Bağlantısını al
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Güvenlik Kontrolü: İşlem yapan kullanıcının bir dükkana (tenant) bağlı olup olmadığını denetler.
     */
    protected function getTenantId(): int {
        if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id'])) {
            throw new \Exception("Güvenlik İhlali: Aktif dükkan (tenant) kimliği bulunamadı! Lütfen giriş yapın.");
        }
        return (int) $_SESSION['tenant_id'];
    }

    /**
     * İlgili tenant'a ait tüm kayıtları getirir.
     */
    public function getAll(): array {
        $tenantId = $this->getTenantId();
        
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Belirli bir ID'ye sahip kaydı getirir (Yine tenant kontrolüyle).
     */
    public function getById(int $id): ?array {
        $tenantId = $this->getTenantId();
        
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Yeni bir kayıt ekler (Tenant ID otomatik atanır).
     */
    public function insert(array $data): bool|int {
        // Güvenlik: Kullanıcının kendi tenant_id'sini belirlemesine izin verme, session'dan al
        $data['tenant_id'] = $this->getTenantId();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Sadece ilgili tenant'a ait olan bir kaydı günceller.
     */
    public function update(int $id, array $data): bool {
        $tenantId = $this->getTenantId();
        
        // tenant_id güncellenmesine izin verme (Güvenlik)
        if (isset($data['tenant_id'])) {
            unset($data['tenant_id']);
        }

        $setParams = [];
        foreach ($data as $key => $value) {
            $setParams[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParams);
        
        // WHERE şartına tenant_id'yi de ekliyoruz! (Kritik)
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Sadece ilgili tenant'a ait kaydı siler.
     */
    public function delete(int $id): bool {
        $tenantId = $this->getTenantId();
        
        // Yine tenant_id kontrolü var, başka tenant'ın datasını silemez!
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
