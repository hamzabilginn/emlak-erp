<?php
namespace App\Models;

use PDO;

/**
 * Esnaf Kasası (Gelir/Gider Defteri) Modeli
 * Sadece emlak ofisinin günlük gelir ve gider hareketlerini basitçe tutar.
 */
class CashboxModel extends BaseModel {
    protected string $table = 'cashbox';
    protected array $fillable = ['tenant_id', 'type', 'category', 'amount', 'description', 'transaction_date'];

    /**
     * Tüm İşlemleri Tarihe Göre Yeniden Eskiye Getir (Limit Seçenekli)
     */
    public function getAllTransactions(int $limit = 100): array {
        $tenantId = $this->getTenantId();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY transaction_date DESC LIMIT :limit");
        
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Bu ayki toplam Kazanç (Gelir) - PostgreSQL
     * date_trunc('month', CURRENT_DATE) kullanarak o ayki kayıtları filtreler.
     */
    public function getMonthlyIncome(): float {
        $tenantId = $this->getTenantId();
        $sql = "SELECT SUM(amount) FROM {$this->table} 
                WHERE tenant_id = :tenant_id AND type = 'Gelir' 
                AND transaction_date >= date_trunc('month', CURRENT_DATE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        
        $result = $stmt->fetchColumn();
        return $result ? (float)$result : 0.0;
    }

    /**
     * Bu ayki toplam Gider - PostgreSQL
     * date_trunc('month', CURRENT_DATE) kullanarak o ayki kayıtları filtreler.
     */
    public function getMonthlyExpense(): float {
        $tenantId = $this->getTenantId();
        $sql = "SELECT SUM(amount) FROM {$this->table} 
                WHERE tenant_id = :tenant_id AND type = 'Gider' 
                AND transaction_date >= date_trunc('month', CURRENT_DATE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        
        $result = $stmt->fetchColumn();
        return $result ? (float)$result : 0.0;
    }
}
