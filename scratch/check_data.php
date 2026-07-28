<?php
include __DIR__ . '/../connect.php';
$q = mysql_query("SELECT idgangguan, unit, penyulang, keypointid, kat_gangguan FROM datagangguan LIMIT 10");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
