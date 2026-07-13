<?php
include 'koneksi.php';

$status   = strtoupper($_POST['status']);
$kegiatan = strtoupper($_POST['kegiatan']);
$lat      = $_POST['lat'];
$lon      = $_POST['lon'];
$foto     = $_POST['foto'];

// folder
$folder = "foto/";
if(!is_dir($folder)){
    mkdir($folder);
}

// decode base64
$foto = str_replace('data:image/jpeg;base64,', '', $foto);
$foto = str_replace(' ', '+', $foto);

$data = base64_decode($foto);

// nama file
$nama_file = date("YmdHis").".jpg";

// simpan
file_put_contents($folder.$nama_file, $data);

// insert DB
mysqli_query($conn,"INSERT INTO absensi
(status,kegiatan,lat,lon,foto,waktu)
VALUES
('$status','$kegiatan','$lat','$lon','$nama_file',NOW())");

echo "BERHASIL ABSEN";