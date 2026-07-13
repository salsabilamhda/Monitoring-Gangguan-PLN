<?php
include "connect.php";
// Hapus data (hanya unit 5125)
if(isset($_GET['hapus'])){
    $id_hapus = intval($_GET['hapus']);
        $gabungawal  = $_GET['awal'];
    $gabungakhir = $_GET['akhir'];
    $qf = mysql_query("SELECT foto1,foto2 FROM datagangguan WHERE idgangguan=$id_hapus");
    if($rf = mysql_fetch_assoc($qf)){
        foreach($rf as $f){
            if(!empty($f) && file_exists("uploads/".$f)) unlink("uploads/".$f);
        }
    }
    mysql_query("DELETE FROM datagangguan WHERE idgangguan=$id_hapus") or die(mysql_error());
    echo "<script>alert('Data berhasil dihapus');window.location='".$_SERVER['PHP_SELF']."?awal={$gabungawal}&akhir={$gabungakhir}';</script>";
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
      background-color: #f4f4f4;
    }
    .container {
      background: white;
      padding: 10px;
      border-radius: 8px;
    }
    table.dataTable thead th {
      text-align: center;
      vertical-align: middle;
    }
    table.dataTable {
      font-size: 12px;
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
  
  <div class="container-fluid">
      <?php
      $tglawal = $_POST['tglawal'];
      $tglakhir = $_POST['tglakhir'];
      $tglawal1 = explode("/",$tglawal);
      $gabungawal = $tglawal1[2].'-'.$tglawal1[0].'-'.$tglawal1[1];
      $tglakhir1 = explode("/",$tglakhir);
      $gabungakhir = $tglakhir1[2].'-'.$tglakhir1[0].'-'.$tglakhir1[1];
      $unit = $_POST['unit'];
        $gabungawal1  = $_GET['awal'];
    $gabungakhir2 = $_GET['akhir'];
      $map = '<img src ="map.png" />';
      $lokasi='https://www.google.com/maps/place/';
      ?>
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
        if ($unit == '5125' &&  $gabungawal1 == '') {
          $query = "SELECT * FROM v_datagangguan 
                    WHERE tglgangguan BETWEEN '$gabungawal' AND '$gabungakhir'";
        } 
      else   if ($unit == '5125' &&  $gabungawal1 != '') {
          $query = "SELECT * FROM v_datagangguan 
                    WHERE tglgangguan BETWEEN '$gabungawal1' AND '$gabungakhir2'";
        } 
         else   if ($unit != '5125' &&  $gabungawal1 != '') {
          $query = "SELECT * FROM v_datagangguan 
                    WHERE tglgangguan BETWEEN '$gabungawal1' AND '$gabungakhir2' AND unit = '$unit'";
        } 
        
        else {
          $query = "SELECT * FROM v_datagangguan 
                    WHERE tglgangguan BETWEEN '$gabungawal' AND '$gabungakhir' 
                    AND unit = '$unit'";
        }
        
        $hasil = mysql_query($query);
        while ($data = mysql_fetch_array($hasil)) {
        if ($data[foto2] != '')
          echo "<tr>
            <td align='center'>$no</td>
              <td>$data[kodegangguan]</td>
            <td>$data[uraian]</td>
            <td>$data[uraianpenyul]</td>
            <td>$data[keterangan]</td>
            <td align='center'>$data[kat_gangguan]</td>
            <td>$data[kategorigangguan]</td>
            <td align='center'>$data[tglgangguan]</td>
            <td align='center'>$data[tglmasuk]</td>
            <td align='center'>$data[selisih_menit]</td>
            <td align='center'>$data[relay]</td>
            <td align='center'>$data[fasa]</td>
            <td align='center'>$data[kv0]</td>
            <td align='center'>$data[ir]</td>
            <td align='center'>$data[ies]</td>
            <td align='center'>$data[it]</td>
            <td align='center'>$data[inetral]</td>
            <td>$data[uraiancuaca]</td>
            <td>$data[uraianjenisgangguan]</td>
            <td>$data[hasiltemuan]</td>
            
            <!-- Foto 1 -->
            <td align='center'>
              <img src='uploads/$data[foto1]' class='img-thumb' data-img='uploads/$data[foto1]' />
            </td>

            <!-- Foto 2 -->
            <td align='center'>
              <img src='uploads/$data[foto2]' class='img-thumb' data-img='uploads/$data[foto2]' />
            </td>

            <!-- Map -->
            <td align='center'>
            <a href=https://www.google.com/maps/place/$data[latlokasi],$data[longlokasi]  target='_blank' style='cursor:pointer' title ='Cek Koordinat'>$map<a>
            </td>

            <td> <a href='?hapus={$data['idgangguan']}&awal={$gabungawal}&akhir={$gabungakhir}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
          </tr>";
          else if ($data[foto1] != '')
        
          echo "<tr>
            <td align='center'>$no</td>
              <td>$data[kodegangguan]</td>
            <td>$data[uraian]</td>
            <td>$data[uraianpenyul]</td>
            <td>$data[keterangan]</td>
            <td align='center'>$data[kat_gangguan]</td>
            <td>$data[kategorigangguan]</td>
            <td align='center'>$data[tglgangguan]</td>
            <td align='center'>$data[tglmasuk]</td>
            <td align='center'>$data[selisih_menit]</td>
            <td align='center'>$data[relay]</td>
            <td align='center'>$data[fasa]</td>
            <td align='center'>$data[kv0]</td>
            <td align='center'>$data[ir]</td>
            <td align='center'>$data[ies]</td>
            <td align='center'>$data[it]</td>
            <td align='center'>$data[inetral]</td>
            <td>$data[uraiancuaca]</td>
            <td>$data[uraianjenisgangguan]</td>
            <td>$data[hasiltemuan]</td>
            
            <!-- Foto 1 -->
            <td align='center'>
              <img src='uploads/$data[foto1]' class='img-thumb' data-img='uploads/$data[foto1]' />
            </td>

            <!-- Foto 2 -->
            <td align='center'>
            </td>

            <!-- Map -->
            <td align='center'>
              <a href=https://www.google.com/maps/place/$data[latlokasi],$data[longlokasi]  target='_blank' style='cursor:pointer' title ='Cek Koordinat'>$map<a>
            </td>

            <td>  <a href='?hapus={$data['idgangguan']}&awal={$gabungawal}&akhir={$gabungakhir}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
          </tr>";
          else 
          echo "<tr>
            <td align='center'>$no</td>
              <td>$data[kodegangguan]</td>
            <td>$data[uraian]</td>
            <td>$data[uraianpenyul]</td>
            <td>$data[keterangan]</td>
            <td align='center'>$data[kat_gangguan]</td>
            <td>$data[kategorigangguan]</td>
            <td align='center'>$data[tglgangguan]</td>
            <td align='center'>$data[tglmasuk]</td>
            <td align='center'>$data[selisih_menit]</td>
            <td align='center'>$data[relay]</td>
            <td align='center'>$data[fasa]</td>
            <td align='center'>$data[kv0]</td>
            <td align='center'>$data[ir]</td>
            <td align='center'>$data[ies]</td>
            <td align='center'>$data[it]</td>
            <td align='center'>$data[inetral]</td>
            <td>$data[uraiancuaca]</td>
            <td>$data[uraianjenisgangguan]</td>
            <td>$data[hasiltemuan]</td>
            
            <!-- Foto 1 -->
            <td align='center'>
             
            </td>

            <!-- Foto 2 -->
            <td align='center'>
            </td>

            <!-- Map -->
            <td align='center'>
              <a href=https://www.google.com/maps/place/$data[latlokasi],$data[longlokasi]  target='_blank' style='cursor:pointer' title ='Cek Koordinat'>$map<a>
            </td>

            <td>  <a href='?hapus={$data['idgangguan']}&awal={$gabungawal}&akhir={$gabungakhir}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus?')\">Hapus</a></td>
          </tr>";
          $no++;
        }
        ?>
      </tbody>
    </table>
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
      $('#tabelPegawai').DataTable({
        scrollX: true,
        scrollY: '300px',
        scrollCollapse: true,
        paging: true,
        dom: 'Bfrtip',
        buttons: ['excelHtml5', 'pdfHtml5', 'print'],
        orderCellsTop: true
      });
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

</body>
</html>
