<?php
// PHP hatalarını göster (Geliştirme ortamı için - Canlıda kapatılmalı)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session başlatma
session_start();

// Temel Dizin Tanımlaması (Linux'ta DOCUMENT_ROOT = .../public ise yedek yol)
$__base = dirname(__DIR__);
$__dash = $__base . '/app/Controllers/DashboardController.php';
if (!is_readable($__dash) && !empty($_SERVER['DOCUMENT_ROOT'])) {
    $__doc = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $__alt = dirname($__doc);
    if ($__alt !== '/' && is_readable($__alt . '/app/Controllers/DashboardController.php')) {
        $__base = $__alt;
    }
}
define('BASE_PATH', $__base);

// Web kökü: Render’da /index.php → ''; XAMPP’ta /emlak/public
$__scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
define('APP_WEB_BASE', ($__scriptDir === '/' || $__scriptDir === '.') ? '' : rtrim($__scriptDir, '/'));

require_once BASE_PATH . '/config/url.php';

// Basit Autoloader (Composer kullanmadan kendi sınıflarımızı yüklemek için)
spl_autoload_register(function ($class) {
    $class = ltrim((string) $class, '\\');
    $rel = str_replace('\\', '/', $class) . '.php';
    // Önemli: preg_replace sadece göreli yolda — BASE_PATH . '/App/...' ile ^App/ asla eşleşmezdi
    $rel = preg_replace('#^App/#', 'app/', $rel);
    $rel = preg_replace('#^Config/#', 'config/', $rel);
    $rel = preg_replace('#^Models/#', 'app/Models/', $rel);
    $file = BASE_PATH . '/' . $rel;

    if (is_readable($file)) {
        require_once $file;
    }
});

/**
 * --- BASİT YÖNLENDİRİCİ (ROUTER) MİMARİSİ ---
 * Gelen isteği parçalar ve ilgili Controller ve Metoda yönlendirir.
 * URL Yapısı: localhost/emlak/public/controller_adi/metod_adi/parametreler
 */

// İsteği al ve temel dizini (Örn: /emlak/public/) atarak parse et
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // /emlak/public

if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    $uri = substr($requestUri, strlen($scriptName));
} else {
    $uri = $requestUri;
}

$uri = trim($uri, '/');

// Eğer uri sonunda .php varsa kaldır
$uri = preg_replace('/\.php$/', '', $uri);

// Rewrite (htaccess) üzerinden gelen 'url' parametresini (Türkçe routing için) öncelikli olarak kullan:
if (isset($_GET['url'])) {
    $uri = trim($_GET['url'], '/');
}

$segments = explode('/', $uri);

// Kök URL: Dashboard (oturum yoksa Auth'a yönlendirir). HomeController dosyasına bağımlılık olmasın diye burada tanımlandı.
$controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'DashboardController';
$methodName = !empty($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Controller Sınıfının Namespace ile Belirlenmesi
$controllerClass = "\\App\\Controllers\\" . $controllerName;

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    
    if (method_exists($controller, $methodName)) {
        // İlgili metodu varsa parametreleriyle birlikte çağırıyoruz
        call_user_func_array([$controller, $methodName], $params);
    } else {
        http_response_code(404);
        echo "404 - Metot bulunamadı: {$methodName}";
    }
} else {
    http_response_code(404);
    echo "404 - Sayfa bulunamadı: {$controllerName}";
}
