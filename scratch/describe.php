<?php
include __DIR__ . '/../connect.php';
$q = mysql_query("DESCRIBE datagangguan");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
