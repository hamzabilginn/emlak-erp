<?php
namespace App\Models;

class PropertyModel extends BaseModel {
    protected string $table = 'properties'; // Hangi PostgreSQL tablosuna bağlanacağını tanımladık

    /**
     * PostgreSQL'den gelen JSONB verisini PHP Array array tipine güvenle çeviren yardımcı fonksiyon.
     * Kullanım: Array'de 'details' key'ine sahipsen controller içinde propertyModel->decodeDetails() kullanabiliriz.
     * 
     * @param string|null $json
     * @return array
     */
    public function getDecodedHtmlDetails(?string $json): array {
        if (!$json) return [];
        return json_decode($json, true) ?? [];
    }
}
