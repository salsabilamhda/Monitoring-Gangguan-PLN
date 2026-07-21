<?php
include "connect.php";

// Handle CRUD operations
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $uraianjenisgangguan = mysql_real_escape_string($_POST['uraianjenisgangguan']);
            
            if (empty($uraianjenisgangguan)) {
                $error = "Nama Jenis Gangguan tidak boleh kosong!";
            } else {
                $query = "INSERT INTO kodejenisgangguan (uraianjenisgangguan) VALUES ('$uraianjenisgangguan')";
                if (mysql_query($query)) {
                    $success = "Data Jenis Gangguan berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan data: " . mysql_error();
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            $idjenisgangguan = (int)$_POST['idjenisgangguan'];
            $uraianjenisgangguan = mysql_real_escape_string($_POST['uraianjenisgangguan']);
            
            if (empty($uraianjenisgangguan)) {
                $error = "Nama Jenis Gangguan tidak boleh kosong!";
            } else {
                $query = "UPDATE kodejenisgangguan SET uraianjenisgangguan = '$uraianjenisgangguan' WHERE idjenisgangguan = $idjenisgangguan";
                if (mysql_query($query)) {
                    $success = "Data Jenis Gangguan berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui data: " . mysql_error();
                }
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $idjenisgangguan = (int)$_GET['delete'];
    $query = "DELETE FROM kodejenisgangguan WHERE idjenisgangguan = $idjenisgangguan";
    if (mysql_query($query)) {
        $success = "Data Jenis Gangguan berhasil dihapus!";
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
  <title>Master Jenis Gangguan - Monitor Gangguan</title>

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
    <h4 class="page-title"><i class="fa fa-database me-2"></i>Master Jenis Gangguan</h4>
    <button class="btn btn-custom-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="fa fa-plus me-1"></i> Tambah Jenis Gangguan
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
        <table id="tableJenisGangguan" class="table table-striped table-bordered mb-0" style="width:100%">
          <thead>
            <tr>
              <th style="width: 15%;">ID Jenis Gangguan</th>
              <th>Uraian Jenis Gangguan</th>
              <th style="width: 20%;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $q = mysql_query("SELECT * FROM kodejenisgangguan ORDER BY idjenisgangguan ASC");
            while ($row = mysql_fetch_assoc($q)):
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['idjenisgangguan']); ?></td>
                <td><?php echo htmlspecialchars($row['uraianjenisgangguan']); ?></td>
                <td>
                  <button class="btn btn-warning btn-sm me-1 edit-btn" 
                          data-id="<?php echo htmlspecialchars($row['idjenisgangguan']); ?>"
                          data-uraian="<?php echo htmlspecialchars($row['uraianjenisgangguan']); ?>">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <a href="?delete=<?php echo urlencode($row['idjenisgangguan']); ?>" 
                     class="btn btn-danger btn-sm" 
                     onclick="return confirm('Apakah Anda yakin ingin menghapus data jenis gangguan ini?');">
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
          <h5 class="modal-title" id="addModalLabel"><i class="fa fa-plus me-2"></i>Tambah Jenis Gangguan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add_uraianjenisgangguan" class="form-label">Uraian Jenis Gangguan</label>
            <input type="text" class="form-control" id="add_uraianjenisgangguan" name="uraianjenisgangguan" style="text-transform: uppercase;" required>
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
      <input type="hidden" id="edit_idjenisgangguan" name="idjenisgangguan">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="editModalLabel"><i class="fa fa-edit me-2"></i>Edit Jenis Gangguan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_uraianjenisgangguan" class="form-label">Uraian Jenis Gangguan</label>
            <input type="text" class="form-control" id="edit_uraianjenisgangguan" name="uraianjenisgangguan" style="text-transform: uppercase;" required>
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
    $('#tableJenisGangguan').DataTable({
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      }
    });

    // Populate edit modal
    $('.edit-btn').on('click', function() {
      var id = $(this).data('id');
      var uraian = $(this).data('uraian');
      
      $('#edit_idjenisgangguan').val(id);
      $('#edit_uraianjenisgangguan').val(uraian);
      
      var editModal = new bootstrap.Modal(document.getElementById('editModal'));
      editModal.show();
    });
  });
</script>
</body>
</html>
