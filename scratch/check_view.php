<?php
include __DIR__ . '/../connect.php';

$q = mysql_query("SHOW CREATE VIEW v_datagangguan");
if ($q) {
    $r = mysql_fetch_assoc($q);
    echo $r['Create View'] . "\n";
} else {
    echo "Error: " . mysql_error() . "\n";
}
?>
