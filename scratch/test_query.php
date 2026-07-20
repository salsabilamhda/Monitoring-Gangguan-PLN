<?php
include 'connect.php';
$q = mysql_query("SELECT COUNT(*) FROM kodeunit");
if ($q) {
    $row = mysql_fetch_array($q);
    echo "Count: " . $row[0] . "\n";
} else {
    echo "Query failed: " . mysql_error() . "\n";
}
?>
