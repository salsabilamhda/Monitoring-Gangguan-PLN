<?php
include __DIR__ . '/../connect.php';
$where_sql = "WHERE g.tglgangguan > '2000-01-01 00:00:00'";
$q = mysql_query("
    SELECT g.unit, u.uraian as nama_unit, k.keterangan as nama_keypoint,
           SUM(CASE WHEN g.kat_gangguan = 'PMT' THEN 1 ELSE 0 END) as permanen,
           SUM(CASE WHEN g.kat_gangguan = 'REC' THEN 1 ELSE 0 END) as temporer,
           COUNT(*) as total
    FROM datagangguan g
    JOIN kodeunit u ON g.unit = u.kodeunit
    JOIN kodekeypoint k ON g.keypointid = k.idkeypoint
    $where_sql
    GROUP BY g.unit, k.keterangan
    ORDER BY g.unit ASC, total DESC
");
while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
