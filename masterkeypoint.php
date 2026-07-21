<?php
include "connect.php";

// Handle CRUD operations
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $kodepenyul = mysql_real_escape_string($_POST['kodepenyul']);
            $jenis = mysql_real_escape_string($_POST['jenis']);
            $keterangan = mysql_real_escape_string($_POST['keterangan']);
            $unit = mysql_real_escape_string($_POST['unit']);
            $zona = mysql_real_escape_string($_POST['zona']);
            $latitud = mysql_real_escape_string($_POST['latitud']);
            $longitud = mysql_real_escape_string($_POST['longitud']);
            $id_keypint = (int)$_POST['id_keypint'];
            
            $query = "INSERT INTO kodekeypoint (kodepenyul, jenis, keterangan, unit, zona, latitud, longitud, id_keypint) 
                      VALUES ('$kodepenyul', '$jenis', '$keterangan', '$unit', '$zona', '$latitud', '$longitud', $id_keypint)";
            if (mysql_query($query)) {
                $success = "Data Keypoint berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan data: " . mysql_error();
            }
        } elseif ($_POST['action'] === 'edit') {
            $idkeypoint = (int)$_POST['idkeypoint'];
            $kodepenyul = mysql_real_escape_string($_POST['kodepenyul']);
            $jenis = mysql_real_escape_string($_POST['jenis']);
            $keterangan = mysql_real_escape_string($_POST['keterangan']);
            $unit = mysql_real_escape_string($_POST['unit']);
            $zona = mysql_real_escape_string($_POST['zona']);
            $latitud = mysql_real_escape_string($_POST['latitud']);
            $longitud = mysql_real_escape_string($_POST['longitud']);
            $id_keypint = (int)$_POST['id_keypint'];
            
            $query = "UPDATE kodekeypoint SET 
                        kodepenyul = '$kodepenyul', 
                        jenis = '$jenis', 
                        keterangan = '$keterangan', 
                        unit = '$unit', 
                        zona = '$zona', 
                        latitud = '$latitud', 
                        longitud = '$longitud', 
                        id_keypint = $id_keypint 
                      WHERE idkeypoint = $idkeypoint";
            if (mysql_query($query)) {
                $success = "Data Keypoint berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data: " . mysql_error();
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $idkeypoint = (int)$_GET['delete'];
    $query = "DELETE FROM kodekeypoint WHERE idkeypoint = $idkeypoint";
    if (mysql_query($query)) {
        $success = "Data Keypoint berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data: " . mysql_error();
    }
}

// Get list of units and penyulangs for dropdowns
$units = [];
$qu = mysql_query("SELECT * FROM kodeunit ORDER BY uraian ASC");
while ($ru = mysql_fetch_assoc($qu)) {
    $units[] = $ru;
}

$penyulangs = [];
$qp = mysql_query("SELECT * FROM kodepenyulang ORDER BY kodepenyul ASC");
while ($rp = mysql_fetch_assoc($qp)) {
    $penyulangs[] = $rp;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Master Keypoint - Monitor Gangguan</title>

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f4f6f9;
      color: #333;
      padding: 15px !important;
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
      margin-bottom: 20px;
    }
    .card-body {
      padding: 20px;
    }
    .page-title {
      margin-top: 5px;
      margin-bottom: 15px;
      font-weight: 600;
      color: #242c6d;
    }
    table.dataTable thead th {
      background-color: #242c6d !important;
      color: #ffffff !important;
      text-align: center;
      vertical-align: middle;
      font-weight: 600;
      border-bottom: 2px solid #dee2e6 !important;
      font-size: 12px;
      padding: 8px !important;
    }
    table.dataTable tbody td {
      font-size: 12px;
      vertical-align: middle;
      text-align: center;
    }
    table.dataTable tbody td:nth-child(3) {
      text-align: left !important;
    }
    .table-responsive {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      overflow-x: auto;
    }
    .btn-custom-primary {
      background-color: #242c6d !important;
      border-color: #242c6d !important;
      color: white !important;
    }
    .btn-custom-primary:hover {
      background-color: #1d2358 !important;
      border-color: #1d2358 !important;
    }
  </style>

  <!-- CSS CDN -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <!-- JS CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div id="content-wrapper">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title"><i class="fa fa-database me-2"></i>Master Keypoint</h4>
    <button class="btn btn-custom-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="fa fa-plus me-1"></i> Tambah Keypoint
    </button>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i> <?php echo $success; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tableKeypoint" class="table table-striped table-bordered mb-0" style="width:100%">
          <thead>
            <tr>
              <th>ID Keypoint</th>
              <th>Penyulang</th>
              <th>Jenis</th>
              <th>Keterangan</th>
              <th>Unit</th>
              <th>Zona</th>
              <th>Latitude</th>
              <th>Longitude</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $q = mysql_query("
              SELECT k.*, u.uraian AS nama_unit 
              FROM kodekeypoint k 
              LEFT JOIN kodeunit u ON k.unit = u.kodeunit 
              ORDER BY k.idkeypoint DESC
            ");
            while ($row = mysql_fetch_assoc($q)):
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['id_keypint']); ?></td>
                <td><?php echo htmlspecialchars($row['kodepenyul']); ?></td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['jenis']); ?></span></td>
                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                <td><?php echo htmlspecialchars($row['nama_unit'] ? $row['nama_unit'] : $row['unit']); ?></td>
                <td><?php echo htmlspecialchars($row['zona']); ?></td>
                <td><?php echo htmlspecialchars($row['latitud']); ?></td>
                <td><?php echo htmlspecialchars($row['longitud']); ?></td>
                <td>
                  <div class="d-flex">
                    <button class="btn btn-warning btn-sm me-1 edit-btn" 
                            data-idkeypoint="<?php echo htmlspecialchars($row['idkeypoint']); ?>"
                            data-id_keypint="<?php echo htmlspecialchars($row['id_keypint']); ?>"
                            data-penyul="<?php echo htmlspecialchars($row['kodepenyul']); ?>"
                            data-jenis="<?php echo htmlspecialchars($row['jenis']); ?>"
                            data-keterangan="<?php echo htmlspecialchars($row['keterangan']); ?>"
                            data-unit="<?php echo htmlspecialchars($row['unit']); ?>"
                            data-zona="<?php echo htmlspecialchars($row['zona']); ?>"
                            data-latitud="<?php echo htmlspecialchars($row['latitud']); ?>"
                            data-longitud="<?php echo htmlspecialchars($row['longitud']); ?>">
                      <i class="fa fa-edit"></i>
                    </button>
                    <a href="?delete=<?php echo urlencode($row['idkeypoint']); ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus keypoint ini?');">
                      <i class="fa fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="addModalLabel"><i class="fa fa-plus me-2"></i>Tambah Keypoint</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="add_id_keypint" class="form-label">ID Keypoint</label>
              <input type="number" class="form-control" id="add_id_keypint" name="id_keypint" required>
            </div>
            <div class="col-md-6">
              <label for="add_jenis" class="form-label">Jenis</label>
              <select class="form-select" id="add_jenis" name="jenis" required>
                <option value="REC">REC</option>
                <option value="LBS">LBS</option>
                <option value="CO">CO</option>
                <option value="PTCT">PTCT</option>
                <option value="LBSM">LBSM</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label for="add_kodepenyul" class="form-label">Penyulang</label>
            <select class="form-select" id="add_kodepenyul" name="kodepenyul" required>
              <?php foreach ($penyulangs as $p): ?>
                <option value="<?php echo htmlspecialchars($p['kodepenyul']); ?>"><?php echo htmlspecialchars($p['kodepenyul'] . ' - ' . $p['uraianpenyul']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_keterangan" class="form-label">Keterangan / Lokasi</label>
            <input type="text" class="form-control" id="add_keterangan" name="keterangan" required>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="add_unit" class="form-label">Unit / ULP</label>
              <select class="form-select" id="add_unit" name="unit" required>
                <?php foreach ($units as $u): ?>
                  <option value="<?php echo htmlspecialchars($u['kodeunit']); ?>"><?php echo htmlspecialchars($u['uraian']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="add_zona" class="form-label">Zona</label>
              <input type="text" class="form-control" id="add_zona" name="zona">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="add_latitud" class="form-label">Latitude</label>
              <input type="text" class="form-control" id="add_latitud" name="latitud">
            </div>
            <div class="col-md-6">
              <label for="add_longitud" class="form-label">Longitude</label>
              <input type="text" class="form-control" id="add_longitud" name="longitud">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-custom-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" id="edit_idkeypoint" name="idkeypoint">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editModalLabel"><i class="fa fa-edit me-2"></i>Edit Keypoint</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_id_keypint" class="form-label">ID Keypoint</label>
              <input type="number" class="form-control" id="edit_id_keypint" name="id_keypint" required>
            </div>
            <div class="col-md-6">
              <label for="edit_jenis" class="form-label">Jenis</label>
              <select class="form-select" id="edit_jenis" name="jenis" required>
                <option value="REC">REC</option>
                <option value="LBS">LBS</option>
                <option value="CO">CO</option>
                <option value="PTCT">PTCT</option>
                <option value="LBSM">LBSM</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit_kodepenyul" class="form-label">Penyulang</label>
            <select class="form-select" id="edit_kodepenyul" name="kodepenyul" required>
              <?php foreach ($penyulangs as $p): ?>
                <option value="<?php echo htmlspecialchars($p['kodepenyul']); ?>"><?php echo htmlspecialchars($p['kodepenyul'] . ' - ' . $p['uraianpenyul']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_keterangan" class="form-label">Keterangan / Lokasi</label>
            <input type="text" class="form-control" id="edit_keterangan" name="keterangan" required>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_unit" class="form-label">Unit / ULP</label>
              <select class="form-select" id="edit_unit" name="unit" required>
                <?php foreach ($units as $u): ?>
                  <option value="<?php echo htmlspecialchars($u['kodeunit']); ?>"><?php echo htmlspecialchars($u['uraian']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_zona" class="form-label">Zona</label>
              <input type="text" class="form-control" id="edit_zona" name="zona">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="edit_latitud" class="form-label">Latitude</label>
              <input type="text" class="form-control" id="edit_latitud" name="latitud">
            </div>
            <div class="col-md-6">
              <label for="edit_longitud" class="form-label">Longitude</label>
              <input type="text" class="form-control" id="edit_longitud" name="longitud">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning text-dark">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#tableKeypoint').DataTable({
      "order": [[0, "desc"]],
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      }
    });

    // Populate edit modal
    $('.edit-btn').on('click', function() {
      $('#edit_idkeypoint').val($(this).data('idkeypoint'));
      $('#edit_id_keypint').val($(this).data('id_keypint'));
      $('#edit_kodepenyul').val($(this).data('penyul'));
      $('#edit_jenis').val($(this).data('jenis'));
      $('#edit_keterangan').val($(this).data('keterangan'));
      $('#edit_unit').val($(this).data('unit'));
      $('#edit_zona').val($(this).data('zona'));
      $('#edit_latitud').val($(this).data('latitud'));
      $('#edit_longitud').val($(this).data('longitud'));
      
      var editModal = new bootstrap.Modal(document.getElementById('editModal'));
      editModal.show();
    });
  });
</script>
</body>
</html>
