<?php
$url = "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js";
$dest = __DIR__ . "/../assets/js/sweetalert2.all.min.js";

echo "Downloading SweetAlert2 from $url...\n";
$content = file_get_contents($url);
if ($content !== false) {
    if (file_put_contents($dest, $content)) {
        echo "Successfully saved SweetAlert2 to assets/js/sweetalert2.all.min.js\n";
    } else {
        echo "Failed to save file.\n";
    }
} else {
    echo "Failed to download file content.\n";
}
?>
