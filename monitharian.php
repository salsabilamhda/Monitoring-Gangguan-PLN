<?php
include "connect.php";

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

    $res = imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $res;
}

function isImage($tmpName) {
    return getimagesize($tmpName) !== false;
}

// Hapus data
if(isset($_GET['hapus'])){
    $id_hapus = intval($_GET['hapus']);
    $gabungawal  = isset($_GET['awal']) ? $_GET['awal'] : '';
    $gabungakhir = isset($_GET['akhir']) ? $_GET['akhir'] : '';
    $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
    $qf = mysql_query("SELECT foto1,foto2 FROM datagangguan WHERE idgangguan=$id_hapus");
    if($rf = mysql_fetch_assoc($qf)){
        foreach($rf as $f){
            if(!empty($f) && file_exists("uploads/".$f)) unlink("uploads/".$f);
        }
    }
    mysql_query("DELETE FROM datagangguan WHERE idgangguan=$id_hapus") or die(mysql_error());
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <script src="assets/js/sweetalert2.all.min.js"></script>
        <link href="assets/css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: 'Data berhasil dihapus',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = '<?php echo $_SERVER['PHP_SELF']."?awal={$gabungawal}&akhir={$gabungakhir}&unit={$unit}"; ?>';
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

// Edit data gangguan
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_gangguan'){
    $id_edit = intval($_POST['idgangguan']);
    $tglgangguan = mysql_real_escape_string(str_replace('T', ' ', $_POST['tglgangguan']));
    if(strlen($tglgangguan) == 16) $tglgangguan .= ':00';
    
    $pilihan = mysql_real_escape_string($_POST['kat_gangguan']);
    if ($pilihan == 'PMT') {
        $data_split = explode('|', $_POST['pmt']);
        $unit_val = isset($data_split[0]) ? mysql_real_escape_string(strtoupper(trim($data_split[0]))) : '';
        $penyulang = isset($data_split[1]) ? mysql_real_escape_string(strtoupper(trim($data_split[1]))) : '';
        $keypoint = '';
    } else {
        $data_split = explode('|', $_POST['rec']);
        $unit_val = isset($data_split[0]) ? mysql_real_escape_string(strtoupper(trim($data_split[0]))) : '';
        $penyulang = isset($data_split[1]) ? mysql_real_escape_string(strtoupper(trim($data_split[1]))) : '';
        $keypoint = isset($data_split[2]) ? mysql_real_escape_string(strtoupper(trim($data_split[2]))) : '';
    }
    
    $kategori = mysql_real_escape_string($_POST['kategorigangguan']);
    $tglmasuk = mysql_real_escape_string(str_replace('T', ' ', $_POST['tglmasuk']));
    if(strlen($tglmasuk) == 16) $tglmasuk .= ':00';
    
    $relay = mysql_real_escape_string($_POST['relay']);
    $fasa = mysql_real_escape_string($_POST['fasa']);
    $kv0 = mysql_real_escape_string($_POST['kv0']);
    $inol = mysql_real_escape_string($_POST['inol']);
    $ir = mysql_real_escape_string($_POST['ir']);
    $ies = mysql_real_escape_string($_POST['ies']);
    $it = mysql_real_escape_string($_POST['it']);
    $cuaca = mysql_real_escape_string($_POST['cuaca']);
    $jenisgangguan = mysql_real_escape_string($_POST['jenisgangguan']);
    $latlokasi = mysql_real_escape_string($_POST['latlokasi']);
    $longlokasi = mysql_real_escape_string($_POST['longlokasi']);
    $kodegangguan = mysql_real_escape_string(strtoupper(trim($_POST['kodegangguan'])));
    $temuan = mysql_real_escape_string(strtoupper(trim($_POST['temuan'])));
    
    // Ambil foto saat ini
    $curr_q = mysql_query("SELECT foto1, foto2 FROM datagangguan WHERE idgangguan = $id_edit");
    $curr_row = mysql_fetch_assoc($curr_q);
    $file1Name = $curr_row ? $curr_row['foto1'] : '';
    $file2Name = $curr_row ? $curr_row['foto2'] : '';
    
    $uploadDir = 'uploads/';
    $waktu = date('YmdHis');
    
    // Hapus foto 1 jika dicentang
    if (isset($_POST['hapus_foto1']) && $_POST['hapus_foto1'] == '1') {
        if (!empty($file1Name) && file_exists($uploadDir . $file1Name)) {
            unlink($uploadDir . $file1Name);
        }
        $file1Name = '';
    }
    // Upload foto 1 baru
    if (!empty($_FILES['file1']['tmp_name']) && isImage($_FILES['file1']['tmp_name'])) {
        if (!empty($file1Name) && file_exists($uploadDir . $file1Name)) {
            unlink($uploadDir . $file1Name);
        }
        $file1Name = 'FILE1_' . $waktu . rand(100, 999) . '.jpg';
        compressImage($_FILES['file1']['tmp_name'], $uploadDir . $file1Name);
    }
    
    // Hapus foto 2 jika dicentang
    if (isset($_POST['hapus_foto2']) && $_POST['hapus_foto2'] == '1') {
        if (!empty($file2Name) && file_exists($uploadDir . $file2Name)) {
            unlink($uploadDir . $file2Name);
        }
        $file2Name = '';
    }
    // Upload foto 2 baru
    if (!empty($_FILES['file2']['tmp_name']) && isImage($_FILES['file2']['tmp_name'])) {
        if (!empty($file2Name) && file_exists($uploadDir . $file2Name)) {
            unlink($uploadDir . $file2Name);
        }
        $file2Name = 'FILE2_' . $waktu . rand(100, 999) . '.jpg';
        compressImage($_FILES['file2']['tmp_name'], $uploadDir . $file2Name);
    }
    
    $update_sql = "UPDATE datagangguan SET 
        tglgangguan = '$tglgangguan',
        kat_gangguan = '$pilihan',
        unit = '$unit_val',
        penyulang = '$penyulang',
        keypointid = '$keypoint',
        kategorigangguan = '$kategori',
        tglmasuk = '$tglmasuk',
        relay = '$relay',
        fasa = '$fasa',
        kv0 = '$kv0',
        inetral = '$inol',
        ir = '$ir',
        ies = '$ies',
        it = '$it',
        cuacakode = '$cuaca',
        jeniskode = '$jenisgangguan',
        hasiltemuan = '$temuan',
        foto1 = '$file1Name',
        foto2 = '$file2Name',
        latlokasi = '$latlokasi',
        longlokasi = '$longlokasi',
        kodegangguan = '$kodegangguan'
        WHERE idgangguan = $id_edit";
    
    $res = mysql_query($update_sql);
    
    $red_awal = isset($_POST['redirect_awal']) ? $_POST['redirect_awal'] : '';
    $red_akhir = isset($_POST['redirect_akhir']) ? $_POST['redirect_akhir'] : '';
    $red_unit = isset($_POST['redirect_unit']) ? $_POST['redirect_unit'] : '';
    $redirect_url = $_SERVER['PHP_SELF'] . "?awal={$red_awal}&akhir={$red_akhir}&unit={$red_unit}";
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <script src="assets/js/sweetalert2.all.min.js"></script>
        <link href="assets/css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <script>
        <?php if($res): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Data gangguan berhasil diperbarui!',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = '<?php echo $redirect_url; ?>';
        });
        <?php else: ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal memperbarui data: <?php echo addslashes(mysql_error()); ?>',
            confirmButtonColor: '#242c6d'
        }).then(() => {
            window.location.href = '<?php echo $redirect_url; ?>';
        });
        <?php endif; ?>
    </script>
    </body>
    </html>
    <?php
    exit;
}

