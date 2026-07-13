<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cari Pelanggan</title>

<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    padding:15px;
    font-family:Arial;
    background:#f4f6f9;
}

.container{
    width:100%;
    max-width:1300px;
    margin:auto;
}

.card,.detail-card,.table-box{
    width:100%;
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
    margin-bottom:20px;
    overflow:hidden;
}

.input{
    width:100%;
    max-width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:14px;
}

.btn{
    padding:10px 16px;
    border:none;
    border-radius:8px;
    color:white;
    text-decoration:none;
    display:inline-block;
    margin:5px;
    cursor:pointer;
}

.btn-search{background:#0d6efd;}
.btn-map{background:#198754;}
.btn-jurusan{background:#fd7e14;}
.btn-dekat{background:#6f42c1;}

.detail-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:15px;
}

.item{
    background:#f8f9fa;
    padding:12px;
    border-radius:10px;
    word-break:break-word;
}

.label{
    font-weight:bold;
    color:#666;
}

iframe{
    width:100%;
    height:500px;
    border:none;
    border-radius:10px;
}
</style>
</head>
<body>

<div class="container">

<div class="card">

<form method="POST">

<input type="number"
       name="idpel"
       class="input"
       min="1"
       max="999999999999"
       placeholder="Masukkan No Meter"
       value="<?php if(isset($_POST['idpel'])) echo $_POST['idpel']; ?>">

<br><br>

<button type="submit" class="btn btn-search">
Cari Data
</button>

</form>

</div>

<?php
if(isset($_POST['idpel']) && $_POST['idpel']!=""){

$idpel=$_POST['idpel'];

$q=mysql_query("SELECT * FROM datapelanggan WHERE prepaid='$idpel' or nometer = '$idpel'");
$d=mysql_fetch_array($q);

if($d){
?>

<div class="detail-card">

<h2>Detail Pelanggan</h2>
<br>

<div class="detail-grid">

<?php
$fields=array(
"IDPEL"=>$d['idpel'],
"Nama"=>$d['nama'],
"Alamat"=>$d['alamat'],
"Tarif"=>$d['tarif'],
"Daya"=>$d['daya'],
"Prepaid"=>$d['prepaid'],
"No Meter"=>$d['nometer'],
"Gardu DIL"=>$d['gardudil'],
"Tiang DIL"=>$d['tiangdil'],
"Penyulang DIL"=>$d['penyulangdil'],
"Gardu DIJ"=>$d['gardudij'],
"Tiang DIJ"=>$d['tiangdij'],
"Penyulang DIJ"=>$d['penyulangdij']
);

foreach($fields as $label=>$value){
echo "
<div class='item'>
<div class='label'>$label</div>
$value
</div>
";
}
?>

</div>

<br>

<a class="btn btn-map" target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latdij']; ?>,<?php echo $d['longdij']; ?>">
Maps DIJ
</a>

<a class="btn btn-map" target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latgardudij']; ?>,<?php echo $d['longgardudij']; ?>">
Gardu DIJ
</a>

<a class="btn btn-map" target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latdil']; ?>,<?php echo $d['longdil']; ?>">
Maps DIL
</a>

<a class="btn btn-map" target="_blank"
href="https://www.google.com/maps?q=<?php echo $d['latgardudil']; ?>,<?php echo $d['longgardudil']; ?>">
Gardu DIL
</a>

<br><br>

<a class="btn btn-jurusan"
href="terdekat.php?mode=jurusan_dil&gardu=<?php echo $d['gardudil']; ?>&tiang=<?php echo $d['tiangdil']; ?>"
target="frameHasil">
Satu Jurusan DIL
</a>

<a class="btn btn-jurusan"
href="terdekat.php?mode=jurusan_dij&gardu=<?php echo $d['gardudij']; ?>&tiang=<?php echo $d['tiangdij']; ?>"
target="frameHasil">
Satu Jurusan DIJ
</a>

<a class="btn btn-dekat"
href="terdekat.php?mode=dekat_dil&lat=<?php echo $d['latdil']; ?>&lng=<?php echo $d['longdil']; ?>"
target="frameHasil">
Terdekat DIL 30M
</a>

<a class="btn btn-dekat"
href="terdekat.php?mode=dekat_dij&lat=<?php echo $d['latdij']; ?>&lng=<?php echo $d['longdij']; ?>"
target="frameHasil">
Terdekat DIJ 30M
</a>

</div>

<div class="table-box">
<iframe name="frameHasil"></iframe>
</div>

<?php
}else{
echo "<div class='detail-card'>Data tidak ditemukan</div>";
}
}
?>

</div>

</body>
</html>