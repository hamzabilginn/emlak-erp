<?php
$file = "resources/views/properties/index.php";
$content = file_get_contents($file);
$content = str_replace(
    '<a href="#" class="btn btn-sm btn-outline-secondary rounded-circle" title="Detay">',
    '<a href="/emlak/public/portfoy-duzenle/<?= $prop[\'id\'] ?>" class="btn btn-sm btn-outline-secondary rounded-circle" title="Detay">',
    $content
);
$content = str_replace(
    '<a href="#" class="btn btn-sm btn-outline-primary rounded-circle ms-1" title="DÃ¼zenle">',
    '<a href="/emlak/public/portfoy-duzenle/<?= $prop[\'id\'] ?>" class="btn btn-sm btn-outline-primary rounded-circle ms-1" title="Düzenle">',
    $content
);
// fix encoding generally on that line if already corrupted
$content = preg_replace('/<a href="#" class="btn btn-sm btn-outline-primary rounded-circle ms-1" title="[^"]*">/i', '<a href="/emlak/public/portfoy-duzenle/<?= $prop[\'id\'] ?>" class="btn btn-sm btn-outline-primary rounded-circle ms-1" title="Düzenle">', $content);

file_put_contents($file, $content);
echo "Changed links in index.php\n";