// Master Dropdown Options untuk Form Edit
$opt_pmt = [];
$q_pmt = mysql_query("SELECT unit, kodepenyul, uraian, uraianpenyul FROM v_penyulang ORDER BY uraian ASC, uraianpenyul ASC");
while($row_p = mysql_fetch_assoc($q_pmt)) {
    $opt_pmt[] = [
        'val' => $row_p['unit'] . '|' . $row_p['kodepenyul'],
        'label' => $row_p['uraian'] . ' | ' . $row_p['uraianpenyul']
    ];
}

$opt_rec = [];
$q_rec = mysql_query("SELECT unit, kodepenyul, idkeypoint, uraian, uraianpenyul, keterangan FROM v_keypoint ORDER BY uraian ASC, uraianpenyul ASC, keterangan ASC");
while($row_r = mysql_fetch_assoc($q_rec)) {
    $opt_rec[] = [
        'val' => $row_r['unit'] . '|' . $row_r['kodepenyul'] . '|' . $row_r['idkeypoint'],
        'label' => $row_r['uraian'] . ' | ' . $row_r['uraianpenyul'] . ' | ' . $row_r['keterangan']
    ];
}

$opt_cuaca = [];
$q_cuaca = mysql_query("SELECT idcuaca, uraiancuaca FROM kodecuaca ORDER BY idcuaca ASC");
while($row_c = mysql_fetch_assoc($q_cuaca)) {
    $opt_cuaca[] = $row_c;
}

$opt_jenis = [];
$q_jenis = mysql_query("SELECT idjenisgangguan, uraianjenisgangguan FROM kodejenisgangguan ORDER BY idjenisgangguan ASC");
while($row_j = mysql_fetch_assoc($q_jenis)) {
    $opt_jenis[] = $row_j;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Monitor Gangguan</title>

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f4f6f9;
      color: #333;
      padding: 0 !important;
      margin: 0 !important;
    }
    #content-wrapper {
      padding-bottom: 80px !important;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      background-color: #ffffff;
      margin: 0 0 10px 0 !important;
    }
    .card-body {
      padding: 12px;
    }
    table.dataTable thead th {
      background-color: #242c6d !important;
      color: #ffffff !important;
      text-align: center;
      vertical-align: middle;
      font-weight: 600;
      border-bottom: 2px solid #dee2e6 !important;
      font-size: 11px;
    }
    table.dataTable tbody td {
      font-size: 11px;
      vertical-align: middle;
      text-align: center;
    }
    /* Align ULP and Penyulang columns to left */
    table.dataTable tbody td:nth-child(2),
    table.dataTable tbody td:nth-child(3) {
      text-align: left !important;
    }
    /* Force DataTables wrappers to full width */
    #tabelPegawai_wrapper,
    .dataTables_scroll,
    .dataTables_scrollHead,
    .dataTables_scrollBody,
    table.dataTable {
      width: 100% !important;
    }
    .table-responsive {
      width: 100% !important;
      overflow-x: auto !important;
      -webkit-overflow-scrolling: touch;
      border: 1px solid #dee2e6;
      border-radius: 8px;
    }
    .img-thumb {
      width: 32px;
      height: 32px;
      cursor: pointer;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid #cbd5e1;
      transition: transform 0.2s ease;
    }
    .img-thumb:hover {
      transform: scale(1.15);
    }
    .modal-img {
      max-width: 100%;
      max-height: 80vh;
    }
    .btn-action-group {
      display: inline-flex;
      gap: 4px;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
    }
    .btn-action-group .btn {
      font-size: 11px;
      padding: 3px 8px;
      line-height: 1.2;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .modal-header-custom {
      background-color: #242c6d !important;
      color: #ffffff !important;
    }
    .modal-header-custom .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
    }
    .view-card-section {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 12px 15px;
      margin-bottom: 12px;
    }
    .view-card-title {
      font-size: 12px;
      font-weight: 700;
      color: #242c6d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
      border-bottom: 2px solid #242c6d;
      padding-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .view-field-label {
      font-size: 11px;
      color: #64748b;
      font-weight: 600;
      margin-bottom: 2px;
      text-transform: uppercase;
    }
    .view-field-value {
      font-size: 13px;
      color: #0f172a;
      font-weight: 600;
    }
    .badge-pmt {
      background-color: #0284c7;
      color: #fff;
    }
    .badge-rec {
      background-color: #0d9488;
      color: #fff;
    }
    .badge-perm {
      background-color: #dc2626;
      color: #fff;
    }
    .badge-temp {
      background-color: #16a34a;
      color: #fff;
    }
    #editModal .modal-dialog,
    #viewModal .modal-dialog {
      margin-top: 1rem !important;
      margin-bottom: 1rem !important;
    }
    #editModal .modal-content,
    #viewModal .modal-content {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-radius: 12px;
    }
    #editModal .modal-body,
    #viewModal .modal-body {
      overflow-y: auto !important;
      -webkit-overflow-scrolling: touch;
      flex: 1 1 auto;
      max-height: 480px !important;
    }
    #editModal .modal-header,
    #viewModal .modal-header {
      flex-shrink: 0;
    }
    #editModal .modal-footer,
    #viewModal .modal-footer {
      flex-shrink: 0;
      background-color: #ffffff !important;
      border-top: 1px solid #e2e8f0;
    }
    #editModal .modal-body::-webkit-scrollbar,
    #viewModal .modal-body::-webkit-scrollbar {
      width: 8px;
    }
    #editModal .modal-body::-webkit-scrollbar-track,
    #viewModal .modal-body::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 4px;
    }
    #editModal .modal-body::-webkit-scrollbar-thumb,
    #viewModal .modal-body::-webkit-scrollbar-thumb {
      background: #94a3b8;
      border-radius: 4px;
    }
    #editModal .modal-body::-webkit-scrollbar-thumb:hover,
    #viewModal .modal-body::-webkit-scrollbar-thumb:hover {
      background: #64748b;
    }
  </style>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

  <!-- Bootstrap CSS & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <!-- jQuery + DataTables + Buttons -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sweetalert2.all.min.js"></script>
