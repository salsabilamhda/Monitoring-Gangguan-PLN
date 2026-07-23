<?php
include "connect.php";

function compressImage($source, $destination, $quality = 70) {
    $info = @getimagesize($source);
    if ($info === false) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if ($image === false) return false;
    $res = imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $res;
}

function saveBase64Image($base64Data, $prefix, $uploadDir = 'uploads/') {
    if (empty($base64Data) || !is_array($base64Data) || empty($base64Data['data'])) {
        return '';
    }
    
    $dataString = $base64Data['data'];
    if (preg_match('/^data:image\/(\w+);base64,/', $dataString, $type)) {
        $dataString = substr($dataString, strpos($dataString, ',') + 1);
        $type = strtolower($type[1]);
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            return '';
        }
        $dataString = base64_decode($dataString);
        if ($dataString === false) {
            return '';
        }
    } else {
        return '';
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $prefix . '_' . date('YmdHis') . '_' . rand(100, 999) . '.jpg';
    $filePath = $uploadDir . $fileName;
    
    if (file_put_contents($filePath, $dataString) !== false) {
        compressImage($filePath, $filePath);
        return $fileName;
    }
    return '';
}

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid atau kosong.']);
    exit;
}

// 1. Fetch lookup tables
// Unit lookup (name/code -> code)
$units = [];
$q = mysql_query("SELECT kodeunit, uraian FROM kodeunit");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $units[strtoupper(trim($r['uraian']))] = $r['kodeunit'];
        $units[strtoupper(trim($r['kodeunit']))] = $r['kodeunit'];
    }
}

// Penyulang lookup (name/code -> code)
$penyulangs = [];
$q = mysql_query("SELECT kodepenyul, uraianpenyul FROM v_penyulang");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $penyulangs[strtoupper(trim($r['uraianpenyul']))] = $r['kodepenyul'];
        $penyulangs[strtoupper(trim($r['kodepenyul']))] = $r['kodepenyul'];
    }
}

// Keypoint lookup (description/id -> idkeypoint)
$keypoints = [];
$q = mysql_query("SELECT idkeypoint, keterangan FROM v_keypoint");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $keypoints[strtoupper(trim($r['keterangan']))] = $r['idkeypoint'];
        $keypoints[strtoupper(trim($r['idkeypoint']))] = $r['idkeypoint'];
    }
}

// Cuaca lookup (uraiancuaca/id -> idcuaca)
$cuacas = [];
$q = mysql_query("SELECT idcuaca, uraiancuaca FROM kodecuaca");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $cuacas[strtoupper(trim($r['uraiancuaca']))] = $r['idcuaca'];
        $cuacas[strtoupper(trim($r['idcuaca']))] = $r['idcuaca'];
    }
}

// Jenis Gangguan lookup (uraian/id -> idjenisgangguan)
$jenis_gangguans = [];
$q = mysql_query("SELECT idjenisgangguan, uraianjenisgangguan FROM kodejenisgangguan");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $jenis_gangguans[strtoupper(trim($r['uraianjenisgangguan']))] = $r['idjenisgangguan'];
        $jenis_gangguans[strtoupper(trim($r['idjenisgangguan']))] = $r['idjenisgangguan'];
    }
}

$inserted = 0;
$errors = [];

