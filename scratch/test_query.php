<?php
include 'connect.php';
$q = mysql_query("SELECT tahun, COUNT(*) as c FROM v_gangguanall GROUP BY tahun");
if ($q) {
    while ($d = mysql_fetch_object($q)) {
        echo "Tahun: " . $d->tahun . " | Count: " . $d->c . "\n";
    }
} else {
    echo "Query failed: " . mysql_error() . "\n";
}
?>
