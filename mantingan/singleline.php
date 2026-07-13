<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Single Line Diagram</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial;
            background:#f4f6f9;
        }

        .topbar{
            background:white;
            padding:15px;
            position:sticky;
            top:0;
            z-index:999;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }

        #map{
            width:100%;
            height:calc(100vh - 80px);
        }

        .floating{
            position:absolute;
            z-index:9999;
            background:white;
            padding:10px;
            border-radius:10px;
            box-shadow:0 0 8px rgba(0,0,0,0.2);
            font-size:12px;
            width:180px;
            max-width:calc(100vw - 20px);
        }

        .gps-box{
            right:10px;
            bottom:10px;
        }

        .filter-box{
            left:10px;
            bottom:10px;
        }

        .input{
            width:100%;
            padding:8px;
            margin-bottom:8px;
            font-size:12px;
            border:1px solid #ccc;
            border-radius:5px;
            box-sizing:border-box;
        }

        .btn{
            width:100%;
            padding:8px;
            border:none;
            background:#0d6efd;
            color:white;
            border-radius:5px;
            font-size:12px;
            cursor:pointer;
            box-sizing:border-box;
        }

        .leaflet-tooltip{
            background:white;
            border:1px solid #333;
            font-size:11px;
            padding:3px 6px;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="topbar">

<form method="POST" id="formCari">

<select name="penyul" class="select2" style="width:100%;">
    <option value="">Pilih Penyulang</option>

    <?php
    $query=mysql_query("
        SELECT DISTINCT penyul 
        FROM datasld 
        ORDER BY penyul ASC
    ",$konek);

    while($data=mysql_fetch_array($query)){

        $selected="";

        if(isset($_POST['penyul']) && $_POST['penyul']==$data['penyul']){
            $selected="selected";
        }

        echo "<option value='".$data['penyul']."' ".$selected.">
                ".$data['penyul']."
              </option>";
    }
    ?>
</select>

</form>

</div>

<div id="map"></div>

<?php if(isset($_POST['penyul']) && $_POST['penyul']!=""){ ?>

<div class="floating gps-box">

<button class="btn" onclick="lokasiSaya()">
📍 Lokasi Saya
</button>

<br><br>

<input type="text" 
       id="lat" 
       class="input" 
       placeholder="Latitude">

<input type="text" 
       id="lng" 
       class="input" 
       placeholder="Longitude">

<button class="btn" onclick="goKoordinat()">
Go Koordinat
</button>

</div>

<div class="floating filter-box">

<b>Filter Marker</b>
<br><br>

<label>
<input type="checkbox" class="filterKet" value="GI" checked> GI
</label><br>

<label>
<input type="checkbox" class="filterKet" value="TIANGTM" checked> TM
</label><br>

<label>
<input type="checkbox" class="filterKet" value="TIANGTR" checked> TR
</label><br>

<label>
<input type="checkbox" class="filterKet" value="GARDU" checked> GARDU
</label><br>

<label>
<input type="checkbox" class="filterKet" value="KEYPOINT" checked> KEYPOINT
</label>

</div>

<?php } ?>

<script>
$('.select2').select2();

$('.select2').on('change',function(){
    $('#formCari').submit();
});

var map=L.map('map').setView([-7.87,111.49],12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19
}).addTo(map);

var markers=[];
var bounds=[];
</script>

<?php
if(isset($_POST['penyul']) && $_POST['penyul']!=""){

$penyul=$_POST['penyul'];

$q=mysql_query("
    SELECT * 
    FROM datasld 
    WHERE penyul='$penyul'
",$konek);

while($d=mysql_fetch_array($q)){

$ket=strtoupper($d['ket']);
$icon="";

if($ket=="GI"){
    $icon="gipakai.png";
}elseif($ket=="TIANGTM"){
    $icon="tiangtmpakai.png";
}elseif($ket=="TIANGTR"){
    $icon="tiangtrpakai.png";
}elseif($ket=="GARDU"){
    $icon="gardupakai.png";
}elseif($ket=="KEYPOINT"){
    $icon="keypointpakai.png";
}
?>

<script>
var iconCustom=L.icon({
    iconUrl:'<?php echo $icon; ?>',
    iconSize:[16,16]
});

var marker=L.marker(
    [
        <?php echo $d['latx']; ?>,
        <?php echo $d['laty']; ?>
    ],
    {
        icon:iconCustom
    }
).addTo(map);

marker.myKet="<?php echo $ket; ?>";
marker.namaLabel=<?php echo json_encode($d['nama']); ?>;

if(
    marker.myKet=="GI" ||
    marker.myKet=="GARDU" ||
    marker.myKet=="KEYPOINT"
){
    marker.bindTooltip(
        marker.namaLabel,
        {
            permanent:true,
            direction:'right'
        }
    );
}

marker.bindPopup(
    "<b>"+marker.namaLabel+"</b><br>" +
    "Jenis: <?php echo $ket; ?><br>" +
    "<a target='_blank' href='https://www.google.com/maps?q=<?php echo $d['latx']; ?>,<?php echo $d['laty']; ?>'>Google Maps</a>"
);

markers.push(marker);

bounds.push([
    <?php echo $d['latx']; ?>,
    <?php echo $d['laty']; ?>
]);
</script>

<?php
}
}
?>

<script>
if(bounds.length>0){
    map.fitBounds(bounds);
}

function lokasiSaya(){

    navigator.geolocation.getCurrentPosition(function(pos){

        var lat=pos.coords.latitude;
        var lng=pos.coords.longitude;

        map.setView([lat,lng],16);

        L.marker([lat,lng]).addTo(map)
        .bindPopup("Lokasi Saya")
        .openPopup();

    });

}

function goKoordinat(){

    var lat=$('#lat').val();
    var lng=$('#lng').val();

    if(lat!="" && lng!=""){

        map.setView([lat,lng],16);

        L.marker([lat,lng]).addTo(map)
        .bindPopup("Koordinat Manual")
        .openPopup();
    }
}

$('.filterKet').on('change',function(){

    var aktif=[];

    $('.filterKet:checked').each(function(){
        aktif.push($(this).val());
    });

    markers.forEach(function(m){

        if(aktif.includes(m.myKet)){
            if(!map.hasLayer(m)){
                map.addLayer(m);
            }
        }else{
            if(map.hasLayer(m)){
                map.removeLayer(m);
            }
        }

    });

});

map.on('zoomend',function(){

    var zoom=map.getZoom();

    markers.forEach(function(m){

        if(
            m.myKet=="TIANGTM" ||
            m.myKet=="TIANGTR"
        ){

            if(zoom>=17){

                if(!m.getTooltip()){
                    m.bindTooltip(
                        m.namaLabel,
                        {
                            permanent:true,
                            direction:'right'
                        }
                    );
                }

            }else{
                m.unbindTooltip();
            }

        }

    });

});
</script>

</body>
</html>