</head>
<body>
<div id="content-wrapper">
  
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <?php
      $tglawal = isset($_POST['tglawal']) ? $_POST['tglawal'] : '';
      $tglakhir = isset($_POST['tglakhir']) ? $_POST['tglakhir'] : '';
      
      $gabungawal = '';
      if (!empty($tglawal)) {
          $tglawal1 = explode("/", $tglawal);
          if (count($tglawal1) === 3) {
              $gabungawal = $tglawal1[2].'-'.$tglawal1[0].'-'.$tglawal1[1];
          }
      }
      
      $gabungakhir = '';
      if (!empty($tglakhir)) {
          $tglakhir1 = explode("/", $tglakhir);
          if (count($tglakhir1) === 3) {
              $gabungakhir = $tglakhir1[2].'-'.$tglakhir1[0].'-'.$tglakhir1[1];
          }
      }
      
      $unit = isset($_POST['unit']) ? $_POST['unit'] : (isset($_GET['unit']) ? $_GET['unit'] : '');
      $gabungawal1  = isset($_GET['awal']) ? $_GET['awal'] : '';
      $gabungakhir2 = isset($_GET['akhir']) ? $_GET['akhir'] : '';
      $map = '<img src ="map.png" alt="Map" />';
      $lokasi='https://www.google.com/maps/place/';
      ?>
    <div class="table-responsive">
    <table id="tabelPegawai" class="display nowrap" style="width:100%">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Gangguan</th>
          <th>Unit</th>
          <th>Penyulang</th>
          <th>Keypoint</th>
          <th>Kat. Gangguan</th>
          <th>Kategori</th>
          <th>Tanggal Gangguan</th>
          <th>Tanggal Masuk</th>
          <th>Lama Padam (Menit)</th>
          <th>Relay</th>
          <th>Fasa</th>
          <th>KV 0</th>
          <th>IR</th>
          <th>IS</th>
          <th>IT</th>
          <th>IN</th>
          <th>Cuaca</th>
          <th>Jenis Gangguan</th>
          <th>Hasil Temuan</th>
          <th>Foto 1</th>
          <th>Foto 2</th>
          <th>Map Lokasi</th>
          <th>Act</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        $hasil = false;
        $query = "";
        
        $current_awal = !empty($gabungawal) ? $gabungawal : $gabungawal1;
        $current_akhir = !empty($gabungakhir) ? $gabungakhir : $gabungakhir2;
        
        if (empty($tglawal) && empty($gabungawal1)) {
            // Jika belum di-filter, tampilkan data gangguan hari ini (CURDATE)
            $query = "SELECT * FROM v_datagangguan WHERE DATE(tglgangguan) = CURDATE() ORDER BY tglgangguan DESC";
        } elseif ($gabungawal1 != '' && $gabungakhir2 != '') {
            if ($unit == '5125') {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE DATE(tglgangguan) BETWEEN '$gabungawal1' AND '$gabungakhir2' ORDER BY tglgangguan DESC";
            } else {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE DATE(tglgangguan) BETWEEN '$gabungawal1' AND '$gabungakhir2' AND unit = '$unit' ORDER BY tglgangguan DESC";
            }
        } elseif ($gabungawal != '' && $gabungakhir != '') {
            if ($unit == '5125') {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE DATE(tglgangguan) BETWEEN '$gabungawal' AND '$gabungakhir' ORDER BY tglgangguan DESC";
            } else {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE DATE(tglgangguan) BETWEEN '$gabungawal' AND '$gabungakhir' 
                          AND unit = '$unit' ORDER BY tglgangguan DESC";
            }
        }
        
        if ($query != "") {
            $hasil = mysql_query($query);
        }
        
        $all_rows_data = [];

        if ($hasil) {
            while ($data = mysql_fetch_array($hasil)) {
                $idgangguan = $data['idgangguan'];
                $all_rows_data[$idgangguan] = $data;

                $foto1_cell = !empty($data['foto1']) 
                    ? "<img src='uploads/{$data['foto1']}' class='img-thumb' data-img='uploads/{$data['foto1']}' alt='Foto 1' />" 
                    : "";
                
                $foto2_cell = !empty($data['foto2']) 
                    ? "<img src='uploads/{$data['foto2']}' class='img-thumb' data-img='uploads/{$data['foto2']}' alt='Foto 2' />" 
                    : "";
                
                $map_cell = (!empty($data['latlokasi']) && !empty($data['longlokasi']))
                    ? "<a href='https://www.google.com/maps/place/{$data['latlokasi']},{$data['longlokasi']}' target='_blank' style='cursor:pointer' title='Cek Koordinat'>$map</a>"
                    : "";

                echo "<tr>
                    <td align='center'>$no</td>
                    <td>" . htmlspecialchars($data['kodegangguan']) . "</td>
                    <td>" . htmlspecialchars($data['uraian']) . "</td>
                    <td>" . htmlspecialchars($data['uraianpenyul']) . "</td>
                    <td>" . htmlspecialchars($data['keterangan']) . "</td>
                    <td align='center'><span class='badge " . ($data['kat_gangguan'] == 'PMT' ? 'badge-pmt' : 'badge-rec') . "'>" . htmlspecialchars($data['kat_gangguan']) . "</span></td>
                    <td align='center'><span class='badge " . ($data['kategorigangguan'] == 'PERMANEN' ? 'badge-perm' : 'badge-temp') . "'>" . htmlspecialchars($data['kategorigangguan']) . "</span></td>
                    <td align='center'>" . htmlspecialchars($data['tglgangguan']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['tglmasuk']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['selisih_menit']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['relay']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['fasa']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['kv0']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['ir']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['ies']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['it']) . "</td>
                    <td align='center'>" . htmlspecialchars($data['inetral']) . "</td>
                    <td>" . htmlspecialchars($data['uraiancuaca']) . "</td>
                    <td>" . htmlspecialchars($data['uraianjenisgangguan']) . "</td>
                    <td title='" . htmlspecialchars($data['hasiltemuan']) . "'>" . htmlspecialchars(strlen($data['hasiltemuan']) > 35 ? substr($data['hasiltemuan'], 0, 35) . '...' : $data['hasiltemuan']) . "</td>
                    <td align='center'>$foto1_cell</td>
                    <td align='center'>$foto2_cell</td>
                    <td align='center'>$map_cell</td>
                    <td align='center'>
                      <div class='btn-action-group'>
                        <button type='button' class='btn btn-sm btn-info text-white btn-view' data-id='$idgangguan' title='Lihat Detail Data'>
                          <i class='fa fa-eye'></i> View
                        </button>
                        <button type='button' class='btn btn-sm btn-warning text-dark btn-edit' data-id='$idgangguan' title='Edit / Koreksi Data'>
                          <i class='fa fa-edit'></i> Edit
                        </button>
                        <a href='?hapus=$idgangguan&awal={$current_awal}&akhir={$current_akhir}&unit={$unit}' class='btn btn-sm btn-danger btn-delete' title='Hapus Data'>
                          <i class='fa fa-trash'></i> Hapus
                        </a>
                      </div>
                    </td>
                </tr>";
                $no++;
            }
        }
        ?>
      </tbody>
    </table>
    </div>
    </div>
  </div>

  <!-- Modal View Detail Gangguan -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content border-0 shadow">
        <div class="modal-header modal-header-custom py-2">
          <div class="d-flex align-items-center gap-2">
            <i class="fa fa-info-circle fa-lg"></i>
            <h5 class="modal-title mb-0" id="viewModalLabel">Detail Data Gangguan</h5>
            <span id="view_badge_kode" class="badge bg-light text-dark fw-bold ms-2"></span>
            <span id="view_badge_kat" class="badge badge-pmt"></span>
            <span id="view_badge_keandalan" class="badge badge-temp"></span>
            <span id="view_badge_durasi" class="badge bg-warning text-dark fw-bold"></span>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            
            <!-- Section 1: Lokasi & Jaringan -->
            <div class="col-md-6">
              <div class="view-card-section h-100">
                <div class="view-card-title">
                  <i class="fa fa-network-wired text-primary"></i> 1. Informasi Lokasi & Jaringan
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="view-field-label">Unit / ULP</div>
                    <div class="view-field-value" id="view_unit">-</div>
                  </div>
                  <div class="col-6">
                    <div class="view-field-label">Penyulang</div>
                    <div class="view-field-value" id="view_penyulang">-</div>
                  </div>
                  <div class="col-12">
                    <div class="view-field-label">Recloser / Keypoint</div>
                    <div class="view-field-value text-primary" id="view_keypoint">-</div>
                  </div>
                  <div class="col-12 mt-2">
                    <div class="view-field-label">Koordinat Lokasi</div>
                    <div class="d-flex align-items-center justify-content-between">
                      <span class="view-field-value font-monospace" id="view_koordinat">-</span>
                      <a href="#" id="view_maps_btn" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">
                        <i class="fa fa-map-marker-alt me-1"></i> Buka Google Maps
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 2: Waktu & Pemadaman -->
            <div class="col-md-6">
              <div class="view-card-section h-100">
                <div class="view-card-title">
                  <i class="fa fa-clock text-warning"></i> 2. Waktu & Durasi Pemadaman
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="view-field-label">Tanggal Gangguan (Padam)</div>
                    <div class="view-field-value text-danger" id="view_tglgangguan">-</div>
                  </div>
                  <div class="col-6">
                    <div class="view-field-label">Tanggal Masuk (Normal)</div>
                    <div class="view-field-value text-success" id="view_tglmasuk">-</div>
                  </div>
                  <div class="col-12 mt-2">
                    <div class="view-field-label">Lama Padam Total</div>
                    <div class="view-field-value fs-6 text-dark" id="view_durasi_text">-</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 3: Indikasi Proteksi & Pengukuran Arus -->
            <div class="col-md-6">
              <div class="view-card-section h-100">
                <div class="view-card-title">
                  <i class="fa fa-bolt text-danger"></i> 3. Indikasi Proteksi & Pengukuran Arus
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="view-field-label">Relay Kerja</div>
                    <div class="view-field-value" id="view_relay">-</div>
                  </div>
                  <div class="col-6">
                    <div class="view-field-label">Fasa Kerja</div>
                    <div class="view-field-value" id="view_fasa">-</div>
                  </div>
                  <div class="col-4 mt-2">
                    <div class="view-field-label">KV 0</div>
                    <div class="view-field-value" id="view_kv0">-</div>
                  </div>
                  <div class="col-4 mt-2">
                    <div class="view-field-label">I Netral (IN)</div>
                    <div class="view-field-value" id="view_inetral">-</div>
                  </div>
                  <div class="col-4 mt-2">
                    <div class="view-field-label">I R</div>
                    <div class="view-field-value" id="view_ir">-</div>
                  </div>
                  <div class="col-6 mt-1">
                    <div class="view-field-label">I S</div>
                    <div class="view-field-value" id="view_ies">-</div>
                  </div>
                  <div class="col-6 mt-1">
                    <div class="view-field-label">I T</div>
                    <div class="view-field-value" id="view_it">-</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 4: Penyebab & Hasil Temuan -->
            <div class="col-md-6">
              <div class="view-card-section h-100">
                <div class="view-card-title">
                  <i class="fa fa-search text-info"></i> 4. Penyebab & Hasil Temuan
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="view-field-label">Kondisi Cuaca</div>
                    <div class="view-field-value" id="view_cuaca">-</div>
                  </div>
                  <div class="col-6">
                    <div class="view-field-label">Jenis Gangguan</div>
                    <div class="view-field-value" id="view_jenisgangguan">-</div>
                  </div>
                  <div class="col-12 mt-2">
                    <div class="view-field-label">Deskripsi Hasil Temuan</div>
                    <div class="alert alert-secondary py-2 px-3 mb-0 fw-bold text-dark font-monospace" id="view_hasiltemuan" style="min-height: 50px; font-size: 12px; white-space: pre-wrap;">-</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 5: Foto Dokumentasi -->
            <div class="col-12">
              <div class="view-card-section mb-0">
                <div class="view-card-title">
                  <i class="fa fa-camera text-success"></i> 5. Dokumentasi Foto Lapangan
                </div>
                <div class="row g-3">
                  <div class="col-md-6 text-center">
                    <div class="fw-bold mb-2 text-secondary" style="font-size: 12px;">Foto Dokumentasi 1</div>
                    <div id="view_foto1_wrapper" class="p-2 border rounded bg-white" style="min-height: 140px; display:flex; align-items:center; justify-content:center;">
                      <span class="text-muted small">Tidak ada foto</span>
                    </div>
                  </div>
                  <div class="col-md-6 text-center">
                    <div class="fw-bold mb-2 text-secondary" style="font-size: 12px;">Foto Dokumentasi 2</div>
                    <div id="view_foto2_wrapper" class="p-2 border rounded bg-white" style="min-height: 140px; display:flex; align-items:center; justify-content:center;">
                      <span class="text-muted small">Tidak ada foto</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i> Tutup
          </button>
          <button type="button" class="btn btn-warning btn-sm text-dark fw-bold" id="btnSwitchToEdit">
            <i class="fa fa-edit me-1"></i> Edit Data Ini
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit Data Gangguan -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <form class="modal-content border-0 shadow" id="editForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
          <input type="hidden" name="action" value="edit_gangguan">
          <input type="hidden" name="idgangguan" id="edit_idgangguan">
          <input type="hidden" name="redirect_awal" value="<?php echo htmlspecialchars($current_awal); ?>">
          <input type="hidden" name="redirect_akhir" value="<?php echo htmlspecialchars($current_akhir); ?>">
          <input type="hidden" name="redirect_unit" value="<?php echo htmlspecialchars($unit); ?>">

          <div class="modal-header modal-header-custom py-2">
            <div class="d-flex align-items-center gap-2">
              <i class="fa fa-edit fa-lg"></i>
              <h5 class="modal-title mb-0" id="editModalLabel">Edit Data Gangguan</h5>
              <span id="edit_modal_id_badge" class="badge bg-light text-dark ms-2"></span>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-4">
            <!-- Baris 1: Kode Gangguan & Kategori Gangguan -->
            <div class="row g-3 mb-3">
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_kodegangguan">Kode Gangguan</label>
                <input type="text" class="form-control text-uppercase" name="kodegangguan" id="edit_kodegangguan" placeholder="Contoh: GG-001">
              </div>
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold">Pilih Kategori Gangguan <span class="text-danger">*</span></label>
                <div class="d-flex gap-4 pt-1">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="kat_gangguan" id="edit_radio_pmt" value="PMT" required>
                    <label class="form-check-label fw-bold" for="edit_radio_pmt">PMT</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="kat_gangguan" id="edit_radio_rec" value="REC" required>
                    <label class="form-check-label fw-bold" for="edit_radio_rec">REC / PMCB</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Baris 2: Pemilihan Penyulang PMT / Keypoint REC -->
            <div class="row g-3 mb-3">
              <div class="col-12" id="edit_box_pmt">
                <label class="form-label fw-bold" for="edit_select_pmt">Pilih Penyulang PMT <span class="text-danger">*</span></label>
                <select class="form-select" name="pmt" id="edit_select_pmt">
                  <option value="">-- Pilih Penyulang PMT --</option>
                  <?php foreach ($opt_pmt as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['val']); ?>"><?php echo htmlspecialchars($p['label']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12" id="edit_box_rec" style="display: none;">
                <label class="form-label fw-bold" for="edit_select_rec">Pilih Recloser / Keypoint REC <span class="text-danger">*</span></label>
                <select class="form-select" name="rec" id="edit_select_rec">
                  <option value="">-- Pilih Recloser / Keypoint REC --</option>
                  <?php foreach ($opt_rec as $r): ?>
                    <option value="<?php echo htmlspecialchars($r['val']); ?>"><?php echo htmlspecialchars($r['label']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Baris 3: Tanggal Gangguan, Tanggal Masuk, Kategori Keandalan -->
            <div class="row g-3 mb-3">
              <div class="col-md-4 col-12">
                <label class="form-label fw-bold" for="edit_tglgangguan">Tanggal Gangguan <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" name="tglgangguan" id="edit_tglgangguan" required>
              </div>
              <div class="col-md-4 col-12">
                <label class="form-label fw-bold" for="edit_tglmasuk">Tanggal Masuk (Normal) <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" name="tglmasuk" id="edit_tglmasuk" required>
              </div>
              <div class="col-md-4 col-12">
                <label class="form-label fw-bold">Kategori Keandalan <span class="text-danger">*</span></label>
                <div class="d-flex gap-4 pt-1">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="kategorigangguan" id="edit_radio_temp" value="TEMPORER" required>
                    <label class="form-check-label fw-bold text-success" for="edit_radio_temp">TEMPORER</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="kategorigangguan" id="edit_radio_perm" value="PERMANEN" required>
                    <label class="form-check-label fw-bold text-danger" for="edit_radio_perm">PERMANEN</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Baris 4: Relay Kerja & Fasa -->
            <div class="row g-3 mb-3">
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_relay">Relay Kerja</label>
                <select class="form-select" name="relay" id="edit_relay">
                  <option value=""></option>
                  <option value="DGR">DGR</option>
                  <option value="EF">EF</option>
                  <option value="OCR">OCR</option>
                  <option value="OCR-INSTANT">OCR-INSTANT</option>
                </select>
              </div>
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_fasa">Fasa</label>
                <select class="form-select" name="fasa" id="edit_fasa">
                  <option value=""></option>
                  <option value="R">R</option>
                  <option value="RS">RS</option>
                  <option value="RST">RST</option>
                  <option value="RT">RT</option>
                  <option value="S">S</option>
                  <option value="ST">ST</option>
                  <option value="T">T</option>
                </select>
              </div>
            </div>

            <!-- Baris 5: Tegangan & Arus -->
            <div class="row g-3 mb-3">
              <div class="col-md-2 col-6">
                <label class="form-label fw-bold" for="edit_kv0">KV 0</label>
                <input type="text" class="form-control" name="kv0" id="edit_kv0">
              </div>
              <div class="col-md-2 col-6">
                <label class="form-label fw-bold" for="edit_inol">I Netral (IN)</label>
                <input type="text" class="form-control" name="inol" id="edit_inol">
              </div>
              <div class="col-md-2 col-4">
                <label class="form-label fw-bold" for="edit_ir">I R</label>
                <input type="text" class="form-control" name="ir" id="edit_ir">
              </div>
              <div class="col-md-3 col-4">
                <label class="form-label fw-bold" for="edit_ies">I S</label>
                <input type="text" class="form-control" name="ies" id="edit_ies">
              </div>
              <div class="col-md-3 col-4">
                <label class="form-label fw-bold" for="edit_it">I T</label>
                <input type="text" class="form-control" name="it" id="edit_it">
              </div>
            </div>

            <!-- Baris 6: Cuaca & Jenis Gangguan -->
            <div class="row g-3 mb-3">
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_cuaca">Cuaca <span class="text-danger">*</span></label>
                <select class="form-select" name="cuaca" id="edit_cuaca" required>
                  <option value="">-- Pilih Cuaca --</option>
                  <?php foreach ($opt_cuaca as $c): ?>
                    <option value="<?php echo htmlspecialchars($c['idcuaca']); ?>"><?php echo htmlspecialchars($c['uraiancuaca']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_jenisgangguan">Jenis Gangguan <span class="text-danger">*</span></label>
                <select class="form-select" name="jenisgangguan" id="edit_jenisgangguan" required>
                  <option value="">-- Pilih Jenis Gangguan --</option>
                  <?php foreach ($opt_jenis as $j): ?>
                    <option value="<?php echo htmlspecialchars($j['idjenisgangguan']); ?>"><?php echo htmlspecialchars($j['uraianjenisgangguan']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Baris 7: Latitude & Longitude -->
            <div class="row g-3 mb-3">
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_latlokasi">Latitude Lokasi</label>
                <input type="text" class="form-control" name="latlokasi" id="edit_latlokasi" placeholder="Contoh: -7.865432">
              </div>
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="edit_longlokasi">Longitude Lokasi</label>
                <input type="text" class="form-control" name="longlokasi" id="edit_longlokasi" placeholder="Contoh: 111.456789">
              </div>
            </div>

            <!-- Baris 8: Hasil Temuan -->
            <div class="mb-3">
              <label class="form-label fw-bold" for="edit_temuan">Hasil Temuan</label>
              <textarea class="form-control text-uppercase" name="temuan" id="edit_temuan" rows="3" placeholder="Deskripsikan temuan penyebab gangguan di lapangan..."></textarea>
            </div>

            <!-- Baris 9: Foto Dokumentasi -->
            <div class="row g-3">
              <div class="col-md-6 col-12">
                <div class="p-3 border rounded bg-light">
                  <label class="form-label fw-bold">Foto Dokumentasi 1</label>
                  <div id="edit_preview_foto1_box" class="mb-2 d-none">
                    <div class="d-flex align-items-center gap-3">
                      <img id="edit_preview_foto1_img" src="" class="img-thumbnail" style="max-height: 80px; object-fit: cover;" alt="Foto 1">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="hapus_foto1" id="edit_hapus_foto1" value="1">
                        <label class="form-check-label text-danger fw-bold small" for="edit_hapus_foto1">
                          <i class="fa fa-trash me-1"></i> Hapus Foto 1 Saat Ini
                        </label>
                      </div>
                    </div>
                  </div>
                  <input type="file" accept="image/*" class="form-control" name="file1">
                  <small class="text-muted d-block mt-1">Pilih file baru jika ingin mengganti Foto 1 (opsional).</small>
                </div>
              </div>

              <div class="col-md-6 col-12">
                <div class="p-3 border rounded bg-light">
                  <label class="form-label fw-bold">Foto Dokumentasi 2</label>
                  <div id="edit_preview_foto2_box" class="mb-2 d-none">
                    <div class="d-flex align-items-center gap-3">
                      <img id="edit_preview_foto2_img" src="" class="img-thumbnail" style="max-height: 80px; object-fit: cover;" alt="Foto 2">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="hapus_foto2" id="edit_hapus_foto2" value="1">
                        <label class="form-check-label text-danger fw-bold small" for="edit_hapus_foto2">
                          <i class="fa fa-trash me-1"></i> Hapus Foto 2 Saat Ini
                        </label>
                      </div>
                    </div>
                  </div>
                  <input type="file" accept="image/*" class="form-control" name="file2">
                  <small class="text-muted d-block mt-1">Pilih file baru jika ingin mengganti Foto 2 (opsional).</small>
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer bg-white border-top shadow-sm py-2 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
              <i class="fa fa-times me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm" style="font-size: 14px;">
              <i class="fa fa-save me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
    </div>
  </div>

  <!-- Modal Galeri / Preview Zoom Foto -->
  <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-transparent border-0 shadow-none">
        <div class="modal-body text-center p-0 position-relative">
          <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                  data-bs-dismiss="modal" aria-label="Close"></button>
          <img id="modalImage" class="modal-img rounded" src="" alt="Preview" />
          <button class="btn btn-dark position-absolute top-50 start-0 translate-middle-y px-3" id="prevImg">&lt;</button>
          <button class="btn btn-dark position-absolute top-50 end-0 translate-middle-y px-3" id="nextImg">&gt;</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Data Gangguan Map untuk JS
    const gangguanData = <?php echo json_encode($all_rows_data); ?>;

    // Helper format datetime string to input datetime-local value
    function formatForDateTimeLocal(dtStr) {
      if (!dtStr) return '';
      // Jika format YYYY-MM-DD HH:mm:ss atau YYYY-MM-DD HH:mm
      return dtStr.replace(' ', 'T').substring(0, 16);
    }

    // DataTables Initialization
    $(document).ready(function() {
      var table = $('#tabelPegawai').DataTable({
        scrollX: false,
        paging: true,
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            exportOptions: {
              columns: [0, 1, 2, 3, 5, 7, 9, 10, 11, 17, 18, 19]
            }
          },
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
              columns: [0, 1, 2, 3, 5, 7, 9, 10, 11, 17, 18, 19]
            },
            customize: function(doc) {
              doc.defaultStyle.fontSize = 8;
              doc.styles.tableHeader.fontSize = 9;
              doc.content[1].table.widths = ['3%', '10%', '8%', '10%', '6%', '12%', '8%', '6%', '5%', '8%', '10%', '14%'];
            }
          },
          {
            extend: 'print',
            exportOptions: {
              columns: [0, 1, 2, 3, 5, 7, 9, 10, 11, 17, 18, 19]
            }
          }
        ],
        orderCellsTop: true
      });

      table.on('draw', function() {
        if (window.parent && typeof window.parent.resizeIframe === 'function') {
          var iframe = window.parent.document.getElementsByName('frame23')[0];
          window.parent.resizeIframe(iframe);
        }
      });

      if (window.parent && typeof window.parent.resizeIframe === 'function') {
        var iframe = window.parent.document.getElementsByName('frame23')[0];
        window.parent.resizeIframe(iframe);
      }
    });

    // Helper untuk menyesuaikan scroll parent saat modal dibuka dalam iframe
    function scrollToModalInIframe() {
      try {
        if (window.parent && window.parent !== window) {
          // Cari posisi iframe di parent window
          var iframe = window.parent.document.getElementsByName('frame23')[0];
          if (iframe) {
            var rect = iframe.getBoundingClientRect();
            var parentScroll = window.parent.pageYOffset || window.parent.document.documentElement.scrollTop || 0;
            var targetY = parentScroll + rect.top;
            if (targetY < parentScroll || targetY > parentScroll + 300) {
              window.parent.scrollTo({ top: Math.max(0, targetY - 20), behavior: 'smooth' });
            }
          }
        }
      } catch(e) {}
    }

    // Helper untuk menyesuaikan tinggi scroll modal-body agar tidak terpotong di layar
    function adjustModalBodyHeight() {
      var winH = 650;
      try {
        if (window.parent && window.parent !== window && window.parent.innerHeight) {
          winH = window.parent.innerHeight;
        } else if (window.innerHeight) {
          winH = window.innerHeight;
        }
      } catch(e) {}
      
      var maxBodyH = Math.max(260, Math.min(480, winH - 180));
      $('#editModal .modal-body, #viewModal .modal-body').css({
        'max-height': maxBodyH + 'px',
        'overflow-y': 'auto'
      });
    }

    $(document).on('show.bs.modal', '#editModal, #viewModal', function() {
      adjustModalBodyHeight();
    });
    $(window).on('resize', adjustModalBodyHeight);

    // Modal View Handler
    $(document).on('click', '.btn-view', function() {
      const id = $(this).data('id');
      const d = gangguanData[id];
      if (!d) return;

      // Header Badges
      $('#view_badge_kode').text(d.kodegangguan ? d.kodegangguan : ('ID: ' + d.idgangguan));
      
      const isPMT = (d.kat_gangguan === 'PMT');
      $('#view_badge_kat')
        .text(d.kat_gangguan)
        .removeClass('badge-pmt badge-rec')
        .addClass(isPMT ? 'badge-pmt' : 'badge-rec');

      const isPerm = (d.kategorigangguan === 'PERMANEN');
      $('#view_badge_keandalan')
        .text(d.kategorigangguan)
        .removeClass('badge-perm badge-temp')
        .addClass(isPerm ? 'badge-perm' : 'badge-temp');

      $('#view_badge_durasi').text((d.selisih_menit || '0') + ' Menit Padam');

      // Section 1: Lokasi & Jaringan
      $('#view_unit').text(d.uraian ? (d.uraian + ' (' + d.unit + ')') : (d.unit || '-'));
      $('#view_penyulang').text(d.uraianpenyul ? (d.uraianpenyul + ' (' + d.penyulang + ')') : (d.penyulang || '-'));
      $('#view_keypoint').text(d.keterangan ? d.keterangan : (isPMT ? 'PMT (Penyulang Utama)' : (d.keypointid || '-')));
      
      if (d.latlokasi && d.longlokasi) {
        $('#view_koordinat').text(d.latlokasi + ', ' + d.longlokasi);
        $('#view_maps_btn').attr('href', 'https://www.google.com/maps/place/' + d.latlokasi + ',' + d.longlokasi).removeClass('d-none');
      } else {
        $('#view_koordinat').text('-');
        $('#view_maps_btn').addClass('d-none');
      }

      // Section 2: Waktu
      $('#view_tglgangguan').text(d.tglgangguan || '-');
      $('#view_tglmasuk').text(d.tglmasuk || '-');
      $('#view_durasi_text').html('<span class="badge bg-warning text-dark fs-6 px-3 py-1">' + (d.selisih_menit || '0') + ' Menit</span>');

      // Section 3: Proteksi & Arus
      $('#view_relay').text(d.relay || '-');
      $('#view_fasa').text(d.fasa || '-');
      $('#view_kv0').text((d.kv0 || '0') + ' kV');
      $('#view_inetral').text((d.inetral || '0') + ' A');
      $('#view_ir').text((d.ir || '0') + ' A');
      $('#view_ies').text((d.ies || '0') + ' A');
      $('#view_it').text((d.it || '0') + ' A');

      // Section 4: Penyebab
      $('#view_cuaca').text(d.uraiancuaca || d.cuacakode || '-');
      $('#view_jenisgangguan').text(d.uraianjenisgangguan || d.jeniskode || '-');
      $('#view_hasiltemuan').text(d.hasiltemuan || '(Tidak ada keterangan temuan)');

      // Section 5: Foto
      if (d.foto1) {
        $('#view_foto1_wrapper').html("<img src='uploads/" + d.foto1 + "' class='img-thumb rounded shadow-sm' style='width: 140px; height: 140px; object-fit: cover; cursor: pointer;' data-img='uploads/" + d.foto1 + "' title='Klik untuk perbesar'>");
      } else {
        $('#view_foto1_wrapper').html("<span class='text-muted small'><i class='fa fa-image me-1'></i> Tidak ada foto 1</span>");
      }

      if (d.foto2) {
        $('#view_foto2_wrapper').html("<img src='uploads/" + d.foto2 + "' class='img-thumb rounded shadow-sm' style='width: 140px; height: 140px; object-fit: cover; cursor: pointer;' data-img='uploads/" + d.foto2 + "' title='Klik untuk perbesar'>");
      } else {
        $('#view_foto2_wrapper').html("<span class='text-muted small'><i class='fa fa-image me-1'></i> Tidak ada foto 2</span>");
      }

      // Hubungkan tombol Edit Data Ini
      $('#btnSwitchToEdit').data('id', id);

      var modal = new bootstrap.Modal(document.getElementById('viewModal'));
      modal.show();
      scrollToModalInIframe();
    });

    // Beralih dari View Modal ke Edit Modal
    $('#btnSwitchToEdit').on('click', function() {
      const id = $(this).data('id');
      const viewModalEl = document.getElementById('viewModal');
      const viewModal = bootstrap.Modal.getInstance(viewModalEl);
      if (viewModal) viewModal.hide();
      
      setTimeout(function() {
        populateAndOpenEditModal(id);
      }, 350);
    });

    // Modal Edit Handler
    $(document).on('click', '.btn-edit', function() {
      const id = $(this).data('id');
      populateAndOpenEditModal(id);
    });

    function populateAndOpenEditModal(id) {
      const d = gangguanData[id];
      if (!d) return;

      $('#edit_idgangguan').val(d.idgangguan);
      $('#edit_modal_id_badge').text('ID: ' + d.idgangguan + (d.kodegangguan ? ' (' + d.kodegangguan + ')' : ''));
      $('#edit_kodegangguan').val(d.kodegangguan || '');

      // Toggle Radio PMT / REC
      if (d.kat_gangguan === 'PMT') {
        $('#edit_radio_pmt').prop('checked', true).trigger('change');
        // Set dropdown PMT
        const pmtVal = (d.unit || '') + '|' + (d.penyulang || '');
        if ($("#edit_select_pmt option[value='" + pmtVal + "']").length > 0) {
          $('#edit_select_pmt').val(pmtVal);
        } else if (d.penyulang) {
          // Jika option belum ada di dropdown, tambahkan fallback
          $('#edit_select_pmt').append(new Option((d.uraian || d.unit) + ' | ' + (d.uraianpenyul || d.penyulang), pmtVal, true, true));
        }
      } else {
        $('#edit_radio_rec').prop('checked', true).trigger('change');
        // Set dropdown REC
        const recVal = (d.unit || '') + '|' + (d.penyulang || '') + '|' + (d.keypointid || '');
        if ($("#edit_select_rec option[value='" + recVal + "']").length > 0) {
          $('#edit_select_rec').val(recVal);
        } else if (d.keypointid) {
          $('#edit_select_rec').append(new Option((d.uraian || d.unit) + ' | ' + (d.uraianpenyul || d.penyulang) + ' | ' + (d.keterangan || d.keypointid), recVal, true, true));
        }
      }

      // Waktu
      $('#edit_tglgangguan').val(formatForDateTimeLocal(d.tglgangguan));
      $('#edit_tglmasuk').val(formatForDateTimeLocal(d.tglmasuk));

      // Keandalan
      if (d.kategorigangguan === 'PERMANEN') {
        $('#edit_radio_perm').prop('checked', true);
      } else {
        $('#edit_radio_temp').prop('checked', true);
      }

      // Proteksi
      $('#edit_relay').val(d.relay || '');
      $('#edit_fasa').val(d.fasa || '');
      $('#edit_kv0').val(d.kv0 || '0');
      $('#edit_inol').val(d.inetral || '0');
      $('#edit_ir').val(d.ir || '0');
      $('#edit_ies').val(d.ies || '0');
      $('#edit_it').val(d.it || '0');

      // Cuaca & Jenis
      $('#edit_cuaca').val(d.cuacakode || '');
      $('#edit_jenisgangguan').val(d.jeniskode || '');

      // Koordinat
      $('#edit_latlokasi').val(d.latlokasi || '');
      $('#edit_longlokasi').val(d.longlokasi || '');

      // Temuan
      $('#edit_temuan').val(d.hasiltemuan || '');

      // Pratinjau Foto 1 & Foto 2
      $('#edit_hapus_foto1').prop('checked', false);
      if (d.foto1) {
        $('#edit_preview_foto1_img').attr('src', 'uploads/' + d.foto1);
        $('#edit_preview_foto1_box').removeClass('d-none');
      } else {
        $('#edit_preview_foto1_box').addClass('d-none');
      }

      $('#edit_hapus_foto2').prop('checked', false);
      if (d.foto2) {
        $('#edit_preview_foto2_img').attr('src', 'uploads/' + d.foto2);
        $('#edit_preview_foto2_box').removeClass('d-none');
      } else {
        $('#edit_preview_foto2_box').addClass('d-none');
      }

      var modal = new bootstrap.Modal(document.getElementById('editModal'));
      modal.show();
      scrollToModalInIframe();
    }

    // Toggle dropdown PMT vs REC pada Edit Modal
    $('input[name="kat_gangguan"]').on('change', function() {
      if ($('#edit_radio_pmt').is(':checked')) {
        $('#edit_box_pmt').show();
        $('#edit_select_pmt').prop('required', true);
        $('#edit_box_rec').hide();
        $('#edit_select_rec').prop('required', false);
      } else {
        $('#edit_box_pmt').hide();
        $('#edit_select_pmt').prop('required', false);
        $('#edit_box_rec').show();
        $('#edit_select_rec').prop('required', true);
      }
    });

    // Galeri modal foto zoom
    let imgList = [];
    let currentIndex = 0;

    $(document).on("click", ".img-thumb", function() {
      imgList = [];
      $(".img-thumb").each(function() {
        imgList.push($(this).data("img"));
      });

      currentIndex = $(".img-thumb").index(this);
      $("#modalImage").attr("src", imgList[currentIndex]);
      $("#imgModal").modal("show");
    });

    $("#nextImg").click(function() {
      currentIndex = (currentIndex + 1) % imgList.length;
      $("#modalImage").attr("src", imgList[currentIndex]);
    });

    $("#prevImg").click(function() {
      currentIndex = (currentIndex - 1 + imgList.length) % imgList.length;
      $("#modalImage").attr("src", imgList[currentIndex]);
    });

    // SweetAlert2 Delete Confirmation
    $(document).on('click', '.btn-delete', function(e) {
      e.preventDefault();
      const url = $(this).attr('href');
      
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data gangguan yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    });
  </script>

</div>
</body>
</html>
