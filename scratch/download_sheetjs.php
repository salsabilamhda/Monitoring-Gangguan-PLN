<?php
$url = "https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js";
$dest = __DIR__ . "/../assets/js/xlsx.full.min.js";

echo "Downloading SheetJS from $url...\n";
$content = file_get_contents($url);
if ($content !== false) {
    if (file_put_contents($dest, $content)) {
        echo "Successfully saved SheetJS to assets/js/xlsx.full.min.js\n";
    } else {
        echo "Failed to save file.\n";
    }
} else {
    echo "Failed to download file content.\n";
}
?>
