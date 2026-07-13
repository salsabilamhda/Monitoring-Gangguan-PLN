<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koordinat Keypoint</title>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
            max-width:900px;
            margin:auto;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
        }

        h2{
            text-align:center;
        }

        .detail-card{
            margin-top:20px;
            padding:20px;
            background:white;
            border-radius:15px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
        }

        .detail-item{
            padding:10px 0;
            border-bottom:1px solid #eee;
        }

        .label{
            font-weight:bold;
        }

        .button-group{
            margin-top:20px;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .btn{
            padding:12px 18px;
            text-decoration:none;
            border-radius:8px;
            color:white;
        }

        .btn-map{
            background:#0d6efd;
        }

        .btn-reset{
            background:#dc3545;
        }
    </style>
</head>
<body>

<div class="container">

<div class="card">

<h2>🗺 Cari Keypoint</h2>

<form method="POST" id="formCari">

<select name="nama" class="select2" style="width:100%;">
    <option value="">Pilih Nama Keypoint</option>

    <?php
    $query = mysql_query("SELECT nama FROM datakeypoint ORDER BY nama ASC",$konek);

    while($data = mysql_fetch_array($query)){

        $selected = "";

        if(isset($_POST['nama']) && $_POST['nama'] == $data['nama']){
            $selected = "selected";
        }

        echo "<option value='".$data['nama']."' ".$selected.">
                ".$data['nama']."
              </option>";
    }
    ?>
</select>

</form>

<?php
if(isset($_POST['nama']) && $_POST['nama'] != ""){

    $nama = $_POST['nama'];

    $detail = mysql_query("SELECT * FROM datakeypoint WHERE nama='$nama'",$konek);
    $d = mysql_fetch_array($detail);

    $lat = $d['latx'];
    $long = $d['longx'];
?>

<div class="detail-card">

    <div class="detail-item">
        <span class="label">Penyulang:</span><br>
        <?php echo $d['penyulang']; ?>
    </div>

    <div class="detail-item">
        <span class="label">Jenis:</span><br>
        <?php echo $d['jenis']; ?>
    </div>

    <div class="detail-item">
        <span class="label">Nama:</span><br>
        <?php echo $d['nama']; ?>
    </div>

    <div class="detail-item">
        <span class="label">Unit:</span><br>
        <?php echo $d['unit']; ?>
    </div>

    <div class="detail-item">
        <span class="label">Latitude:</span><br>
        <?php echo $lat; ?>
    </div>

    <div class="detail-item">
        <span class="label">Longitude:</span><br>
        <?php echo $long; ?>
    </div>

    <div class="button-group">

        <a class="btn btn-map"
           href="https://www.google.com/maps?q=<?php echo $lat; ?>,<?php echo $long; ?>"
           target="_blank">
            📍 Buka Google Maps
        </a>

        <a class="btn btn-reset" href="keypoint.php">
            🔄 Reset
        </a>

    </div>

</div>

<?php } ?>

</div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Cari Nama Keypoint"
    });

    $('.select2').on('change', function() {
        $('#formCari').submit();
    });
});
</script>

</body>
</html>