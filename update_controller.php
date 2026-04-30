<?php
$c = file_get_contents('app/Controllers/PropertyController.php');

// Add "use App\Models\PropertyImageModel;" if not present
if (strpos($c, 'use App\Models\PropertyImageModel;') === false) {
    $c = preg_replace('/use App\\\\Models\\\\PropertyModel;/', "use App\\Models\\PropertyModel;\nuse App\\Models\\PropertyImageModel;", $c);
}

// Locate store
$oldStoreSuccess = "if (\$model->insert(\$data)) {\n            // Başarıyı doğrudan global değişkene yazarsak /layouts/main.php bunu tepeden yeşil olarak yakalar\n            \$_SESSION['success'] = \"Yeni emlak kaydı portföye başarıyla eklendi.\";\n        } else {\n            \$_SESSION['error'] = \"Ekleme sırasında veritabanı hatası oluştu.\";\n        }";

$newStoreSuccess = '$propertyId = $model->insert($data);
        if ($propertyId) {
            $this->handleImages($propertyId);
            $_SESSION[\'success\'] = "Yeni emlak kaydı portföye başarıyla eklendi.";
        } else {
            $_SESSION[\'error\'] = "Ekleme sırasında veritabanı hatası oluştu.";
        }';

// Since encoding or small differences might prevent str_replace, let's use a regex replacement as fallback
$c = preg_replace('/if \(\$model->insert\(\$data\)\)\s*\{[^\}]+\}[^\}]+\}/', $newStoreSuccess, $c);

$newUpdateSuccess = 'if ($model->update($id, $data)) {
            $this->handleImages($id);
            $_SESSION[\'success\'] = \'Emlak kaydı güncellendi.\';
        } else {
             $_SESSION[\'error\'] = \'Güncelleme hatası.\';
        }';
$c = preg_replace('/if \(\$model->update\(\$id, \$data\)\)\s*\{[^\}]+\}[^\}]+\}/', $newUpdateSuccess, $c);

// Add the handleImages function before the closing brace of the class
$handleImagesFunc = '
    /**
     * Çoklu resim yükleme işlemini yönetir ve veritabanına PropertyImageModel ile yazar
     */
    protected function handleImages(int $propertyId): void {
        if (!isset($_FILES[\'images\']) || empty($_FILES[\'images\'][\'name\'][0])) {
            return;
        }

        $uploadDir = __DIR__ . \'/../../public/uploads/properties/\';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageModel = new PropertyImageModel();
        
        $fileCount = count($_FILES[\'images\'][\'name\']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES[\'images\'][\'error\'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES[\'images\'][\'tmp_name\'][$i];
                $name    = basename($_FILES[\'images\'][\'name\'][$i]);
                
                // Güvenli dosya adı oluştur
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, [\'jpg\', \'jpeg\', \'png\', \'webp\'])) continue;

                $newName = uniqid("prop_{$propertyId}_") . \'.\' . $ext;
                $targetFile = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    // public altındaki path\'i veritabanına kaydet (/uploads/properties/abc.jpg)
                    $imageModel->addImage($propertyId, \'/uploads/properties/\' . $newName);
                }
            }
        }
    }
}';

$c = preg_replace('/\}\s*$/', $handleImagesFunc, $c);

file_put_contents('app/Controllers/PropertyController.php', $c);
echo "PropertyController updated!\n";