foreach ($data as $index => $row) {
    $rowNum = $index + 2; // Row number in Excel (header is row 1)
    
    // Extract and map fields
    $kodegangguan = isset($row['Kode Gangguan']) ? mysql_real_escape_string(strtoupper(trim($row['Kode Gangguan']))) : '';
    $tglgangguan = isset($row['Tanggal Gangguan']) ? mysql_real_escape_string(trim($row['Tanggal Gangguan'])) : '';
    $kat_gangguan = isset($row['Kategori Gangguan']) ? mysql_real_escape_string(strtoupper(trim($row['Kategori Gangguan']))) : '';
    
    $raw_unit = isset($row['Unit']) ? strtoupper(trim($row['Unit'])) : '';
    $unit = isset($units[$raw_unit]) ? $units[$raw_unit] : '';
    
    $raw_penyulang = isset($row['Penyulang']) ? strtoupper(trim($row['Penyulang'])) : '';
    $penyulang_code = isset($penyulangs[$raw_penyulang]) ? $penyulangs[$raw_penyulang] : '';
    
    $raw_keypoint = isset($row['Keypoint ID']) ? strtoupper(trim($row['Keypoint ID'])) : '';
    $keypointid = isset($keypoints[$raw_keypoint]) ? $keypoints[$raw_keypoint] : '';
    
    $kategorigangguan = isset($row['Kategori']) ? mysql_real_escape_string(strtoupper(trim($row['Kategori']))) : ''; // TEMPORER / PERMANEN
    $tglmasuk = isset($row['Tanggal Masuk']) ? mysql_real_escape_string(trim($row['Tanggal Masuk'])) : '';
    $relay = isset($row['Relay Kerja']) ? mysql_real_escape_string(strtoupper(trim($row['Relay Kerja']))) : '';
    $fasa = isset($row['Fasa']) ? mysql_real_escape_string(strtoupper(trim($row['Fasa']))) : '';
    
    $kv0 = isset($row['KV 0']) ? floatval($row['KV 0']) : 0;
    $inetral = isset($row['I N']) ? floatval($row['I N']) : 0;
    $ir = isset($row['I R']) ? floatval($row['I R']) : 0;
    $ies = isset($row['I S']) ? floatval($row['I S']) : 0;
    $it = isset($row['I T']) ? floatval($row['I T']) : 0;
    
    $raw_cuaca = isset($row['Cuaca']) ? strtoupper(trim($row['Cuaca'])) : '';
    $cuacakode = isset($cuacas[$raw_cuaca]) ? $cuacas[$raw_cuaca] : '';
    
    $raw_jenis = isset($row['Jenis Gangguan']) ? strtoupper(trim($row['Jenis Gangguan'])) : '';
    $jeniskode = isset($jenis_gangguans[$raw_jenis]) ? $jenis_gangguans[$raw_jenis] : '';
    
    $latlokasi = isset($row['Latitude']) ? mysql_real_escape_string(trim($row['Latitude'])) : '';
    $longlokasi = isset($row['Longitude']) ? mysql_real_escape_string(trim($row['Longitude'])) : '';
    $hasiltemuan = isset($row['Hasil Temuan']) ? mysql_real_escape_string(strtoupper(trim($row['Hasil Temuan']))) : '';
    
    // Validations
    if (empty($tglgangguan)) {
        $errors[] = "Baris $rowNum: Tanggal Gangguan kosong.";
        continue;
    }
    if ($kat_gangguan !== 'PMT' && $kat_gangguan !== 'REC') {
        $errors[] = "Baris $rowNum: Kategori Gangguan harus 'PMT' atau 'REC'.";
        continue;
    }
    if (empty($unit)) {
        $errors[] = "Baris $rowNum: Unit '$raw_unit' tidak ditemukan di database.";
        continue;
    }
    if (empty($penyulang_code)) {
        $errors[] = "Baris $rowNum: Penyulang '$raw_penyulang' tidak ditemukan di database.";
        continue;
    }
    if ($kat_gangguan === 'REC' && empty($keypointid)) {
        $errors[] = "Baris $rowNum: Keypoint '$raw_keypoint' tidak ditemukan di database.";
        continue;
    }
    if (empty($cuacakode)) {
        $errors[] = "Baris $rowNum: Cuaca '$raw_cuaca' tidak ditemukan di database.";
        continue;
    }
    if (empty($jeniskode)) {
        $errors[] = "Baris $rowNum: Jenis Gangguan '$raw_jenis' tidak ditemukan di database.";
        continue;
    }
    
    // Check for duplicate data in database
    $dup_where = "";
    if (!empty($kodegangguan)) {
        $dup_where = "kodegangguan = '$kodegangguan'";
    } else {
        $dup_where = "tglgangguan = '$tglgangguan' AND unit = '$unit' AND penyulang = '$penyulang_code' AND keypointid = '$keypointid'";
    }
    
    $check = mysql_query("SELECT COUNT(*) FROM datagangguan WHERE $dup_where");
    $rowCheck = mysql_fetch_array($check);
    if ($rowCheck[0] > 0) {
        $errors[] = "Baris $rowNum: Data gangguan " . (!empty($kodegangguan) ? "dengan Kode Gangguan '$kodegangguan'" : "pada waktu/lokasi tersebut") . " sudah ada di database (data duplikat).";
        continue;
    }

    // Process base64 photo uploads
    $foto1 = isset($row['foto1']) ? saveBase64Image($row['foto1'], 'FILE1') : '';
    $foto2 = isset($row['foto2']) ? saveBase64Image($row['foto2'], 'FILE2') : '';

    // SQL insert
    $sql = "INSERT INTO datagangguan (
        tglgangguan, kat_gangguan, unit, penyulang, keypointid, kategorigangguan, 
        tglmasuk, relay, fasa, kv0, inetral, ir, ies, it, cuacakode, jeniskode, 
        hasiltemuan, foto1, foto2, latlokasi, longlokasi, kodegangguan
    ) VALUES (
        '$tglgangguan', '$kat_gangguan', '$unit', '$penyulang_code', '$keypointid', '$kategorigangguan',
        '$tglmasuk', '$relay', '$fasa', '$kv0', '$inetral', '$ir', '$ies', '$it', 
        '$cuacakode', '$jeniskode', '$hasiltemuan', '$foto1', '$foto2', '$latlokasi', '$longlokasi', '$kodegangguan'
    )";
    
    $res = mysql_query($sql);
    if ($res) {
        $inserted++;
    } else {
        $errors[] = "Baris $rowNum: Gagal menyimpan data ke database (" . mysql_error() . ")";
    }
}

echo json_encode([
    'success' => count($errors) === 0 || $inserted > 0,
    'inserted' => $inserted,
    'errors' => $errors
]);
