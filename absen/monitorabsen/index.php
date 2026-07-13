<?php
include 'koneksi.php';

// PHP 5.6 aman
$tgl1 = isset($_GET['tgl1']) ? $_GET['tgl1'] : '';
$tgl2 = isset($_GET['tgl2']) ? $_GET['tgl2'] : '';

// koordinat kantor
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MONITOR ABSENSI</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-3">

<!-- FILTER -->
<div class="card p-3 mb-3">
<form method="get" class="row g-2">

<div class="col-5">
<input type="date" name="tgl1" value="<?php echo $tgl1; ?>" class="form-control">
</div>

<div class="col-5">
<input type="date" name="tgl2" value="<?php echo $tgl2; ?>" class="form-control">
</div>

<div class="col-2">
<button class="btn btn-primary w-100">FILTER</button>
</div>

</form>

<div class="row mt-2 g-2">
<div class="col-6">
<a href="export_excel.php?tgl1=<?php echo $tgl1; ?>&tgl2=<?php echo $tgl2; ?>" class="btn btn-success w-100">EXCEL</a>
</div>
<div class="col-6">
<a href="export_pdf.php?tgl1=<?php echo $tgl1; ?>&tgl2=<?php echo $tgl2; ?>" class="btn btn-danger w-100">PDF</a>
</div>
</div>

</div>

<!-- DATA -->
<div class="card p-3">

<?php if($tgl1 != '' && $tgl2 != ''){ ?>

<?php
// TOTAL DATA
$total_q = mysqli_query($conn,"SELECT COUNT(*) as total FROM absensi 
WHERE DATE(waktu) BETWEEN '$tgl1' AND '$tgl2'");
$total_d = mysqli_fetch_assoc($total_q);
$total = $total_d['total'];
?>

<div class="alert alert-success text-center">
TOTAL DATA: <b><?php echo $total; ?></b>
</div>

<table class="table table-bordered text-center">

<tr>
<th>TANGGAL</th>
<th>WAKTU</th>
<th>STATUS</th>
<th>KEGIATAN</th>
<th>FOTO</th>
<th>JARAK</th>
<th>AKSI</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM absensi 
WHERE DATE(waktu) BETWEEN '$tgl1' AND '$tgl2'
ORDER BY waktu DESC") or die(mysqli_error($conn));

while($d = mysqli_fetch_assoc($q)){

$jarak = round(hitungJarak(
$d['lat'],$d['lon'],$kantor_lat,$kantor_lon
));

echo "<tr>
<td>".date('Y-m-d',strtotime($d['waktu']))."</td>
<td>".date('H:i:s',strtotime($d['waktu']))."</td>
<td>".$d['status']."</td>
<td>".$d['kegiatan']."</td>

<td>
<img src='../foto/".$d['foto']."' width='60'
onclick=\"lihatFoto('../foto/".$d['foto']."')\">
</td>

<td>".$jarak." m</td>

<td>
<a href='hapus.php?id=".$d['id']."&foto=".$d['foto']."' 
class='btn btn-sm btn-danger'
onclick=\"return confirm('Yakin hapus?')\">
Hapus
</a>
</td>

</tr>";
}
?>

</table>

<?php } else { ?>

<div class="alert alert-info text-center">
Silakan pilih tanggal lalu klik FILTER
</div>

<?php } ?>

</div>

</div>

<!-- MODAL FOTO -->
<div class="modal fade" id="modalFoto">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content bg-dark">
<div class="modal-body text-center">
<img id="imgModal" style="width:100%;">
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function lihatFoto(src){
  document.getElementById("imgModal").src=src;
  new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
</script>

</body>
</html>