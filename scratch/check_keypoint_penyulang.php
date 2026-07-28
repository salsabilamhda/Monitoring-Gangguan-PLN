<?php
include __DIR__ . '/../connect.php';

$q = mysql_query("SELECT keterangan, kodepenyul, unit FROM kodekeypoint WHERE keterangan LIKE '%KOCOR%' OR keterangan LIKE '%NGRAKET%' OR keterangan LIKE '%PELEM%' OR keterangan LIKE '%TOKAWI%'");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
