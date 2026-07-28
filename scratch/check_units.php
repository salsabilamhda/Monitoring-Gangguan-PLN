<?php
include __DIR__ . '/../connect.php';

echo "=== KODE UNIT ===\n";
$q = mysql_query("SELECT * FROM kodeunit");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}

echo "=== COUNT PER UNIT IN DATAGANGGUAN ===\n";
$q2 = mysql_query("SELECT unit, COUNT(*) as qty FROM datagangguan GROUP BY unit");
while ($r = mysql_fetch_assoc($q2)) {
    print_r($r);
}
?>
