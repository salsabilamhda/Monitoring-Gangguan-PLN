<?php
include 'koneksi.php';

$id   = isset($_GET['id']) ? $_GET['id'] : '';
$foto = isset($_GET['foto']) ? $_GET['foto'] : '';

if($id == '') die('ID kosong');

// hapus foto
$path = "../foto/" . $foto;
if(file_exists($path)){
    unlink($path);
}

// hapus data
mysqli_query($conn,"DELETE FROM absensi WHERE id='$id'");

header("Location: index.php");
exit;