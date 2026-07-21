<?php
include "connect.php";
// Hapus data (hanya unit 5125)
if(isset($_GET['hapus'])){
    $id_hapus = intval($_GET['hapus']);
    $gabungawal  = $_GET['awal'];
    $gabungakhir = $_GET['akhir'];
    $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
    $qf = mysql_query("SELECT foto1,foto2 FROM datagangguan WHERE idgangguan=$id_hapus");
    if($rf = mysql_fetch_assoc($qf)){
        foreach($rf as $f){
            if(!empty($f) && file_exists("uploads/".$f)) unlink("uploads/".$f);
        }
    }
    mysql_query("DELETE FROM datagangguan WHERE idgangguan=$id_hapus") or die(mysql_error());
    echo "<script>alert('Data berhasil dihapus');window.location='".$_SERVER['PHP_SELF']."?awal={$gabungawal}&akhir={$gabungakhir}&unit={$unit}';</script>";
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
      width: 30px;
      height: 30px;
      cursor: pointer;
      object-fit: cover;
    }
    .modal-img {
      max-width: 100%;
      max-height: 80vh;
    }
  </style>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
      $map = '<img src ="map.png" />';
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
                          WHERE tglgangguan BETWEEN '$gabungawal1' AND '$gabungakhir2'";
            } else {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE tglgangguan BETWEEN '$gabungawal1' AND '$gabungakhir2' AND unit = '$unit'";
            }
        } elseif ($gabungawal != '' && $gabungakhir != '') {
            if ($unit == '5125') {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE tglgangguan BETWEEN '$gabungawal' AND '$gabungakhir'";
            } else {
                $query = "SELECT * FROM v_datagangguan 
                          WHERE tglgangguan BETWEEN '$gabungawal' AND '$gabungakhir' 
                          AND unit = '$unit'";
            }
        }
        
        if ($query != "") {
            $hasil = mysql_query($query);
        }
        
        if ($hasil) {
            while ($data = mysql_fetch_array($hasil)) {
                if ($data['foto2'] != '') {
                    echo "<tr>
                        <td align='center'>$no</td>
                        <td>{$data['kodegangguan']}</td>
                        <td>{$data['uraian']}</td>
                        <td>{$data['uraianpenyul']}</td>
                        <td>{$data['keterangan']}</td>
                        <td align='center'>{$data['kat_gangguan']}</td>
                        <td>{$data['kategorigangguan']}</td>
                        <td align='center'>{$data['tglgangguan']}</td>
                        <td align='center'>{$data['tglmasuk']}</td>
                        <td align='center'>{$data['selisih_menit']}</td>
                        <td align='center'>{$data['relay']}</td>
                        <td align='center'>{$data['fasa']}</td>
                        <td align='center'>{$data['kv0']}</td>
                        <td align='center'>{$data['ir']}</td>
                        <td align='center'>{$data['ies']}</td>
                        <td align='center'>{$data['it']}</td>
                        <td align='center'>{$data['inetral']}</td>
                        <td>{$data['uraiancuaca']}</td>
                        <td>{$data['uraianjenisgangguan']}</td>
                        <td>{$data['hasiltemuan']}</td>
                        
                        <!-- Foto 1 -->
                        <td align='center'>
                          <img src='uploads/{$data['foto1']}' class='img-thumb' data-img='uploads/{$data['foto1']}' />
                        </td>

                        <!-- Foto 2 -->
                        <td align='center'>
                          <img src='uploads/{$data['foto2']}' class='img-thumb' data-img='uploads/{$data['foto2']}' />
                        </td>

                        <!-- Map -->
                        <td align='center'>
                          <a href='https://www.google.com/maps/place/{$data['latlokasi']},{$data['longlokasi']}' target='_blank' style='cursor:pointer' title='Cek Koordinat'>$map</a>
                        </td>

                        <td><a href='?hapus={$data['idgangguan']}&awal={$current_awal}&akhir={$current_akhir}&unit={$unit}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
                    </tr>";
                } else if ($data['foto1'] != '') {
                    echo "<tr>
                        <td align='center'>$no</td>
                        <td>{$data['kodegangguan']}</td>
                        <td>{$data['uraian']}</td>
                        <td>{$data['uraianpenyul']}</td>
                        <td>{$data['keterangan']}</td>
                        <td align='center'>{$data['kat_gangguan']}</td>
                        <td>{$data['kategorigangguan']}</td>
                        <td align='center'>{$data['tglgangguan']}</td>
                        <td align='center'>{$data['tglmasuk']}</td>
                        <td align='center'>{$data['selisih_menit']}</td>
                        <td align='center'>{$data['relay']}</td>
                        <td align='center'>{$data['fasa']}</td>
                        <td align='center'>{$data['kv0']}</td>
                        <td align='center'>{$data['ir']}</td>
                        <td align='center'>{$data['ies']}</td>
                        <td align='center'>{$data['it']}</td>
                        <td align='center'>{$data['inetral']}</td>
                        <td>{$data['uraiancuaca']}</td>
                        <td>{$data['uraianjenisgangguan']}</td>
                        <td>{$data['hasiltemuan']}</td>
                        
                        <!-- Foto 1 -->
                        <td align='center'>
                          <img src='uploads/{$data['foto1']}' class='img-thumb' data-img='uploads/{$data['foto1']}' />
                        </td>

                        <!-- Foto 2 -->
                        <td align='center'>
                        </td>

                        <!-- Map -->
                        <td align='center'>
                          <a href='https://www.google.com/maps/place/{$data['latlokasi']},{$data['longlokasi']}' target='_blank' style='cursor:pointer' title='Cek Koordinat'>$map</a>
                        </td>

                        <td><a href='?hapus={$data['idgangguan']}&awal={$current_awal}&akhir={$current_akhir}&unit={$unit}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
                    </tr>";
                } else {
                    echo "<tr>
                        <td align='center'>$no</td>
                        <td>{$data['kodegangguan']}</td>
                        <td>{$data['uraian']}</td>
                        <td>{$data['uraianpenyul']}</td>
                        <td>{$data['keterangan']}</td>
                        <td align='center'>{$data['kat_gangguan']}</td>
                        <td>{$data['kategorigangguan']}</td>
                        <td align='center'>{$data['tglgangguan']}</td>
                        <td align='center'>{$data['tglmasuk']}</td>
                        <td align='center'>{$data['selisih_menit']}</td>
                        <td align='center'>{$data['relay']}</td>
                        <td align='center'>{$data['fasa']}</td>
                        <td align='center'>{$data['kv0']}</td>
                        <td align='center'>{$data['ir']}</td>
                        <td align='center'>{$data['ies']}</td>
                        <td align='center'>{$data['it']}</td>
                        <td align='center'>{$data['inetral']}</td>
                        <td>{$data['uraiancuaca']}</td>
                        <td>{$data['uraianjenisgangguan']}</td>
                        <td>{$data['hasiltemuan']}</td>
                        
                        <!-- Foto 1 -->
                        <td align='center'>
                        </td>

                        <!-- Foto 2 -->
                        <td align='center'>
                        </td>

                        <!-- Map -->
                        <td align='center'>
                          <a href='https://www.google.com/maps/place/{$data['latlokasi']},{$data['longlokasi']}' target='_blank' style='cursor:pointer' title='Cek Koordinat'>$map</a>
                        </td>

                        <td><a href='?hapus={$data['idgangguan']}&awal={$current_awal}&akhir={$current_akhir}&unit={$unit}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
                    </tr>";
                }
                $no++;
            }
        }
        ?>
      </tbody>
    </table>
    </div>
    </div>
  </div>

  <!-- Modal Galeri -->
  <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-transparent border-0 shadow-none">
        <div class="modal-body text-center p-0 position-relative">
          <!-- Tombol Close -->
          <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                  data-bs-dismiss="modal" aria-label="Close"></button>
          <!-- Gambar -->
          <img id="modalImage" class="modal-img rounded" src="" alt="Preview" />
          <!-- Navigasi -->
          <button class="btn btn-dark position-absolute top-50 start-0 translate-middle-y px-3" id="prevImg">&lt;</button>
          <button class="btn btn-dark position-absolute top-50 end-0 translate-middle-y px-3" id="nextImg">&gt;</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // DataTables
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

    // Galeri modal
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
  </script>

</div>
</body>
</html>
