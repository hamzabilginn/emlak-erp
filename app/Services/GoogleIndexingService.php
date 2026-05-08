<?php
namespace App\Services;

use Google\Client;
use Google\Service\Indexing;
use Exception;

class GoogleIndexingService {
    private Client $client;
    private string $serviceAccountPath;

    public function __construct() {
        // Kök dizindeki google-service-account.json dosyasını kullanıyoruz
        $this->serviceAccountPath = dirname(dirname(__DIR__)) . '/google-service-account.json';
        
        $this->client = new Client();
        
        if (file_exists($this->serviceAccountPath)) {
            $this->client->setAuthConfig($this->serviceAccountPath);
            $this->client->addScope('https://www.googleapis.com/auth/indexing');
        }
    }

    /**
     * Google Indexing API'ye URL bildirimi gönderir.
     * 
     * @param string $url Bildirilecek sayfa URL'si
     * @param string $type İşlem tipi (URL_UPDATED veya URL_DELETED)
     * @return bool Başarı durumu
     */
    public function notify(string $url, string $type = 'URL_UPDATED'): bool {
        if (!file_exists($this->serviceAccountPath)) {
            error_log("Google Indexing API Hatası: google-service-account.json dosyası bulunamadı.");
            return false;
        }

        try {
            $indexing = new Indexing($this->client);
            $urlNotification = new Indexing\UrlNotification();
            $urlNotification->setUrl($url);
            $urlNotification->setType($type);

            $indexing->urlNotifications->publish($urlNotification);
            return true;
        } catch (Exception $e) {
            error_log("Google Indexing API Hatası: " . $e->getMessage());
            return false;
        }
    }
}
