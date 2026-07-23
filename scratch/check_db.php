<?php
include __DIR__ . '/../connect.php';

echo "=== KODEUNIT ===\n";
$q = mysql_query("SELECT * FROM kodeunit LIMIT 5");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}

echo "=== V_PENYULANG ===\n";
$q = mysql_query("SELECT * FROM v_penyulang LIMIT 5");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}

echo "=== V_KEYPOINT ===\n";
$q = mysql_query("SELECT * FROM v_keypoint LIMIT 5");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}

echo "=== KODECUACA ===\n";
$q = mysql_query("SELECT * FROM kodecuaca LIMIT 5");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}

echo "=== KODEJENISGANGGUAN ===\n";
$q = mysql_query("SELECT * FROM kodejenisgangguan LIMIT 5");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
