<?php
$lines = file(__DIR__ . '/../assets/css/style.css');
foreach ($lines as $i => $line) {
    if (strpos($line, 'footer') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
?>
