<?php
include 'koneksi.php';

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
?>

<!DOCTYPE html>
<html>
<head>
<style>
body { font-family: Arial; font-size:12px; }
table { border-collapse: collapse; width:100%; }
th, td { border:1px solid #000; padding:5px; text-align:center; }
img { width:60px; }

.footer {
position: fixed;
bottom: 0;
width: 100%;
text-align: center;
}

.content { margin-bottom: 120px; }
</style>
</head>

<body>

<div class="content">

<h3>DATA ABSENSI</h3>

<table>
<tr>
<th>TANGGAL</th>
<th>WAKTU</th>
<th>STATUS</th>
<th>KEGIATAN</th>
<th>FOTO</th>
<th>JARAK</th>
</tr>

<?php
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
<td><img src='../foto/".$d['foto']."'></td>
<td>".$jarak." m</td>
</tr>";
}
?>

</table>

</div>


<script>
window.onload = function(){ window.print(); }
</script>

</body>
</html>