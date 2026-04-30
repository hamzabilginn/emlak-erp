<?php
namespace App\Models;

use PDO;

class ViewingModel extends BaseModel {
    protected string $table = 'viewings';

    /**
     * Yer Gösterme Randevularını Müşteri ve İlan bilgileriyle (JOIN) birleştirerek getirir.
     * SaaS Güvenliği için WHERE tenant_id filtresi kuralı korunur.
     */
    public function getViewingsWithDetails(): array {
        $tenantId = $this->getTenantId();
        
        $sql = "SELECT v.*, 
                       c.name as customer_name, c.phone as customer_phone, 
                       p.category, p.city, p.district, p.price 
                FROM viewings v
                INNER JOIN customers c ON v.customer_id = c.id
                INNER JOIN properties p ON v.property_id = p.id
                WHERE v.tenant_id = :tenant_id
                ORDER BY v.viewing_date ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Tekil bir sözleşme dökümü çıktısı için id bazlı detay getirir.
     */
    public function getViewingDocumentData(int $id): ?array {
        $tenantId = $this->getTenantId();
        
        $sql = "SELECT v.*, 
                       c.name as customer_name, c.phone as customer_phone, c.id as customer_no,
                       p.category, p.city, p.district, p.price, p.id as property_no,
                       t.name as tenant_name
                FROM viewings v
                INNER JOIN customers c ON v.customer_id = c.id
                INNER JOIN properties p ON v.property_id = p.id
                INNER JOIN tenants t ON v.tenant_id = t.id
                WHERE v.id = :id AND v.tenant_id = :tenant_id
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
