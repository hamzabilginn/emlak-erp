<?php
// PHP hatalarını göster (Geliştirme ortamı için - Canlıda kapatılmalı)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session başlatma
session_start();
// --- NEIGHBORHOST EVRENSEL ROUTING BASLANGICI ---
$reqUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = [
    '#^/portfoyler/?$#' => 'property/index',
    '#^/portfoy-ekle/?$#' => 'property/create',
    '#^/portfoy-kaydet/?$#' => 'property/store',
    '#^/portfoy-duzenle/([0-9]+)/?$#' => 'property/edit/$1',
    '#^/portfoy-guncelle/([0-9]+)/?$#' => 'property/update/$1',
    '#^/musteriler/?$#' => 'customer/index',
    '#^/musteri-sahsi/?$#' => 'customer/createIndividual',
    '#^/musteri-kurumsal/?$#' => 'customer/createCorporate',
    '#^/yer-gosterme/?$#' => 'viewing/index',
    '#^/yer-gosterme-ekle/?$#' => 'viewing/create',
    '#^/ortak-havuz/?$#' => 'network/index',
    '#^/esnaf-kasasi/?$#' => 'cashbox/index',
    '#^/kayit-ol/?$#' => 'register/index',
    '#^/kayit-islemi/?$#' => 'register/store',
    '#^/sitemap\.xml$#' => 'sitemap/index',
    '#^/giris-yap/?$#' => 'auth/login',
    '#^/cikis-yap/?$#' => 'auth/logout',
    '#^/ana-pano/?$#' => 'dashboard/index',
    '#^/vitrin/?$#' => 'showcase/index',
    '#^/ping/?$#' => 'ping/index',
    '#^/ilan/.+-([0-9]+)/?$#' => 'showcase/show/$1',
    '#^/emlakci/([a-zA-Z0-9_-]+)-([0-9]+)/?$#' => 'showcase/index&tenant=$2',
];

foreach ($routes as $pattern => $target) {
    if (preg_match($pattern, $reqUri, $matches)) {
        if (isset($matches[1])) $target = str_replace('$1', $matches[1], $target);
        if (isset($matches[2])) $target = str_replace('$2', $matches[2], $target);
        
        if (strpos($target, '&') !== false) {
            $parts = explode('&', $target);
            $_GET['url'] = $parts[0];
            foreach(array_slice($parts, 1) as $param) {
                list($k, $v) = explode('=', $param);
                $_GET[$k] = $v;
            }
        } else {
            $_GET['url'] = $target;
        }
        break;
    }
}
// --- NEIGHBORHOST EVRENSEL ROUTING BITISI ---

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

// Composer Autoloader (Kütüphaneler için)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} else {
    // Hata Ayıklama: Eğer kütüphane hala bulunamıyorsa tam yolu ekrana bas
    die("HATA: Autoloader bulunamadı! Bakılan yol: " . BASE_PATH . '/vendor/autoload.php');
}

// Web kökü: Render’da /index.php → ''; XAMPP’ta /emlak/public
$__scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
define('APP_WEB_BASE', ($__scriptDir === '/' || $__scriptDir === '.') ? '' : rtrim($__scriptDir, '/'));

require_once BASE_PATH . '/config/url.php';

// Config\Database: autoload ÖNCESI yükle (Render/Linux'ta Database.php vs database.php büyük-küçük harf farkı)
foreach (
    [
        BASE_PATH . '/config/database.php',
        BASE_PATH . '/config/Database.php',
    ] as $__dbBootstrap
) {
    if (is_readable($__dbBootstrap)) {
        require_once $__dbBootstrap;
        break;
    }
}

// Basit Autoloader (Composer kullanmadan kendi sınıflarımızı yüklemek için)
spl_autoload_register(function ($class) {
    $class = ltrim((string) $class, '\\');
    $rel = str_replace('\\', '/', $class) . '.php';
    // Önemli: preg_replace sadece göreli yolda — BASE_PATH . '/App/...' ile ^App/ asla eşleşmezdi
    $rel = preg_replace('#^App/#', 'app/', $rel);
    $rel = preg_replace('#^Config/#', 'config/', $rel);
    $rel = preg_replace('#^Models/#', 'app/Models/', $rel);
    $file = BASE_PATH . '/' . $rel;

    if (!is_readable($file) && $class === 'Config\Database') {
        $file = BASE_PATH . '/config/database.php';
    }

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

// public/uploads/* : Dosya varsa doğrudan gönder (yoksa router UploadsController diye 404 veriyordu)
if (!empty($segments[0]) && strcasecmp($segments[0], 'uploads') === 0) {
    foreach ($segments as $seg) {
        if ($seg === '' || str_contains($seg, '..')) {
            http_response_code(400);
            exit;
        }
    }
    $basePublic = realpath(BASE_PATH . '/public');
    if ($basePublic !== false) {
        $candidate = $basePublic . '/' . implode('/', $segments);
        $real = realpath($candidate);
        if ($real !== false && str_starts_with($real, $basePublic) && is_file($real)) {
            $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
            $types = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'webp' => 'image/webp', 'gif' => 'image/gif',
            ];
            header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: public, max-age=86400');
            readfile($real);
            exit;
        }
    }
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Görsel bulunamadı. Render/Docker gibi ortamlarda disk genelde kalıcı değildir; '
        . 'yeniden deploy sonrası yüklenen dosyalar silinir. Çözüm: Render’da Persistent Disk '
        . '(`public/uploads`e bağlayın) veya Supabase Storage / S3 kullanın.';
    exit;
}

// Kök URL: Dashboard (oturum yoksa Auth'a yönlendirir). HomeController dosyasına bağımlılık olmasın diye burada tanımlandı.
$controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
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
