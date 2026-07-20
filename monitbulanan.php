<?php
include "connect.php";
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
      $tahun = !empty($_REQUEST['tahun']) ? $_REQUEST['tahun'] : date('Y');
      $kategori = !empty($_REQUEST['option']) ? $_REQUEST['option'] : 'ALL';
      $unit = !empty($_REQUEST['unit']) ? $_REQUEST['unit'] : '5125';
      
      $merah = '<span class="badge-dot bg-red" title="Range 4 - 8"></span>';
      $hijau = '<span class="badge-dot bg-green" title="Range 1 - 3"></span>';
      $hitam = '<span class="badge-dot bg-black" title="Lebih dari 8"></span>';
      include "connect.php";
      $query2 = "select * from kodeunit where kodeunit = '$unit'"; 
      $hasil2 = mysql_query ($query2);
      $data2 = mysql_fetch_array($hasil2);
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
            <td class="text-dark fw-semibold"><?php echo $data2['uraian']; ?></td>
          </tr>
          <tr>
            <td class="fw-bold text-secondary">Tahun</td>
            <td>:</td>
            <td class="text-dark fw-semibold"><?php echo $tahun; ?></td>
          </tr>
        </table>
      </div>
      <!-- Kategori Keterangan -->
      <div class="col-md-6 ps-md-4">
        <h6 class="text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 1px;">Kategori Keandalan (Setahun)</h6>
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
          <th colspan="2">Jan</th>
          <th colspan="2">Feb</th>
          <th colspan="2">Mar</th>
          <th colspan="2">Apr</th>
          <th colspan="2">Mei</th>
          <th colspan="2">Jun</th>
          <th colspan="2">Jul</th>
          <th colspan="2">Ags</th>
          <th colspan="2">Sep</th>
          <th colspan="2">Okt</th>
           <th colspan="2">Nov</th>
            <th colspan="2">Des</th>
             
        </tr>
        <tr>
          <th>P</th>
          <th>T</th>
        <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
          <th>P</th>
          <th>T</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;

        include "connect.php";
       if ($kategori == 'REC' && $unit == '5125'){
        $query = "SELECT * FROM v_gangguan where tahun = '$tahun'";}
        else if ($kategori == 'REC' && $unit != '5125')
        {
        $query = "SELECT * FROM v_gangguan where tahun = '$tahun' and unit = '$unit'";}
        else if ($kategori != 'REC' && $kategori != 'ALL' && $unit != '5125')
        {
        $query = "SELECT *,'' as keterangan FROM v_gangguan2 where tahun = '$tahun' and unit = '$unit'";}
         else if ($kategori != 'REC' && $kategori != 'ALL' && $unit == '5125'){
        $query = "SELECT *,'' as keterangan FROM v_gangguan2 where tahun = '$tahun'";}
      else if ($kategori == 'ALL' && $unit != '5125')
{
$query = "SELECT * 
FROM v_gangguanall 
where tahun = '$tahun' and unit = '$unit'";
}

