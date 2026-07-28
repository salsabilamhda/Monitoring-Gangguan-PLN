<?php
include __DIR__ . '/../connect.php';

$q = mysql_query("SELECT idgangguan, unit, penyulang, keypointid, cuacakode, jeniskode, tglgangguan FROM datagangguan WHERE idgangguan NOT IN (SELECT idgangguan FROM v_datagangguan)");
echo "=== UNMATCHED ROWS ===\n";
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
