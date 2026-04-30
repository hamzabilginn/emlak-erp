<?php
$file = "resources/views/properties/edit.php";
$content = file_get_contents($file);

$modelVar = '<?= $property[\'id\'] ?>';
$content = str_replace('<form action="/emlak/public/property/store" method="POST">', '<form action="/emlak/public/property/update/'.$modelVar.'" method="POST">', $content);

$content = str_replace('<h2>Yeni İlan / Portföy Ekle</h2>', '<h2>İlanı Düzenle</h2>', $content);
$content = preg_replace('/<h2[^>]*>.*?<\/h2>/is', '<h2 class="mb-0">İlanı Düzenle (#<?= $property[\'id\'] ?>)</h2>', $content);

$content = str_replace('value="residential" selected>Konut<', 'value="residential" <?= $property[\'category\']===\'residential\'?\'selected\':\'\' ?>>Konut<', $content);
$content = str_replace('value="commercial">Ticari', 'value="commercial" <?= $property[\'category\']===\'commercial\'?\'selected\':\'\' ?>>Ticari', $content);
$content = str_replace('value="land">Arsa', 'value="land" <?= $property[\'category\']===\'land\'?\'selected\':\'\' ?>>Arsa', $content);

$content = preg_replace('/name="city"\s+class="[^"]+"\s*(placeholder="[^"]*")?\s*required>/', 'name="city" class="form-control form-control-lg" value="<?= htmlspecialchars((string)$property[\'city\']) ?>" required>', $content);
$content = preg_replace('/name="district"\s+class="[^"]+"\s*(placeholder="[^"]*")?\s*required>/', 'name="district" class="form-control form-control-lg" value="<?= htmlspecialchars((string)$property[\'district\']) ?>" required>', $content);
$content = preg_replace('/name="price"\s+class="[^"]+"\s*(placeholder="[^"]*")?\s*required>/', 'name="price" class="form-control form-control-lg text-success fw-bold" value="<?= (int)$property[\'price\'] ?>" required>', $content);

$content = str_replace('value="for_sale" selected>Satılık', 'value="for_sale" <?= $property[\'status\']===\'for_sale\'?\'selected\':\'\' ?>>Satılık', $content);
$content = str_replace('value="for_rent">Kiralık', 'value="for_rent" <?= $property[\'status\']===\'for_rent\'?\'selected\':\'\' ?>>Kiralık', $content);

$content = str_replace('id="isSharedPool" name="is_shared_pool"', 'id="isSharedPool" name="is_shared_pool" <?= $property[\'is_shared_pool\'] ? \'checked\' : \'\' ?>', $content);

$content = str_replace('Bizde (Kendimde) Asıl', 'Bizde (Kendimde) Asıl', $content); // No logic change needed, handled via preg_replace if needed

// Just add details filling quickly via php head injection
$headInject = <<<EOT
<?php
// Extract JSONB details for the view to auto-fill inputs
\$detailArr = json_decode(\$property['details'], true) ?? [];
\$getDet = function(\$key) use (\$detailArr) { return htmlspecialchars((string)(\$detailArr[\$key] ?? '')); };
?>

EOT;
$content = $headInject . ltrim($content);

$content = str_replace('name="details[rooms]" class="form-control"', 'name="details[rooms]" class="form-control" value="<?= \$getDet(\'rooms\') ?>"', $content);
$content = str_replace('name="details[net_m2]" class="form-control"', 'name="details[net_m2]" class="form-control" value="<?= \$getDet(\'net_m2\') ?>"', $content);
$content = str_replace('name="details[heating]" class="form-control"', 'name="details[heating]" class="form-control" value="<?= \$getDet(\'heating\') ?>"', $content);
$content = str_replace('name="details[building_age]" class="form-control"', 'name="details[building_age]" class="form-control" value="<?= \$getDet(\'building_age\') ?>"', $content);
$content = str_replace('name="details[floor]" class="form-control"', 'name="details[floor]" class="form-control" value="<?= \$getDet(\'floor\') ?>"', $content);

$content = str_replace('<option value="Var">Evet, Eşyalı</option>', '<option value="Var" <?= \$getDet(\'furniture\')===\'Var\'?\'selected\':\'\' ?>>Evet, Eşyalı</option>', $content);
$content = str_replace('<option value="Yok">Hayır, Eşyasız</option>', '<option value="Yok" <?= \$getDet(\'furniture\')===\'Yok\'?\'selected\':\'\' ?>>Hayır, Eşyasız</option>', $content);

$content = str_replace('<option value="Var">Evet, Asansörlü</option>', '<option value="Var" <?= \$getDet(\'elevator\')===\'Var\'?\'selected\':\'\' ?>>Evet, Asansörlü</option>', $content);
$content = str_replace('<option value="Yok">Hayır, Asansör Yok</option>', '<option value="Yok" <?= \$getDet(\'elevator\')===\'Yok\'?\'selected\':\'\' ?>>Hayır, Asansör Yok</option>', $content);


$content = str_replace('Portföye Ekle!', 'Güncellemeleri Kaydet', $content);

file_put_contents($file, $content);
echo "Edit view template injected values.\n";