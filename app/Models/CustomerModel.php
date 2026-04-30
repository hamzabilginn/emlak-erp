<?php
namespace App\Models;

class CustomerModel extends BaseModel {
    protected string $table = 'customers';

    /**
     * PostgreSQL'deki demand_details (JSONB) sütununu PHP dizisine çeviren yardımcı metod
     * @param string|null $json
     * @return array
     */
    public function getDecodedDemandDetails(?string $json): array {
        if (!$json) return [];
        return json_decode($json, true) ?? [];
    }
}
