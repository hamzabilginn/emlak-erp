<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use Config\Database;
use PDO;

class CustomerController extends BaseController {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {
            $this->redirect('/emlak/public/auth/login');
        }
    }

    /**
     * Müşterileri Listeleyen DataTables formatındaki ana metod
     */
    public function index(): void {
        $model = new CustomerModel();
        $customers = $model->getAll();

        $this->render('customers/index', [
            'pageTitle' => 'Müşteri Yönetimi (CRM)',
            'customers' => $customers
        ]);
    }

    /**
     * Yeni Müşteri (CRM) Ekleme View'ını çağırır
     */
    public function create(): void {
        $this->render('customers/create', [
            'pageTitle' => 'Yeni Müşteri Ekle'
        ]);
    }

    /**
     * Müşteriyi veritabanına JSON array mantığıyla güvenle (Odalara, Bütçeye göre) ekler.
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/emlak/public/customer/index');
            return;
        }

        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING) ?? 'buyer';

        $data = [
            'name'  => trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING)),
            'phone' => trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING)),
            'type'  => $type
        ];

        // SADECE Alıcı ve Kiracı ise JSONB demand_details kısmını okuyup işleyelim. Satıcıların talebi olmaz kendi mülkü olur.
        $demandDetails = [];
        if (in_array($type, ['buyer', 'tenant'])) {
            $rawDetails = [
                'min_price' => $_POST['min_price'] ?? null,
                'max_price' => $_POST['max_price'] ?? null,
                'city'      => $_POST['city'] ?? null,
                'district'  => $_POST['district'] ?? null,
                'rooms'     => $_POST['rooms'] ?? null
            ];
            
            // Kullanıcının boş bıraktığı alanları veritabanına taşımamak için filtreliyoruz
            $demandDetails = array_filter($rawDetails, function($val) {
                return $val !== null && $val !== '';
            });
        }

        // Türkçe karakterleri bozmadan PostgreSQL JSONB sütununa dizi encode'luyoruz.
        $data['demand_details'] = !empty($demandDetails) ? json_encode($demandDetails, JSON_UNESCAPED_UNICODE) : null;

        $model = new CustomerModel();
        
        if ($model->insert($data)) {
            $_SESSION['success'] = "Müşteri, Portföy veritabanınıza başarıyla kaydedildi.";
        } else {
            $_SESSION['error'] = "Müşteri kaydedilirken bir hata oluştu.";
        }

        $this->redirect('/emlak/public/customer/index');
    }

    /**
     * AKILLI EŞLEŞTİRME (Matchmaking) ALGORİTMASI:
     * JSONB yetenekleriyle Müşterilerin istek dizisini -> İlanların Veritabanıyla Sıkı Eşleştirir (Strict JSON Match)
     */
    public function match($id = null): void {
        $customerId = (int) $id;
        
        if ($customerId <= 0) {
            $this->redirect('/emlak/public/customer/index');
            return;
        }

        $model = new CustomerModel();
        $customer = $model->getById($customerId);

        if (!$customer || !in_array($customer['type'], ['buyer', 'tenant'])) {
            $_SESSION['error'] = "Akıllı Eşleştirme mekanizması yalnızca Alıcı ve Kiracı tipi müşteriler içindir.";
            $this->redirect('/emlak/public/customer/index');
            return;
        }

        $demands = $model->getDecodedDemandDetails($customer['demand_details']);
        $tenantId = (int)$_SESSION['tenant_id'];
        
        // Alıcıysa 'Satılık' ilanlara baksın, Kiracı ise 'Kiralık' İlanları getirsin.
        $targetStatuses = $customer['type'] === 'buyer' ? "('for_sale')" : "('for_rent')";

        // Hazırlanacak Dinamik PostgreSQL Query'si
        $sql = "SELECT * FROM properties WHERE tenant_id = :tenant_id AND status IN $targetStatuses";
        $params = [':tenant_id' => $tenantId];

        // 1. Şehir / İlçe Kriteri (Büyük/Küçük Harf Toleranslı ILIKE benzeri mantık ile)
        if (!empty($demands['city'])) {
            $sql .= " AND LOWER(city) = LOWER(:city)";
            $params[':city'] = mb_strtolower(trim($demands['city']), 'UTF-8');
        }
        if (!empty($demands['district'])) {
            $sql .= " AND LOWER(district) = LOWER(:district)";
            $params[':district'] = mb_strtolower(trim($demands['district']), 'UTF-8');
        }

        // 2. Fiyat / Bütçe Aralığı Kriteri (PostgreSQL NUMERIC Tipiyle doğal karşılaştırma)
        if (!empty($demands['min_price'])) {
            $sql .= " AND price >= :min_price";
            $params[':min_price'] = (float) $demands['min_price'];
        }
        if (!empty($demands['max_price'])) {
            $sql .= " AND price <= :max_price";
            $params[':max_price'] = (float) $demands['max_price'];
        }

        // 3. PostgreSQL JSONB `->>` Operatörü ile JSON içi "Oda Sayısı" Eşleştirmesi! (En Harika Senior Trick'i)
        // jsonb_column->>'key' metinsel bir sonuç üretir.
        if (!empty($demands['rooms'])) {
            $sql .= " AND details->>'rooms' = :rooms";
            $params[':rooms'] = trim($demands['rooms']);
        }

        // Mülkleri Çekelim (Artan fiyata göre sırala ki en ucuzu en üstte önersin)
        $sql .= " ORDER BY price ASC";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $matchedProperties = $stmt->fetchAll();

        // Müşterinin talepleriyle bulunan son verileri Arayüzümüze Basalım:
        $this->render('customers/match', [
            'pageTitle'  => 'Akıllı Eşleştirme (Match) - ' . htmlspecialchars($customer['name']),
            'customer'   => $customer,
            'demands'    => $demands,
            'properties' => $matchedProperties
        ]);
    }
}