else if ($kategori == 'ALL' && $unit == '5125'){
$query = "SELECT * 
FROM v_gangguanall 
where tahun = '$tahun'";
}
       $hasil = mysql_query ($query);
	while ($data = mysql_fetch_array($hasil))
	{	
	    $datatotal = $data['permanentotal'] + $data['temporertotal'];
	    if ($datatotal < 4)
	echo "
<tr>
<td align='center'>$no</td>
<td >$data[uraian]</td>
<td >$data[uraianpenyul]</td>
<td >$data[keterangan]</td>
<td align='center'>$hijau</td>
<td align='center'>$data[permanentotal]</td>
<td align='center'>$data[temporertotal]</td>
<td align='center'>$data[permanen1]</td>
<td align='center'>$data[temporer1]</td>
<td align='center'>$data[permanen2]</td>
<td align='center'>$data[temporer2]</td>
<td align='center'>$data[permanen3]</td>
<td align='center'>$data[temporer3]</td>
<td align='center'>$data[permanen4]</td>
<td align='center'>$data[temporer4]</td>
<td align='center'>$data[permanen5]</td>
<td align='center'>$data[temporer5]</td>
<td align='center'>$data[permanen6]</td>
<td align='center'>$data[temporer6]</td>
<td align='center'>$data[permanen7]</td>
<td align='center'>$data[temporer7]</td>
<td align='center'>$data[permanen8]</td>
<td align='center'>$data[temporer8]</td>
<td align='center'>$data[permanen9]</td>
<td align='center'>$data[temporer9]</td>
<td align='center'>$data[permanen10]</td>
<td align='center'>$data[temporer10]</td>
<td align='center'>$data[permanen11]</td>
<td align='center'>$data[temporer11]</td>
<td align='center'>$data[permanen12]</td>
<td align='center'>$data[temporer12]</td>

 </tr>";
else  if ($datatotal < 9)
	echo "
<tr>
<td align='center'>$no</td>
<td >$data[uraian]</td>
<td >$data[uraianpenyul]</td>
<td >$data[keterangan]</td>
<td align='center'>$merah</td>
<td align='center'>$data[permanentotal]</td>
<td align='center'>$data[temporertotal]</td>
<td align='center'>$data[permanen1]</td>
<td align='center'>$data[temporer1]</td>
<td align='center'>$data[permanen2]</td>
<td align='center'>$data[temporer2]</td>
<td align='center'>$data[permanen3]</td>
<td align='center'>$data[temporer3]</td>
<td align='center'>$data[permanen4]</td>
<td align='center'>$data[temporer4]</td>
<td align='center'>$data[permanen5]</td>
<td align='center'>$data[temporer5]</td>
<td align='center'>$data[permanen6]</td>
<td align='center'>$data[temporer6]</td>
<td align='center'>$data[permanen7]</td>
<td align='center'>$data[temporer7]</td>
<td align='center'>$data[permanen8]</td>
<td align='center'>$data[temporer8]</td>
<td align='center'>$data[permanen9]</td>
<td align='center'>$data[temporer9]</td>
<td align='center'>$data[permanen10]</td>
<td align='center'>$data[temporer10]</td>
<td align='center'>$data[permanen11]</td>
<td align='center'>$data[temporer11]</td>
<td align='center'>$data[permanen12]</td>
<td align='center'>$data[temporer12]</td>

 </tr>";
 else
	echo "
<tr>
<td align='center'>$no</td>
<td >$data[uraian]</td>
<td >$data[uraianpenyul]</td>
<td >$data[keterangan]</td>
<td align='center'>$hitam</td>
<td align='center'>$data[permanentotal]</td>
<td align='center'>$data[temporertotal]</td>
<td align='center'>$data[permanen1]</td>
<td align='center'>$data[temporer1]</td>
<td align='center'>$data[permanen2]</td>
<td align='center'>$data[temporer2]</td>
<td align='center'>$data[permanen3]</td>
<td align='center'>$data[temporer3]</td>
<td align='center'>$data[permanen4]</td>
<td align='center'>$data[temporer4]</td>
<td align='center'>$data[permanen5]</td>
<td align='center'>$data[temporer5]</td>
<td align='center'>$data[permanen6]</td>
<td align='center'>$data[temporer6]</td>
<td align='center'>$data[permanen7]</td>
<td align='center'>$data[temporer7]</td>
<td align='center'>$data[permanen8]</td>
<td align='center'>$data[temporer8]</td>
<td align='center'>$data[permanen9]</td>
<td align='center'>$data[temporer9]</td>
<td align='center'>$data[permanen10]</td>
<td align='center'>$data[temporer10]</td>
<td align='center'>$data[permanen11]</td>
<td align='center'>$data[temporer11]</td>
<td align='center'>$data[permanen12]</td>
<td align='center'>$data[temporer12]</td>

 </tr>";
  $no ++;
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
