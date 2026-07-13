<?php
include 'koneksi.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=absensi.xls");

$tgl1 = isset($_GET['tgl1']) ? $_GET['tgl1'] : date("Y-m-01");
$tgl2 = isset($_GET['tgl2']) ? $_GET['tgl2'] : date("Y-m-d");

$kantor_lat = -7.845002622674241;
$kantor_lon = 111.47715155148373;

function hitungJarak($lat1, $lon1, $lat2, $lon2) {
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
          cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
          cos(deg2rad($theta));
  $dist = acos($dist);
  return $dist * 6371000;
}

echo "<table border='1'>
<tr>
<th>TANGGAL</th>
<th>WAKTU</th>
<th>STATUS</th>
<th>KEGIATAN</th>
<th>KOORDINAT</th>
<th>JARAK</th>
</tr>";

$q = mysqli_query($conn,"SELECT * FROM absensi 
WHERE DATE(waktu) BETWEEN '$tgl1' AND '$tgl2'");

while($d = mysqli_fetch_assoc($q)){

$jarak = round(hitungJarak(
$d['lat'],$d['lon'],$kantor_lat,$kantor_lon
));

echo "<tr>
<td>".date('Y-m-d',strtotime($d['waktu']))."</td>
<td>".date('H:i:s',strtotime($d['waktu']))."</td>
<td>".$d['status']."</td>
<td>".$d['kegiatan']."</td>
<td>".$d['lat'].",".$d['lon']."</td>
<td>".$jarak."</td>
</tr>";
}

echo "</table>";