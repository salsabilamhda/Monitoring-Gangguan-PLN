<?php
include __DIR__ . '/../connect.php';

$drop = mysql_query("DROP VIEW IF EXISTS v_datagangguan");
$sql = "CREATE VIEW v_datagangguan AS
SELECT 
  a.kodegangguan AS kodegangguan,
  a.idgangguan AS idgangguan,
  a.tglgangguan AS tglgangguan,
  a.kat_gangguan AS kat_gangguan,
  a.unit AS unit,
  a.penyulang AS penyulang,
  a.keypointid AS keypointid,
  a.kategorigangguan AS kategorigangguan,
  a.tglmasuk AS tglmasuk,
  a.relay AS relay,
  a.fasa AS fasa,
  a.kv0 AS kv0,
  a.inetral AS inetral,
  a.ir AS ir,
  a.ies AS ies,
  a.it AS it,
  a.cuacakode AS cuacakode,
  a.jeniskode AS jeniskode,
  a.hasiltemuan AS hasiltemuan,
  a.foto1 AS foto1,
  a.foto2 AS foto2,
  a.latlokasi AS latlokasi,
  a.longlokasi AS longlokasi,
  a.hitung AS hitung,
  COALESCE(b.uraian, '') AS uraian,
  COALESCE(c.uraianpenyul, '') AS uraianpenyul,
  TIMESTAMPDIFF(MINUTE, a.tglgangguan, a.tglmasuk) AS selisih_menit,
  COALESCE(d.keterangan, '') AS keterangan,
  COALESCE(e.uraiancuaca, '') AS uraiancuaca,
  COALESCE(f.uraianjenisgangguan, '') AS uraianjenisgangguan
FROM datagangguan a
LEFT JOIN kodeunit b ON a.unit = b.kodeunit
LEFT JOIN kodepenyulang c ON a.penyulang = c.kodepenyul
LEFT JOIN kodekeypoint d ON a.keypointid = d.idkeypoint
LEFT JOIN kodecuaca e ON a.cuacakode = e.idcuaca
LEFT JOIN kodejenisgangguan f ON a.jeniskode = f.idjenisgangguan";

if (mysql_query($sql)) {
    echo "SUCCESS: View v_datagangguan redefined with LEFT JOINs!\n";
    $q = mysql_query("SELECT COUNT(*) FROM v_datagangguan");
    $r = mysql_fetch_array($q);
    echo "Total rows in v_datagangguan now: " . $r[0] . "\n";
} else {
    echo "ERROR: " . mysql_error() . "\n";
}
?>
