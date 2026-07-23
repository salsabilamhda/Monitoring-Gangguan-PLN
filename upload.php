<?php
include 'connect.php';

function compressImage($source, $destination, $quality = 70) {
    $info = getimagesize($source);
    if ($info === false) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    return imagejpeg($image, $destination, $quality);
}

function isImage($tmpName) {
    return getimagesize($tmpName) !== false;
}

$uploadDir = 'uploads/';
$waktu = date('YmdHis');

$tglgangguan = $_POST['tglgangguan'];
$pilihan = $_POST['option'];

if ($pilihan == 'PMT') {
    $data = explode('|', $_POST['pmt']);
    $unit = mysql_real_escape_string(strtoupper(trim($data[0])));
    $penyulang = mysql_real_escape_string(strtoupper(trim($data[1])));
    $keypoint = '';
} else {
    $data = explode('|', $_POST['rec']);
    $unit = mysql_real_escape_string(strtoupper(trim($data[0])));
    $penyulang = mysql_real_escape_string(strtoupper(trim($data[1])));
    $keypoint = mysql_real_escape_string(strtoupper(trim($data[2])));
}


$kategori = $_POST['kategori']; 
$tglmasuk = $_POST['tglmasuk']; 
$relay = $_POST['relay']; 
$fasa = $_POST['fasa']; 
$kv0 = $_POST['kv0']; 
$inol = $_POST['inol'];
$ies = $_POST['ies'];
$ir = $_POST['ir'];
$it = $_POST['it'];
$cuaca = $_POST['cuaca'];
$jenisgangguan = $_POST['jenisgangguan'];
$latlokasi = $_POST['latlokasi'];
$longlokasi = $_POST['longlokasi'];
$kodegangguan = mysql_real_escape_string(strtoupper(trim($_POST['kodegangguan'])));
$temuan = mysql_real_escape_string(strtoupper(trim($_POST['temuan'])));

// Default kosong
$file1NewName = '';
$file2NewName = '';

// === File 1 (opsional tapi tetap diproses kalau ada) ===
if (!empty($_FILES['file1']['tmp_name'])) {
    if (isImage($_FILES['file1']['tmp_name'])) {
        $file1Tmp = $_FILES['file1']['tmp_name'];
        $file1NewName = 'FILE1_' . $waktu . rand(100, 999) . '.jpg';
        $file1Path = $uploadDir . $file1NewName;
        compressImage($file1Tmp, $file1Path);
    }
}

// === File 2 (opsional juga) ===
if (!empty($_FILES['file2']['tmp_name'])) {
    if (isImage($_FILES['file2']['tmp_name'])) {
        $file2Tmp = $_FILES['file2']['tmp_name'];
        $file2NewName = 'FILE2_' . $waktu . rand(100, 999) . '.jpg';
        $file2Path = $uploadDir . $file2NewName;
        compressImage($file2Tmp, $file2Path);
    }
}

// === Simpan ke database ===
$sql = "INSERT INTO datagangguan (
    tglgangguan, kat_gangguan, unit, penyulang, keypointid, kategorigangguan, 
    tglmasuk, relay, fasa, kv0, inetral, ir, ies, it, cuacakode, jeniskode, 
    hasiltemuan, foto1, foto2, latlokasi, longlokasi,kodegangguan
) VALUES (
    '$tglgangguan', '$pilihan', '$unit', '$penyulang', '$keypoint', '$kategori',
    '$tglmasuk', '$relay', '$fasa', '$kv0', '$inol', '$ir', '$ies', '$it', 
    '$cuaca', '$jenisgangguan', '$temuan', '$file1NewName', '$file2NewName',
    '$latlokasi', '$longlokasi','$kodegangguan'
)";

// === Cek Duplikasi Data ===
$dup_where = "";
if (!empty($kodegangguan)) {
    $dup_where = "kodegangguan = '$kodegangguan'";
} else {
    $dup_where = "tglgangguan = '$tglgangguan' AND unit = '$unit' AND penyulang = '$penyulang' AND keypointid = '$keypoint'";
}

$check = mysql_query("SELECT COUNT(*) FROM datagangguan WHERE $dup_where");
$rowCheck = mysql_fetch_array($check);

if ($rowCheck[0] > 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='assets/js/sweetalert2.all.min.js'></script>
        <link href='assets/css/style.css' rel='stylesheet' type='text/css'>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Data Duplikat',
            text: 'Data gangguan " . (!empty($kodegangguan) ? "dengan Kode Gangguan $kodegangguan" : "pada waktu/lokasi tersebut") . " sudah ada di database!',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = 'entridata.php';
        });
    </script>
    </body>
    </html>";
    exit;
}

$result = mysql_query($sql);

if ($result) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='assets/js/sweetalert2.all.min.js'></script>
        <link href='assets/css/style.css' rel='stylesheet' type='text/css'>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: 'Data berhasil disimpan!',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = 'entridata.php';
        });
    </script>
    </body>
    </html>";
} else {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='assets/js/sweetalert2.all.min.js'></script>
        <link href='assets/css/style.css' rel='stylesheet' type='text/css'>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menyimpan data ke database.',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = 'entridata.php';
        });
    </script>
    </body>
    </html>";
}
?>
