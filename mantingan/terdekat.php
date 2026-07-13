<?php
include "koneksi2.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$where = "";

if($mode=="jurusan_dil"){
    $gardu=$_GET['gardu'];
    $tiang=$_GET['tiang'];
    $where="gardudil='$gardu' AND tiangdil='$tiang'";
}

if($mode=="jurusan_dij"){
    $gardu=$_GET['gardu'];
    $tiang=$_GET['tiang'];
    $where="gardudij='$gardu' AND tiangdij='$tiang'";
}

if($mode=="dekat_dil"){
    $lat=$_GET['lat'];
    $lng=$_GET['lng'];

    $where="
    (
    6371000 * acos(
        cos(radians($lat)) *
        cos(radians(latdil)) *
        cos(radians(longdil)-radians($lng)) +
        sin(radians($lat)) *
        sin(radians(latdil))
    )
    ) <= 30";
}

if($mode=="dekat_dij"){
    $lat=$_GET['lat'];
    $lng=$_GET['lng'];

    $where="
    (
    6371000 * acos(
        cos(radians($lat)) *
        cos(radians(latdij)) *
        cos(radians(longdij)-radians($lng)) +
        sin(radians($lat)) *
        sin(radians(latdij))
    )
    ) <= 30";
}

$q=mysql_query("SELECT * FROM datapelanggan WHERE $where");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
body{
    font-family:Arial;
    padding:15px;
    margin:0;
    background:#f4f6f9;
}

table{
    width:100%;
}

.map-btn{
    display:inline-block;
    padding:8px 10px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    margin:2px;
    font-size:12px;
}

.map-dij{background:#0d6efd;}
.map-dil{background:#198754;}
.map-gdij{background:#fd7e14;}
.map-gdil{background:#6f42c1;}
</style>
</head>
<body>

<table id="tabelhasil" class="display">

<thead>
<tr>
<th>IDPEL</th>
<th>Nama</th>
<th>Alamat</th>
<th>Tarif</th>
<th>Daya</th>
<th>No Meter</th>
<th>Prepaid</th>
<th>GarduTiang DIL</th>
<th>GarduTiang DIJ</th>
<th>Maps</th>
</tr>
</thead>

<tbody>

<?php while($d=mysql_fetch_array($q)){ ?>

<tr>

<td><?php echo $d['idpel']; ?></td>
<td><?php echo $d['nama']; ?></td>
<td><?php echo $d['alamat']; ?></td>
<td><?php echo $d['tarif']; ?></td>
<td><?php echo $d['daya']; ?></td>
<td><?php echo $d['nometer']; ?></td>
<td><?php echo $d['prepaid']; ?></td>
<td><?php echo $d['gardudil'].'-'.$d['tiangdil']; ?></td>
<td><?php echo $d['gardudij'].'-'.$d['tiangdij']; ?></td>

<td>

<a class="map-btn map-dij"
target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latdij']; ?>,<?php echo $d['longdij']; ?>"
title="Maps DIJ">
<i class="fa-solid fa-location-dot"></i>
</a>

<a class="map-btn map-dil"
target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latdil']; ?>,<?php echo $d['longdil']; ?>"
title="Maps DIL">
<i class="fa-solid fa-location-dot"></i>
</a>

<a class="map-btn map-gdij"
target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latgardudij']; ?>,<?php echo $d['longgardudij']; ?>"
title="Gardu DIJ">
<i class="fa-solid fa-bolt"></i>
</a>

<a class="map-btn map-gdil"
target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latgardudil']; ?>,<?php echo $d['longgardudil']; ?>"
title="Gardu DIL">
<i class="fa-solid fa-bolt"></i>
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<script>
$(document).ready(function(){
    $('#tabelhasil').DataTable({
        pageLength:10,
        responsive:true
    });
});
</script>

</body>
</html>