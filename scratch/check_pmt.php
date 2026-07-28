<?php
include __DIR__ . '/../connect.php';

$q = mysql_query("SELECT COUNT(*) as count FROM datagangguan WHERE kat_gangguan = 'PMT' AND keypointid != '' AND keypointid != '0'");
$r = mysql_fetch_assoc($q);
echo "PMT with keypoint: " . $r['count'] . "\n";

$q = mysql_query("SELECT COUNT(*) as count FROM datagangguan WHERE kat_gangguan = 'PMT'");
$r = mysql_fetch_assoc($q);
echo "Total PMT: " . $r['count'] . "\n";

$q = mysql_query("SELECT COUNT(*) as count FROM datagangguan WHERE kat_gangguan = 'REC' AND keypointid != '' AND keypointid != '0'");
$r = mysql_fetch_assoc($q);
echo "REC with keypoint: " . $r['count'] . "\n";
?>
