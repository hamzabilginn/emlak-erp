<?php

namespace App\Models;

use PDO;
use Exception;

abstract class BaseModel {
    protected ?PDO $db;
    protected string $table = '';

    public function __construct() {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    public function getDb(): ?PDO {
        return $this->db;
    }

    protected function getTenantId(): int {
        if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id'])) {
            throw new Exception("Güvenlik İhlali: Aktif dükkan (tenant) kimliği bulunamadı!");
        }
        return (int) $_SESSION['tenant_id'];
    }
}