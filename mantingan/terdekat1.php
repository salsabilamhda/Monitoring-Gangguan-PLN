<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cari Koordinat</title>

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

.card,.table-box{
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
    margin-bottom:10px;
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

.btn-lokasi{background:#198754;}
.btn-dekat{background:#6f42c1;}

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

<h2>Cari Koordinat</h2>
<br>

<form method="POST">

<input type="text"
       name="lat"
       id="lat"
       class="input"
       placeholder="Latitude"
       value="<?php if(isset($_POST['lat'])) echo $_POST['lat']; ?>">

<input type="text"
       name="lng"
       id="lng"
       class="input"
       placeholder="Longitude"
       value="<?php if(isset($_POST['lng'])) echo $_POST['lng']; ?>">

<button type="button"
        class="btn btn-lokasi"
        onclick="lokasiSaya()">
📍 Lokasi Saya
</button>

<button type="submit"
        class="btn btn-dekat">
🔍 Cari Koordinat
</button>

</form>

</div>

<?php
if(isset($_POST['lat']) && isset($_POST['lng']) &&
   $_POST['lat']!="" && $_POST['lng']!=""){

$lat=$_POST['lat'];
$lng=$_POST['lng'];
?>

<div class="card">

<a class="btn btn-dekat"
href="terdekat.php?mode=dekat_dil&lat=<?php echo $lat; ?>&lng=<?php echo $lng; ?>"
target="frameHasil">
Terdekat DIL 30M
</a>

<a class="btn btn-dekat"
href="terdekat.php?mode=dekat_dij&lat=<?php echo $lat; ?>&lng=<?php echo $lng; ?>"
target="frameHasil">
Terdekat DIJ 30M
</a>

</div>

<div class="table-box">
<iframe name="frameHasil"></iframe>
</div>

<?php } ?>

</div>

<script>
function lokasiSaya(){

    if(navigator.geolocation){

        navigator.geolocation.getCurrentPosition(function(position){

            document.getElementById("lat").value =
                position.coords.latitude;

            document.getElementById("lng").value =
                position.coords.longitude;

        }, function(){
            alert("Lokasi tidak bisa diambil");
        });

    }else{
        alert("Browser tidak mendukung GPS");
    }
}
</script>

</body>
</html>