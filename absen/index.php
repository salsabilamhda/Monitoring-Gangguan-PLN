<?php 
include 'koneksi.php';

date_default_timezone_set("Asia/Jakarta");
$today = date("Y-m-d");

// KOORDINAT KANTOR
$kantor_lat = -7.845002622674241;
$kantor_lon = 111.47715155148373;

function hitungJarak($lat1, $lon1, $lat2, $lon2) {
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
          cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
          cos(deg2rad($theta));
  $dist = acos($dist);
  return $dist * 6371000;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ABSENSI</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
body { background:#e5e7eb; font-family:Segoe UI; }
.card { border-radius:15px; }
.btn { font-size:18px; font-weight:bold; }

#loading {
display:none; position:fixed; top:0; left:0;
width:100%; height:100%; background:rgba(0,0,0,0.7);
z-index:9999; color:white; text-align:center; padding-top:50%;
}

img:hover{ transform:scale(1.05); transition:0.2s; cursor:pointer; }
</style>
</head>

<body>

<div id="loading">⏳ MENYIMPAN...</div>

<div class="container py-3">

<div class="card p-3 mb-3">
<div class="text-center fw-bold mb-2">ABSENSI HARIAN</div>

<input type="text" id="koordinat" class="form-control mb-2 text-center" readonly>

<input type="file" id="foto" accept="image/*" capture="user" style="display:none;">

<div id="box_kegiatan" style="display:none;">
<textarea id="kegiatan" class="form-control mb-2" placeholder="KEGIATAN" style="text-transform:uppercase;"></textarea>
<button class="btn btn-primary w-100 mb-2" onclick="kirimPulang()">KIRIM PULANG</button>
</div>

<div class="row g-2">
<div class="col-6">
<button class="btn btn-success w-100" onclick="klikMasuk()">MASUK</button>
</div>
<div class="col-6">
<button class="btn btn-danger w-100" onclick="klikPulang()">PULANG</button>
</div>
</div>
</div>

<!-- TABEL -->
<div class="card p-3">
<div class="text-center fw-bold mb-2">RIWAYAT</div>

<div class="table-responsive">
<table class="table table-bordered text-center">
<tr>
<th>WAKTU</th>
<th>STATUS</th>
<th>KEGIATAN</th>
<th>KOORDINAT</th>
<th>FOTO</th>
<th>JARAK</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM absensi WHERE DATE(waktu)='$today' ORDER BY waktu DESC");

while($d = mysqli_fetch_assoc($q)){

$jarak = round(hitungJarak(
$d['lat'],$d['lon'],
$kantor_lat,$kantor_lon
));

echo "<tr>
<td>".date('H:i:s',strtotime($d['waktu']))."</td>
<td>".$d['status']."</td>
<td>".$d['kegiatan']."</td>
<td>".$d['lat'].", ".$d['lon']."</td>

<td>
<img src='foto/".$d['foto']."' width='60'
onclick=\"lihatFoto('foto/".$d['foto']."')\">
</td>

<td>".$jarak." m</td>
</tr>";
}
?>
</table>
</div>

</div>

</div>

<!-- MODAL FOTO -->
<div class="modal fade" id="modalFoto">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content bg-dark">
<div class="modal-body text-center">
<img id="imgModal" style="width:100%;">
</div>
</div>
</div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

// koordinat kantor
var kantor_lat = -7.845002622674241;
var kantor_lon = 111.47715155148373;

// GPS tampil
navigator.geolocation.getCurrentPosition(function(pos){
  document.getElementById("koordinat").value =
  pos.coords.latitude + "," + pos.coords.longitude;
});

// loading
function showLoading(){ document.getElementById("loading").style.display="block"; }
function hideLoading(){ document.getElementById("loading").style.display="none"; }

// AUTO UPPERCASE (REAL)
document.getElementById("kegiatan").addEventListener("input", function(){
  this.value = this.value.toUpperCase();
});

// MASUK
function klikMasuk(){
  let f=document.getElementById("foto");
  f.value="";
  f.onchange=function(){
    kirimData("MASUK","MASUK");
  };
  f.click();
}

// PULANG
function klikPulang(){
  document.getElementById("box_kegiatan").style.display="block";
}

// kirim pulang
function kirimPulang(){
  let kegiatan=document.getElementById("kegiatan").value.trim();
  if(kegiatan==""){ alert("ISI KEGIATAN"); return; }

  let f=document.getElementById("foto");
  f.value="";
  f.onchange=function(){
    kirimData("PULANG",kegiatan.toUpperCase());
  };
  f.click();
}

// kirim data
function kirimData(status,kegiatan){

  let file=document.getElementById("foto").files[0];
  if(!file) return;

  showLoading();

  navigator.geolocation.getCurrentPosition(function(pos){

    let lat=pos.coords.latitude;
    let lon=pos.coords.longitude;

    let reader=new FileReader();
    reader.onload=function(e){

      let img=new Image();
      img.src=e.target.result;

      img.onload=function(){

        let canvas=document.createElement("canvas");
        let ctx=canvas.getContext("2d");

        let maxWidth=320;
        let scale=maxWidth/img.width;

        canvas.width=maxWidth;
        canvas.height=img.height*scale;

        ctx.drawImage(img,0,0,canvas.width,canvas.height);

        let quality=0.7;
        let target=50*1024;

        function loop(){
          let data=canvas.toDataURL("image/jpeg",quality);
          let size=(data.length*3)/4;

          if(size>target && quality>0.1){
            quality-=0.1;
            loop();
          }else{

            let xhr=new XMLHttpRequest();
            xhr.open("POST","simpan.php",true);
            xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

            xhr.onload=function(){
              hideLoading();
              alert(xhr.responseText);
              location.reload();
            };

            xhr.send(
              "status="+status+
              "&kegiatan="+encodeURIComponent(kegiatan)+
              "&lat="+lat+
              "&lon="+lon+
              "&kantor_lat="+kantor_lat+
              "&kantor_lon="+kantor_lon+
              "&foto="+encodeURIComponent(data)
            );

          }
        }

        loop();

      };

    };

    reader.readAsDataURL(file);

  });

}

// modal foto
function lihatFoto(src){
  document.getElementById("imgModal").src=src;
  new bootstrap.Modal(document.getElementById('modalFoto')).show();
}

</script>

</body>
</html>