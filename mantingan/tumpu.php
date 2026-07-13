<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koordinat Titik Tumpu</title>

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
            font-family:Arial, sans-serif;
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
            margin-top:0;
            text-align:center;
            color:#333;
        }

        .input{
            width:100%;
            padding:12px;
            margin-top:15px;
            border:1px solid #ddd;
            border-radius:8px;
            font-size:14px;
        }

        .btn{
            width:100%;
            padding:12px;
            margin-top:15px;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:14px;
            color:white;
        }

        .btn-search{
            background:#0d6efd;
        }

        .detail-card{
            margin-top:20px;
            background:white;
            border-radius:15px;
            padding:20px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
        }

        .detail-item{
            padding:10px 0;
            border-bottom:1px solid #eee;
        }

        .label{
            font-weight:bold;
            color:#555;
        }

        .button-group{
            margin-top:20px;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .btn-map{
            background:#28a745;
            text-decoration:none;
            padding:12px 18px;
            border-radius:8px;
            color:white;
        }

        .btn-reset{
            background:#dc3545;
            text-decoration:none;
            padding:12px 18px;
            border-radius:8px;
            color:white;
        }

        @media(max-width:768px){
            .button-group{
                flex-direction:column;
            }

            .btn-map,
            .btn-reset{
                width:100%;
                text-align:center;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">

        <h2>📍 Cari Titik Tumpu</h2>

        <form method="POST">

 <select name="kodegardu" class="select2" style="width:100%;" onchange="this.form.submit()">
                <option value="">Pilih Kode Gardu</option>

                <?php
                $query = mysql_query("SELECT DISTINCT(namatrafo) as kodegardu FROM datagardu ORDER BY namatrafo ASC",$konek);

                while($data = mysql_fetch_array($query)){

                    $selected = "";
                    if(isset($_POST['kodegardu']) && $_POST['kodegardu'] == $data['kodegardu']){
                        $selected = "selected";
                    }

                    echo "<option value='".$data['kodegardu']."' ".$selected.">
                            ".$data['kodegardu']."
                          </option>";
                }
                ?>
            </select>

          <select name="notiang" class="select2" style="width:100%; margin-top:15px;">
    <option value="">Pilih Nomor Tiang</option>

    <?php
    if(isset($_POST['kodegardu']) && $_POST['kodegardu']!=""){

        $kodegardu=$_POST['kodegardu'];

        $qtiang=mysql_query("
            SELECT DISTINCT(notiang)
            FROM datatitiktumpu
            WHERE kodegardu='$kodegardu'
            ORDER BY notiang ASC
        ");

        while($t=mysql_fetch_array($qtiang)){

            $selected="";
            if(isset($_POST['notiang']) && $_POST['notiang']==$t['notiang']){
                $selected="selected";
            }

            echo "<option value='".$t['notiang']."' $selected>
                    ".$t['notiang']."
                  </option>";
        }
    }
    ?>
</select>

            <button type="submit" class="btn btn-search">
                🔍 Cari Data
            </button>

        </form>

        <?php
        if(isset($_POST['kodegardu']) && isset($_POST['notiang']) &&
           $_POST['kodegardu'] != "" && $_POST['notiang'] != ""){

            $kodegardu = $_POST['kodegardu'];
            $notiang = strtoupper($_POST['notiang']);
            
            $detail = mysql_query("SELECT * FROM datatitiktumpu WHERE kodegardu='$kodegardu' AND notiang = '$notiang'",$konek);

            while($d = mysql_fetch_array($detail)){

                $lat = $d['latnya'];
                $long = $d['longnya'];
        ?>

        <div class="detail-card">

            <div class="detail-item">
                <span class="label">Kode Gardu:</span><br>
                <?php echo $d['kodegardu']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Nomor Tiang:</span><br>
                <?php echo $d['notiang']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Penyulang:</span><br>
                <?php echo $d['penyul']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Kode Tiang:</span><br>
                <?php echo $d['kodetiang']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Tinggi Tiang:</span><br>
                <?php echo $d['tinggitiang']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Bahan Tiang:</span><br>
                <?php echo $d['bahantiang']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Kekuatan Tiang:</span><br>
                <?php echo $d['kekuatantiang']; ?>
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

                <a class="btn-map"
                   href="https://www.google.com/maps?q=<?php echo $lat; ?>,<?php echo $long; ?>"
                   target="_blank">
                    📍 Buka Google Maps
                </a>

                <a class="btn-reset" href="tumpu.php">
                    🔄 Reset Pencarian
                </a>

            </div>

        </div>

        <?php
            }

            $jumlah = mysql_num_rows($detail);

            if($jumlah == 0){
                echo "<div class='detail-card'>Data tidak ditemukan.</div>";
            }

        }
        ?>

    </div>

</div>

<script>
$('select[name="kodegardu"]').select2({
    placeholder: "Pilih Kode Gardu"
});

$('select[name="notiang"]').select2({
    placeholder: "Pilih Nomor Tiang"
});
</script>

</body>
</html>