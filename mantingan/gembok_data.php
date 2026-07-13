<?php
session_start();
include "koneksi2.php";

if(!isset($_SESSION['login_gembok'])){
    header("Location:gembok.php");
    exit;
}

if(isset($_POST['hapus'])){
    if(isset($_POST['pilih'])){
        foreach($_POST['pilih'] as $idpel){
            mysql_query("DELETE FROM gembok WHERE idpel='$idpel'");
        }
    }
}

if(isset($_POST['export'])){
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=data_gembok.xls");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Data Gembok</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
body{
    font-family:Arial;
    padding:20px;
    background:#f4f6f9;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
}

.topbar{
    display:flex;
    gap:10px;
    margin-bottom:20px;
    flex-wrap:wrap;
}

button,a.btn{
    padding:10px 15px;
    border:none;
    border-radius:8px;
    color:white;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}

.hapus{
    background:#dc3545;
}

.export{
    background:#198754;
}

.logout{
    background:#6c757d;
}

.pilih{
    background:#0d6efd;
}

table.dataTable{
    width:100% !important;
}
</style>

</head>
<body>

<div class="card">

<h2>🔒 Data Gembok</h2>
<br>

<form method="POST">

<div class="topbar">

<button type="button" class="pilih" onclick="checkAll()">
☑ Pilih Semua
</button>

<button type="submit" name="hapus" class="hapus"
onclick="return confirm('Hapus data terpilih?')">
🗑 Hapus Terpilih
</button>

<button type="submit" name="export" class="export">
📊 Export Excel
</button>

<a href="logout_gembok.php" class="btn logout">
🚪 Logout
</a>

</div>

<table id="tabelku" class="display">

<thead>
<tr>
<th>Pilih</th>
<th>IDPEL</th>
<th>Nama</th>
<th>PIN</th>
<th>No Meter</th>
<th>Tgl Tera</th>
<th>Koneksi AMR</th>
<th>Shunttrip</th>
<th>Vendor Pasang</th>
<th>Petugas Tera</th>
</tr>
</thead>

<tbody>

<?php
$q=mysql_query("SELECT * FROM gembok ORDER BY idpel ASC");

while($d=mysql_fetch_array($q)){
?>

<tr>
<td>
<input type="checkbox" name="pilih[]" value="<?php echo $d['idpel']; ?>">
</td>
<td><?php echo $d['idpel']; ?></td>
<td><?php echo $d['nama']; ?></td>
<td><?php echo $d['pin']; ?></td>
<td><?php echo $d['nometer']; ?></td>
<td><?php echo $d['tgltera']; ?></td>
<td><?php echo $d['koneksiamr']; ?></td>
<td><?php echo $d['shunttrip']; ?></td>
<td><?php echo $d['vendorpasang']; ?></td>
<td><?php echo $d['petugastera']; ?></td>
</tr>

<?php } ?>

</tbody>

</table>

</form>

</div>

<script>
$(document).ready(function(){
    $('#tabelku').DataTable({
        pageLength:25
    });
});

function checkAll(){
    $('input[type=checkbox]').prop('checked', true);
}
</script>

</body>
</html>