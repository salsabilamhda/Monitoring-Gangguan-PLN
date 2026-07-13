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
      font-size: 12px; /* font lebih kecil */
    }

  </style>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

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
      <?php
	$tahun = $_REQUEST['tahun'];
	$kategori = $_REQUEST['option'];
	$unit = $_REQUEST['unit'];
	
	        $merah = '<img src ="merah1.png" />';
$hijau = '<img src ="hijau1.png" />';
$hitam = '<img src ="hitam1.png" />';
include "connect.php";
$query2 = "select * from kodeunit where kodeunit = '$unit'"; 
$hasil2 = mysql_query ($query2);
$data2 = mysql_fetch_array($hasil2)
?>       
<h5 style="background: white;padding :10px;">
     <hr>
    <table border = "0">
        <tr>
            <td>Kategori Gangguan</td>
            <td>:</td>
            <td><?php $a = 'PMT'; $b='REC / PMCB';$c='ALL'; if ($kategori == 'PMT')
            {echo $a;} elseif ($kategori == 'REC') {echo $b;} elseif ($kategori == 'ALL') {echo $c;} else {};
            ?> </td>
        </tr>
         <tr>
            <td>Unit</td>
            <td>:</td>
            <td><?php echo $data2['uraian']; ?></td>
        </tr>
         <tr>
            <td>Tahun</td>
            <td>:</td>
            <td><?php echo $tahun;  ?></td>
        </tr>
    </table>
    <hr>
    <table border="0">
        <tr><td>Cat :</td>
        <td><?php echo $hijau; ?> </td><td style="vertical-align: middle;">Range 1 - 3 Dalam Setahun</td><td><?php echo $merah; ?></td><td style="vertical-align: middle;">Range 4 - 8 Dalam Setahun</td><td><?php echo $hitam; ?></td><td style="vertical-align: middle;">Lebih 8 Dalam Setahun</td></tr>
      
    </table>
     <hr>
     </h5>
  <div class="container">
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
      echo $query;
exit;  
       $hasil = mysql_query ($query);
	while ($data = mysql_fetch_array($hasil))
	{	
	    $datatotal =$data[permanentotal]+$data[temporertotal] ;
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

  <script>
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
  </script>

</body>
</html>
