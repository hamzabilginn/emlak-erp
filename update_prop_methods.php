<?php
$file = "app/Controllers/PropertyController.php";
$content = file_get_contents($file);

$methods = "    /**
     * Portföy Düzenleme / Detay Görünümü
     */
    public function edit(int \$id): void {
        \$model = new PropertyModel();
        \$property = \$model->getById(\$id);

        if (!\$property) {
            \$_SESSION['error'] = 'Kayıt bulunamadı veya bu kaydı görme yetkiniz yok.';
            \$this->redirect('/emlak/public/portfoyler');
            return;
        }

        \$this->render('properties/edit', [
            'pageTitle' => 'İlan Düzenle & Detay',
            'property' => \$property
        ]);
    }

    /**
     * Düzenleme POST
     */
    public function update(int \$id): void {
        if (\$_SERVER['REQUEST_METHOD'] !== 'POST') {
             \$this->redirect('/emlak/public/portfoyler');
             return;
        }

        \$model = new PropertyModel();
        
        // Form Verileri (Gelişmiş filter eklenebilir)
        \$data = [
            'category' => \$_POST['category'] ?? 'residential',
            'status'   => \$_POST['status'] ?? 'for_sale',
            'city'     => \$_POST['city'] ?? '',
            'district' => \$_POST['district'] ?? '',
            'price'    => (float) (\$_POST['price'] ?? 0),
            'is_shared_pool' => isset(\$_POST['is_shared_pool']) ? 1 : 0,
            'key_status' => \$_POST['key_status'] ?? 'Bizde',
            'key_number' => \$_POST['key_number'] ?? ''
        ];

        \$detailsArray = \$_POST['details'] ?? [];
        \$detailsClean = array_filter(\$detailsArray, function(\$value) {
            return \$value !== null && \$value !== ''; 
        });
        \$data['details'] = json_encode(\$detailsClean, JSON_UNESCAPED_UNICODE);

        if (\$model->update(\$id, \$data)) {
            \$_SESSION['success'] = 'Emlak kaydı güncellendi.';
        } else {
             \$_SESSION['error'] = 'Güncelleme hatası.';
        }
        \$this->redirect('/emlak/public/portfoyler');
    }
";

$content = preg_replace('/}\s*$/', "\n" . $methods . "\n}", $content);
file_put_contents($file, $content);
echo "Added edit/update logic.\n";