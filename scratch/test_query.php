<?php
include 'connect.php';
$q = mysql_query("SELECT unit, COUNT(*) as c FROM v_gangguanall WHERE tahun = '2026' GROUP BY unit");
while ($d = mysql_fetch_assoc($q)) {
    echo "unit: " . $d['unit'] . " | count: " . $d['c'] . "\n";
}
?>
