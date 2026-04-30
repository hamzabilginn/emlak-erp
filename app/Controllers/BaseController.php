<?php
namespace App\Controllers;

/**
 * Base Controller: Tüm controller'ların türetileceği çekirdek yapı.
 * Temel görevi view (arayüz) dosyalarını `resources/views` dizininden yüklemek
 * ve gerektiğinde projenin farklı noktalarına HTTP yönlendirmesi (redirect) sağlamaktır.
 */
abstract class BaseController {
    
    /**
     * Yönlendirilen View dosyasını çalıştırır ve içine veri paslar.
     * 
     * @param string $view Yüklenmek istenen dosyanın adı (örn. 'auth/login' -> 'resources/views/auth/login.php')
     * @param array $data View içine aktarılmak istenen değişkenler dizisi (örn. ['users' => $userData])
     */
    protected function render(string $view, array $data = []): void {
        // Gelen dizideki elementleri, isimleriyle aynı adlı değişkenlere dönüştür
        // Örn: $data['title'] = "Giriş Yap" -> View içinde '$title' değişkeni olur.
        if (!empty($data)) {
            extract($data);
        }

        // Proje ana dizinini belirten sabitin (BASE_PATH) set edilip edilmediğini kontrol et
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__));
        $file = $base . '/resources/views/' . $view . '.php';

        if (file_exists($file)) {
            // View dosyasını ob_start ile tamponla
            ob_start();
            require_once $file;
            $content = ob_get_clean();

            // Eğer ana layout dosyası varsa, sayfayı içine sar
            $layout = $base . '/resources/views/layouts/main.php';
            
            // Eğer giriş sayfasındaysa (auth/*), veya yazdırılabilir belge (document) sayfasındaysa layout'dan çıksın.
            // Sadece components view'ı ekrana basarak matbaa tasarımına uyum sağlasın.
            if(file_exists($layout) && strpos($view, 'auth/') === false && strpos($view, 'document') === false) {
                require_once $layout;
            } else {
                echo $content;
            }
            
        } else {
            // Güvenlik ve Hata Ayıklama (404 View Error)
            http_response_code(404);
            die("View (Arayüz) Dosyası Bulunamadı: {$view}.php (Aranan Dizin: resources/views/)");
        }
    }

    /**
     * Belirtilen URL'e güvenli HTTP yönlendirmesi sağlar.
     * 
     * @param string $url Örn: '/public/dashboard' veya 'https://example.com'
     */
    protected function redirect(string $url): void {
        // İlgili yapı framework olmadığı için public dizinini de dikkate alıyor
        // Not: Eğer .htaccess ile public route yönlendirmesi tam sağlanmışsa $url yeterlidir.
        header("Location: " . $url);
        exit(); // header işleminden sonra kodun devam etmesini (arka planda çalışmasını) kesin olarak kes.
    }
}