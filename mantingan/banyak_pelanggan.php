<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Peta Pelanggan</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
html, body{
    margin:0;
    padding:0;
    width:100%;
    height:100%;
    font-family:Arial, sans-serif;
    background:#f5f5f5;
}

.topbar{
    background:white;
    padding:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    position:sticky;
    top:0;
    z-index:999;
}

textarea{
    width:100%;
    height:45px;
    padding:8px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
    resize:none;
    box-sizing:border-box;
}

button{
    margin-top:8px;
    padding:8px 15px;
    border:none;
    background:#0d6efd;
    color:white;
    border-radius:8px;
    cursor:pointer;
}

#map{
    width:100%;
    height:calc(100vh - 95px);
}

.gps-btn{
    position:absolute;
    right:15px;
    bottom:20px;
    z-index:9999;
    background:#0d6efd;
    color:white;
    padding:10px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    box-shadow:0 2px 8px rgba(0,0,0,0.3);
}
</style>

</head>
<body>

<div class="topbar">

<form method="POST">

<b>IDPEL (pisah koma)</b>

<textarea name="idpel" placeholder="51540111,51540122,51540133"><?php
if(isset($_POST['idpel'])){
    echo $_POST['idpel'];
}
?></textarea>

<button type="submit">Cari</button>

</form>

</div>

<div id="map"></div>

<button class="gps-btn" onclick="lokasiSaya()">
📍 Lokasi Saya
</button>

<script>
var map = L.map('map').setView([-7.87,111.49],12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19
}).addTo(map);

var bounds = [];
var markerSaya = null;

// marker biru = lokasi saya
var blueIcon = new L.Icon({
    iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
    shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize:[25,41],
    iconAnchor:[12,41],
    popupAnchor:[1,-34],
    shadowSize:[41,41]
});

// marker merah = hasil pencarian
var redIcon = new L.Icon({
    iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize:[25,41],
    iconAnchor:[12,41],
    popupAnchor:[1,-34],
    shadowSize:[41,41]
});

function lokasiSaya(){

    if(navigator.geolocation){

        navigator.geolocation.getCurrentPosition(function(pos){

            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;

            if(markerSaya){
                map.removeLayer(markerSaya);
            }

            markerSaya = L.marker(
                [lat,lng],
                {icon:blueIcon}
            ).addTo(map);

            markerSaya.bindPopup(
                "<b>Lokasi Saya</b><br>" +
                "Lat: " + lat + "<br>" +
                "Lng: " + lng
            ).openPopup();

            map.setView([lat,lng],17);

        }, function(){
            alert("Lokasi tidak bisa diambil");
        });

    }else{
        alert("Browser tidak mendukung GPS");
    }
}
</script>

<?php

if(isset($_POST['idpel']) && $_POST['idpel']!=""){

    $input = $_POST['idpel'];
    $ids = explode(",", $input);
    $clean = array();

    foreach($ids as $id){

        $id = trim($id);

        if($id!=""){
            $clean[] = "'" . $id . "'";
        }
    }

    if(count($clean)>0){

        $in = implode(",", $clean);

        $query = mysql_query("
            SELECT * FROM datapelanggan
            WHERE idpel IN ($in)
        ");

        while($d = mysql_fetch_array($query)){

            if($d['latdij']!="" && $d['longdij']!=""){
?>

<script>

var marker = L.marker([
    <?php echo $d['latdij']; ?>,
    <?php echo $d['longdij']; ?>
],{
    icon:redIcon
}).addTo(map);

marker.bindPopup(
    "<b>DETAIL PELANGGAN</b><br><br>" +

    "<b>IDPEL :</b> <?php echo $d['idpel']; ?><br>" +
    "<b>Nama :</b> <?php echo addslashes($d['nama']); ?><br>" +
    "<b>Alamat :</b> <?php echo addslashes($d['alamat']); ?><br>" +
    "<b>Unit :</b> <?php echo $d['unit']; ?><br>" +
    "<b>Tarif :</b> <?php echo $d['tarif']; ?><br>" +
    "<b>Daya :</b> <?php echo $d['daya']; ?><br>" +
    "<b>Gardu DIJ :</b> <?php echo $d['gardudij']; ?><br>" +
    "<b>Tiang DIJ :</b> <?php echo $d['tiangdij']; ?><br>" +
    "<b>Gardu DIL :</b> <?php echo $d['gardudil']; ?><br>" +
    "<b>Tiang DIL :</b> <?php echo $d['tiangdil']; ?><br>" +
    "<b>Prepaid :</b> <?php echo $d['prepaid']; ?><br>" +
    "<b>No Meter :</b> <?php echo $d['nometer']; ?><br><br>" +

    "<a href='https://www.google.com/maps?q=<?php echo $d['latdij']; ?>,<?php echo $d['longdij']; ?>' target='_blank'>Google Maps DIJ</a><br>" +
    "<a href='https://www.google.com/maps?q=<?php echo $d['latdil']; ?>,<?php echo $d['longdil']; ?>' target='_blank'>Google Maps DIL</a><br>" +
    "<a href='https://www.google.com/maps?q=<?php echo $d['latgardudij']; ?>,<?php echo $d['longgardudij']; ?>' target='_blank'>Google Maps Gardu DIJ</a><br>" +
    "<a href='https://www.google.com/maps?q=<?php echo $d['latgardudil']; ?>,<?php echo $d['longgardudil']; ?>' target='_blank'>Google Maps Gardu DIL</a>"
);

bounds.push([
    <?php echo $d['latdij']; ?>,
    <?php echo $d['longdij']; ?>
]);

</script>

<?php
            }
        }
    }
}
?>

<script>
if(bounds.length > 0){
    map.fitBounds(bounds,{
        padding:[30,30]
    });
}
</script>

</body>
</html>