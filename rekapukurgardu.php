<?php
include "connect.php";

// Ambil data dropdown
$trafo_list = [];
$unit_list = [];
$q1 = mysql_query("SELECT DISTINCT namaaset FROM ukurgardu WHERE namaaset IS NOT NULL ORDER BY namaaset");
while ($d1 = mysql_fetch_object($q1)) $trafo_list[] = $d1->namaaset;
$q2 = mysql_query("SELECT DISTINCT namaulp FROM ukurgardu WHERE namaulp IS NOT NULL ORDER BY namaulp");
while ($d2 = mysql_fetch_object($q2)) $unit_list[] = $d2->namaulp;

// Hapus satu data
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  mysql_query("DELETE FROM ukurgardu WHERE id = $id");
  echo "<script>alert('✅ Data berhasil dihapus'); window.location='rekapukurgardu.php';</script>";
  exit;
}

// Hapus semua
if (isset($_POST['hapus_semua'])) {
  mysql_query("TRUNCATE TABLE ukurgardu");
  echo "<script>alert('⚠️ Semua data berhasil dihapus'); window.location='rekapukurgardu.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Rekap Pengukuran Gardu | Zoter Dashboard</title>
    <meta content="Admin Dashboard" name="description" />
    <meta content="Mannatthemes" name="author" />

    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/plugins/select2/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <style>
      .page-title {
        color: #0d6efd;
        font-weight: 600;
        margin-bottom: 20px;
      }
      .card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: none;
      }
      .table th {
        background-color: #0d6efd;
        color: #fff;
        text-align: center;
        font-size: 13px;
      }
      .table td {
        vertical-align: middle;
        font-size: 13px;
      }
      .btn-sm { font-size: 12px; padding: 3px 8px; border-radius: 6px; }
      .btn-outline-primary:hover { background-color: #0d6efd; color: #fff; }
      .btn-outline-danger:hover { background-color: #dc3545; color: #fff; }
      .modal-header { background-color: #0d6efd; color: white; }
      .text-danger { color: #dc3545 !important; font-weight: bold; }
      .text-success { color: #198754 !important; font-weight: bold; }
    </style>
  </head>

  <body class="fixed-left">

    <div class="container-fluid mt-3">
      <div class="card p-4">
        <h4 class="page-title">📊 Rekap Pengukuran Gardu</h4>
        <form method="POST" action="">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-bold text-secondary">Filter Berdasarkan</label>
              <select class="form-control" name="filter_tipe" id="filter_tipe" required>
                <option value="">-- Pilih Filter --</option>
                <option value="trafo">Nama Trafo</option>
                <option value="unit">Nama ULP</option>
                <option value="tanggal">Tanggal Pengukuran</option>
              </select>
            </div>
            <div class="col-md-5" id="filter_value_box"></div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary w-100">Tampilkan Data</button>
            </div>
          </div>
        </form>
      </div>

      <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['hapus_semua'])) {
        $filter_tipe = $_POST["filter_tipe"];
        if ($filter_tipe == "trafo") {
          $namaaset = $_POST["namaaset"];
          $q = mysql_query("SELECT * FROM ukurgardu WHERE namaaset = '$namaaset'");
          echo "<h5 class='mt-4'>Hasil untuk Trafo: <b>$namaaset</b></h5>";
        } elseif ($filter_tipe == "unit") {
          $namaulp = $_POST["namaulp"];
          $q = mysql_query("SELECT * FROM ukurgardu WHERE namaulp = '$namaulp'");
          echo "<h5 class='mt-4'>Hasil untuk Unit: <b>$namaulp</b></h5>";
        } elseif ($filter_tipe == "tanggal") {
          $tglawal = $_POST["tglawal"];
          $tglakhir = $_POST["tglakhir"];
          $q = mysql_query("SELECT * FROM ukurgardu WHERE tanggalpengukuran BETWEEN '$tglawal' AND '$tglakhir'");
          echo "<h5 class='mt-4'>Hasil Pengukuran dari <b>$tglawal</b> s/d <b>$tglakhir</b></h5>";
        }

        if ($q && mysql_num_rows($q) > 0) {
          echo "
          <div class='card mt-3 p-3'>
            <div class='d-flex justify-content-between mb-2'>
              <h6 class='fw-bold text-primary'>Tabel Rekap Pengukuran</h6>
              <form method='POST' onsubmit=\"return confirm('⚠️ Hapus semua data?');\">
                <button name='hapus_semua' class='btn btn-danger btn-sm'>🗑️ Hapus Semua</button>
              </form>
            </div>
            <div class='table-responsive'>
              <table id='garduTable' class='table table-striped table-bordered align-middle'>
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Unit</th>
                    <th>ULP</th>
                    <th>Penyulang</th>
                    <th>Nama Aset</th>
                    <th>Kapasitas</th>
                    <th>Tanggal</th>
                    <th>Pembebanan (%)</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>";
          $no = 1;
          while ($r = mysql_fetch_assoc($q)) {
            $encoded = base64_encode(json_encode($r));
            $persen = floatval($r['pembebanantrafo']) * 100;
            $warna = ($persen > 80) ? "text-danger" : "text-success";
            echo "<tr>
              <td>$no</td>
              <td>{$r['namaunit']}</td>
              <td>{$r['namaulp']}</td>
              <td>{$r['namapenyulang']}</td>
              <td>{$r['namaaset']}</td>
              <td>{$r['kapasitasaset']}</td>
              <td>{$r['tanggalpengukuran']}</td>
              <td class='$warna'>" . round($persen,1) . "%</td>
              <td class='text-center'>
                <button type='button' class='btn btn-outline-primary btn-sm btnDetail' data-json='$encoded'>Detail</button>
                <a href='?hapus={$r['id']}' onclick='return confirm(\"Hapus data ini?\")' class='btn btn-outline-danger btn-sm'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
          echo "</tbody></table></div></div>";
        } else {
          echo "<div class='alert alert-warning mt-3'>⚠️ Tidak ada data ditemukan.</div>";
        }
      }
      ?>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Pengukuran Gardu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="detailContent"></div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Script Zoter -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/plugins/select2/select2.min.js"></script>
    <script src="assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
      const dataTrafo = <?php echo json_encode($trafo_list); ?>;
      const dataULP = <?php echo json_encode($unit_list); ?>;

      $(document).ready(function(){
        $('#filter_tipe').change(function(){
          let tipe = $(this).val(), box = $('#filter_value_box');
          box.html('');
          if (tipe === 'trafo') {
            let html = '<label>Nama Trafo</label><select class="form-control select2" name="namaaset" required><option value="">-- Pilih Trafo --</option>';
            dataTrafo.forEach(v => html += `<option value="${v}">${v}</option>`);
            html += '</select>'; box.html(html); $('.select2').select2();
          }
          else if (tipe === 'unit') {
            let html = '<label>Nama ULP</label><select class="form-control select2" name="namaulp" required><option value="">-- Pilih ULP --</option>';
            dataULP.forEach(v => html += `<option value="${v}">${v}</option>`);
            html += '</select>'; box.html(html); $('.select2').select2();
          }
          else if (tipe === 'tanggal') {
            box.html(`
              <label>Tanggal Pengukuran</label>
              <div class="input-daterange input-group">
                <input type="text" class="form-control datepicker" name="tglawal" placeholder="Dari" required>
                <span class="input-group-text">s.d</span>
                <input type="text" class="form-control datepicker" name="tglakhir" placeholder="Sampai" required>
              </div>`);
            $('.datepicker').datepicker({format: 'yyyy-mm-dd', autoclose: true});
          }
        });

        $('#garduTable').DataTable({
          pageLength: 10,
          order: [[0, 'asc']],
          language: {
            search: "🔍 Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "‹ Sebelumnya", next: "Berikutnya ›" }
          }
        });

        $(document).on('click', '.btnDetail', function(){
          const data = JSON.parse(atob($(this).attr('data-json')));
          let html = '';
          for (const key in data) {
            if (data[key] && data[key] !== "NULL" && data[key] !== "") {
              let val = data[key], cls = '';
              if (key.toLowerCase().includes("pembebanan")) {
                let num = parseFloat(val) * 100;
                val = num.toFixed(1) + '%';
                cls = num > 80 ? 'text-danger' : 'text-success';
              }
              html += `<div class='row border-bottom py-1'>
                <div class='col-md-4 fw-bold text-secondary'>${key}</div>
                <div class='col-md-8 ${cls}'>${val}</div>
              </div>`;
            }
          }
          $('#detailContent').html(html);
          const modal = new bootstrap.Modal(document.getElementById('detailModal'));
          modal.show();
        });
      });
    </script>
  </body>
</html>
