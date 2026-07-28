<?php
include __DIR__ . '/../connect.php';

$ulp_id = '51541'; // BALONG
$q = mysql_query("
    SELECT k.keterangan as nama_keypoint, k.kodepenyul,
           (
               SELECT COUNT(*) 
               FROM datagangguan g 
               WHERE g.tglgangguan > '2000-01-01 00:00:00' AND g.keypointid = k.idkeypoint AND g.kat_gangguan = 'REC'
           ) as temporer,
           (
               SELECT COUNT(*) 
               FROM datagangguan g 
               WHERE g.tglgangguan > '2000-01-01 00:00:00' AND g.penyulang = k.kodepenyul AND g.kat_gangguan = 'PMT'
           ) as permanen
    FROM kodekeypoint k
    WHERE k.unit = '$ulp_id' 
      AND k.idkeypoint IN (
          SELECT DISTINCT keypointid 
          FROM datagangguan 
          WHERE keypointid != '' AND keypointid != '0' AND tglgangguan > '2000-01-01 00:00:00'
      )
    ORDER BY (temporer + permanen) DESC
    LIMIT 7
");

while ($r = mysql_fetch_assoc($q)) {
    print_r($r);
}
?>
