<?php
include "connect.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Monitor Gangguan - Rekap Tahunan</title>

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
    .badge-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      display: inline-block;
      vertical-align: middle;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }
    .bg-green { background-color: #28a745; }
    .bg-red { background-color: #dc3545; }
    .bg-black { background-color: #212529; }
    
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
    /* Styling buttons for DataTables */
    .dt-buttons .dt-button {
      background: #242c6d !important;
      color: white !important;
      border: none !important;
      border-radius: 4px !important;
      padding: 6px 12px !important;
      font-size: 12px !important;
      transition: all 0.3s ease;
    }
    .dt-buttons .dt-button:hover {
      background: #1d2358 !important;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
  </style>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery + DataTables + Buttons -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

</head>
<body>
<div id="content-wrapper">
      <?php
      $kategori = !empty($_REQUEST['option']) ? $_REQUEST['option'] : 'ALL';
      $unit = !empty($_REQUEST['unit']) ? $_REQUEST['unit'] : '5125';
      $tahun_awal = !empty($_REQUEST['tahun_awal']) ? (int)$_REQUEST['tahun_awal'] : (int)date('Y') - 4;
      $tahun_akhir = !empty($_REQUEST['tahun_akhir']) ? (int)$_REQUEST['tahun_akhir'] : (int)date('Y');
      
      $merah = '<span class="badge-dot bg-red" title="Range 4 - 8"></span>';
      $hijau = '<span class="badge-dot bg-green" title="Range 1 - 3"></span>';
      $hitam = '<span class="badge-dot bg-black" title="Lebih dari 8"></span>';
      
      $query2 = "select * from kodeunit where kodeunit = '$unit'"; 
      $hasil2 = mysql_query ($query2);
      $data2 = mysql_fetch_array($hasil2);
      
      // Ambil list tahun dari range
      $years = [];
      for ($y = $tahun_awal; $y <= $tahun_akhir; $y++) {
          $years[] = $y;
      }
      ?>       

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="row align-items-center">
      <!-- Info Filter -->
      <div class="col-md-6 border-end">
        <h6 class="text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 1px;">Informasi Filter</h6>
        <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
          <tr>
            <td style="width: 150px;" class="fw-bold text-secondary">Kategori Gangguan</td>
            <td style="width: 10px;">:</td>
            <td><span class="badge bg-primary px-2 py-1"><?php echo $kategori == 'REC' ? 'REC / PMCB' : $kategori; ?></span></td>
          </tr>
          <tr>
            <td class="fw-bold text-secondary">Unit / ULP</td>
            <td>:</td>
            <td class="text-dark fw-semibold"><?php echo isset($data2['uraian']) ? $data2['uraian'] : 'SEMUA UNIT'; ?></td>
          </tr>
          <tr>
            <td class="fw-bold text-secondary">Rentang Tahun</td>
            <td>:</td>
            <td class="text-dark fw-semibold"><?php echo $tahun_awal . " - " . $tahun_akhir; ?></td>
          </tr>
        </table>
      </div>
      <!-- Kategori Keterangan -->
      <div class="col-md-6 ps-md-4">
        <h6 class="text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 1px;">Kategori Keandalan (Tahunan)</h6>
        <div class="d-flex flex-column gap-2" style="font-size: 13px;">
          <div class="d-flex align-items-center">
            <span class="badge-dot bg-green me-2"></span>
            <span class="text-secondary">Range 1 - 3 Kali Gangguan (Hijau / Aman)</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="badge-dot bg-red me-2"></span>
            <span class="text-secondary">Range 4 - 8 Kali Gangguan (Merah / Waspada)</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="badge-dot bg-black me-2"></span>
            <span class="text-secondary">Lebih dari 8 Kali Gangguan (Hitam / Kritis)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tabelPegawai" class="display nowrap" style="width:100%">
      <thead>
        <tr>
          <th rowspan="2">No</th>
          <th rowspan="2">ULP</th>
          <th rowspan="2">Penyulang</th>
          <th rowspan="2">Recloser</th>
          <th rowspan="2">Status</th>
          <th colspan="2">Total</th>
          <?php foreach ($years as $yr) { ?>
            <th colspan="2"><?php echo $yr; ?></th>
          <?php } ?>
        </tr>
        <tr>
          <th>P</th>
          <th>T</th>
          <?php foreach ($years as $yr) { ?>
            <th>P</th>
            <th>T</th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;

        $select_parts = [
            "uraian",
            "uraianpenyul",
            "keterangan",
            "unit",
            "SUM(IF(YEAR(tglgangguan) BETWEEN $tahun_awal AND $tahun_akhir AND kategorigangguan = 'PERMANEN', hitung, 0)) as permanentotal",
            "SUM(IF(YEAR(tglgangguan) BETWEEN $tahun_awal AND $tahun_akhir AND kategorigangguan = 'TEMPORER', hitung, 0)) as temporertotal"
        ];
        foreach ($years as $y) {
            $select_parts[] = "SUM(IF(YEAR(tglgangguan) = $y AND kategorigangguan = 'PERMANEN', hitung, 0)) as permanen_$y";
            $select_parts[] = "SUM(IF(YEAR(tglgangguan) = $y AND kategorigangguan = 'TEMPORER', hitung, 0)) as temporer_$y";
        }

        $sql_select = implode(", ", $select_parts);

        $where_clauses = [];
        if ($kategori == 'REC') {
            $where_clauses[] = "kat_gangguan = 'REC'";
        } elseif ($kategori == 'PMT') {
            $where_clauses[] = "kat_gangguan = 'PMT'";
        }

        if ($unit != '5125') {
            $where_clauses[] = "unit = '$unit'";
        }

        $where_sql = "";
        if (!empty($where_clauses)) {
            $where_sql = "WHERE " . implode(" AND ", $where_clauses);
        }

        $query = "SELECT $sql_select 
                  FROM v_datagangguan 
                  $where_sql 
                  GROUP BY uraian, uraianpenyul, keterangan, unit 
                  ORDER BY unit ASC, uraianpenyul ASC";

        $hasil = mysql_query($query) or die(mysql_error());
        while ($data = mysql_fetch_array($hasil))
        {	
            $datatotal = $data['permanentotal'] + $data['temporertotal'];
            
            // Jika tidak ada gangguan pada rentang tahun terpilih, lewati
            if ($datatotal == 0) {
                continue;
            }
            
            // Tentukan status keandalan
            if ($datatotal < 4) {
                $status_indicator = $hijau;
            } else if ($datatotal < 9) {
                $status_indicator = $merah;
            } else {
                $status_indicator = $hitam;
            }

            echo "<tr>";
            echo "<td align='center'>$no</td>";
            echo "<td>" . htmlspecialchars($data['uraian']) . "</td>";
            echo "<td>" . htmlspecialchars($data['uraianpenyul']) . "</td>";
            echo "<td>" . htmlspecialchars($data['keterangan']) . "</td>";
            echo "<td align='center'>$status_indicator</td>";
            echo "<td align='center'>" . (int)$data['permanentotal'] . "</td>";
            echo "<td align='center'>" . (int)$data['temporertotal'] . "</td>";

            foreach ($years as $yr) {
                $p_val = (int)$data['permanen_' . $yr];
                $t_val = (int)$data['temporer_' . $yr];
                echo "<td align='center'>$p_val</td>";
                echo "<td align='center'>$t_val</td>";
            }
            echo "</tr>";
            $no++;
        }
        ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

  <script>
    $(document).ready(function() {
      var table = $('#tabelPegawai').DataTable({
        scrollX: false,
        paging: true,
        dom: 'Bfrtip',
        buttons: ['excelHtml5', 'pdfHtml5', 'print'],
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
  </script>

</div>
</body>
</html>
