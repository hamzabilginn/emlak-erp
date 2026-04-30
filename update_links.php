<?php
$file = "resources/views/layouts/main.php";
$content = file_get_contents($file);

$replaces = [
    "/emlak/public/dashboard" => "/emlak/public/ana-pano",
    "/emlak/public/property/index" => "/emlak/public/portfoyler",
    "/emlak/public/customer/index" => "/emlak/public/musteriler",
    "/emlak/public/viewing/index" => "/emlak/public/yer-gosterme",
    "/emlak/public/network/index" => "/emlak/public/ortak-havuz",
    "/emlak/public/cashbox/index" => "/emlak/public/esnaf-kasasi",
    "/emlak/public/auth/logout" => "/emlak/public/cikis-yap",
    "/emlak/public/showcase/index" => "/emlak/public/vitrin",
];

$content = str_replace(array_keys($replaces), array_values($replaces), $content);
file_put_contents($file, $content);
echo "main.php updated\n";

$fileDash = "resources/views/dashboard/index.php";
if(file_exists($fileDash)){
    $content = file_get_contents($fileDash);
    $content = str_replace(array_keys($replaces), array_values($replaces), $content);
    $content = str_replace("/emlak/public/viewing/create", "/emlak/public/yer-gosterme-ekle", $content);
    $content = str_replace("/emlak/public/property/create", "/emlak/public/portfoy-ekle", $content);
    file_put_contents($fileDash, $content);
}