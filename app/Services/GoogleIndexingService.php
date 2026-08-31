<?php

namespace App\Services;

use Google\Client;
use Google\Service\Indexing;

class GoogleIndexingService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        
        // Render'a eklediğin GOOGLE_SERVICE_ACCOUNT_JSON değişkenini okuyoruz
        $jsonKey = getenv('GOOGLE_SERVICE_ACCOUNT_JSON');
        
        if ($jsonKey) {
            // Eğer değişken varsa, içindeki JSON metnini diziye çevirip koda tanıtıyoruz
            $this->client->setAuthConfig(json_decode($jsonKey, true));
        } else {
            // Eğer değişken yoksa (lokal çalışıyorsan), dosya yolundan okumaya devam edebilir
            $this->client->setAuthConfig(dirname(__DIR__, 2) . '/emlakcim-495910-2013f86ca7bb.json');
        }
        
        $this->client->addScope('https://www.googleapis.com/auth/indexing');
    }

    public function updateUrl($url)
    {
        $service = new Indexing($this->client);
        $urlNotification = new \Google\Service\Indexing\UrlNotification();
        $urlNotification->setUrl($url);
        $urlNotification->setType('URL_UPDATED');

        return $service->urlNotifications->publish($urlNotification);
    }

    /**
     * Alias method for notify() - calls updateUrl() internally
     * Used when notifying Google of URL changes
     */
    public function notify($url, $type = 'URL_UPDATED')
    {
        $service = new Indexing($this->client);
        $urlNotification = new \Google\Service\Indexing\UrlNotification();
        $urlNotification->setUrl($url);
        $urlNotification->setType($type);

        return $service->urlNotifications->publish($urlNotification);
    }
}
