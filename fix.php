<?php
$file = "resources/views/layouts/main.php";
$content = file_get_contents($file);

// Fix previously broken Turkish characters
$replaces = [
    "MenÃ¼" => "Menü",
    "KullanÄ±cÄ±nÄ±n" => "Kullanıcının",
    "Ä°stenirse" => "İstenirse",
    "GÃ¶sterebiliriz" => "Gösterebiliriz",
    "PortfÃ¶yler" => "Portföyler",
    "MÃ¼ÅŸteriler" => "Müşteriler",
    "Ã‡Ä±kÄ±ÅŸ" => "Çıkış",
    "ÄŸ" => "ğ", "ÅŸ" => "ş", "Ä±" => "ı", "Ã¶" => "ö", "Ã¼" => "ü", "Ã§" => "ç", "Ä°" => "İ", "Ã–" => "Ö"
];
$content = str_replace(array_keys($replaces), array_values($replaces), $content);

// Now we inject the links.
$search = '<a href="/emlak/public/customer/index"><i class="bi bi-people"></i> Müşteriler</a>';
$replace = $search . "\n        <hr class=\"border-secondary mx-3 my-2\" style=\"opacity:0.3;\">\n        <a href=\"/emlak/public/viewing/index\"><i class=\"bi bi-calendar-event\"></i> Yer Gösterme</a>\n        <a href=\"/emlak/public/network/index\"><i class=\"bi bi-globe\"></i> Ortak Havuz</a>\n        <a href=\"/emlak/public/cashbox/index\" class=\"text-warning\"><i class=\"bi bi-cash-stack\"></i> Esnaf Kasası</a>";

if (strpos($content, "Esnaf Kasası") === false) {
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        echo "Links added.\n";
    } else {
        echo "Could not find target to add links.\n";
    }
} else {
    echo "Links already present.\n";
}

file_put_contents($file, $content);
echo "Done.\n";