<?php
include "connect.php";

// Handle CRUD operations
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $kodeunit = mysql_real_escape_string($_POST['kodeunit']);
            $uraian = mysql_real_escape_string($_POST['uraian']);
            
            if (empty($kodeunit) || empty($uraian)) {
                $error = "Kode Unit dan Uraian tidak boleh kosong!";
            } else {
                $check = mysql_query("SELECT * FROM kodeunit WHERE kodeunit = '$kodeunit'");
                if (mysql_num_rows($check) > 0) {
                    $error = "Kode Unit sudah ada!";
                } else {
                    $query = "INSERT INTO kodeunit (kodeunit, uraian) VALUES ('$kodeunit', '$uraian')";
                    if (mysql_query($query)) {
                        $success = "Data Unit berhasil ditambahkan!";
                    } else {
                        $error = "Gagal menambahkan data: " . mysql_error();
                    }
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            $old_kodeunit = mysql_real_escape_string($_POST['old_kodeunit']);
            $kodeunit = mysql_real_escape_string($_POST['kodeunit']);
            $uraian = mysql_real_escape_string($_POST['uraian']);
            
            if (empty($kodeunit) || empty($uraian)) {
                $error = "Kode Unit dan Uraian tidak boleh kosong!";
            } else {
                $query = "UPDATE kodeunit SET kodeunit = '$kodeunit', uraian = '$uraian' WHERE kodeunit = '$old_kodeunit'";
                if (mysql_query($query)) {
                    $success = "Data Unit berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui data: " . mysql_error();
                }
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $kodeunit = mysql_real_escape_string($_GET['delete']);
    $query = "DELETE FROM kodeunit WHERE kodeunit = '$kodeunit'";
    if (mysql_query($query)) {
        $success = "Data Unit berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data: " . mysql_error();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Master Unit - Monitor Gangguan</title>

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
      font-size: 13px;
      padding: 10px !important;
    }
    table.dataTable tbody td {
      font-size: 13px;
      vertical-align: middle;
      text-align: center;
    }
    table.dataTable tbody td:nth-child(2) {
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
    <h4 class="page-title"><i class="fa fa-database me-2"></i>Master Unit</h4>
    <button class="btn btn-custom-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="fa fa-plus me-1"></i> Tambah Unit
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
        <table id="tableUnit" class="table table-striped table-bordered mb-0" style="width:100%">
          <thead>
            <tr>
              <th style="width: 20%;">Kode Unit</th>
              <th>Uraian Unit</th>
              <th style="width: 20%;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $q = mysql_query("SELECT * FROM kodeunit ORDER BY kodeunit ASC");
            while ($row = mysql_fetch_assoc($q)):
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['kodeunit']); ?></td>
                <td><?php echo htmlspecialchars($row['uraian']); ?></td>
                <td>
                  <button class="btn btn-warning btn-sm me-1 edit-btn" 
                          data-kode="<?php echo htmlspecialchars($row['kodeunit']); ?>"
                          data-uraian="<?php echo htmlspecialchars($row['uraian']); ?>">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <a href="?delete=<?php echo urlencode($row['kodeunit']); ?>" 
                     class="btn btn-danger btn-sm" 
                     onclick="return confirm('Apakah Anda yakin ingin menghapus unit ini?');">
                    <i class="fa fa-trash"></i> Hapus
                  </a>
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
          <h5 class="modal-title" id="addModalLabel"><i class="fa fa-plus me-2"></i>Tambah Unit</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add_kodeunit" class="form-label">Kode Unit</label>
            <input type="number" class="form-control" id="add_kodeunit" name="kodeunit" required>
          </div>
          <div class="mb-3">
            <label for="add_uraian" class="form-label">Uraian Unit</label>
            <input type="text" class="form-control" id="add_uraian" name="uraian" style="text-transform: uppercase;" required>
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
      <input type="hidden" id="old_kodeunit" name="old_kodeunit">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editModalLabel"><i class="fa fa-edit me-2"></i>Edit Unit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_kodeunit" class="form-label">Kode Unit</label>
            <input type="number" class="form-control" id="edit_kodeunit" name="kodeunit" required>
          </div>
          <div class="mb-3">
            <label for="edit_uraian" class="form-label">Uraian Unit</label>
            <input type="text" class="form-control" id="edit_uraian" name="uraian" style="text-transform: uppercase;" required>
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
    $('#tableUnit').DataTable({
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      }
    });

    // Populate edit modal
    $('.edit-btn').on('click', function() {
      var kode = $(this).data('kode');
      var uraian = $(this).data('uraian');
      
      $('#old_kodeunit').val(kode);
      $('#edit_kodeunit').val(kode);
      $('#edit_uraian').val(uraian);
      
      var editModal = new bootstrap.Modal(document.getElementById('editModal'));
      editModal.show();
    });
  });
</script>
</body>
</html>
