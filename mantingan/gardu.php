<?php
include "koneksi2.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koordinat Gardu</title>

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

        .detail-card{
            margin-top:20px;
            background:#ffffff;
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

        .btn{
            padding:12px 18px;
            text-decoration:none;
            border-radius:8px;
            color:white;
            font-size:14px;
            display:inline-block;
        }

        .btn-map{
            background:#0d6efd;
        }

        .btn-reset{
            background:#dc3545;
        }

        @media(max-width:768px){
            .button-group{
                flex-direction:column;
            }

            .btn{
                width:100%;
                text-align:center;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">

        <h2>⚡ Cari Koordinat Gardu</h2>

        <form method="POST" id="formCari">

            <select name="namatrafo" class="select2" style="width:100%;">
                <option value="">Pilih Nama Trafo</option>

                <?php
                $query = mysql_query("SELECT namatrafo FROM datagardu ORDER BY namatrafo ASC",$konek);

                while($data = mysql_fetch_array($query)){
                    $selected = "";
                    if(isset($_POST['namatrafo']) && $_POST['namatrafo'] == $data['namatrafo']){
                        $selected = "selected";
                    }

                    echo "<option value='".$data['namatrafo']."' ".$selected.">
                            ".$data['namatrafo']."
                          </option>";
                }
                ?>
            </select>

        </form>

        <?php
        if(isset($_POST['namatrafo']) && $_POST['namatrafo'] != ""){

            $namatrafo = $_POST['namatrafo'];

            $detail = mysql_query("SELECT * FROM datagardu WHERE namatrafo='$namatrafo'",$konek);
            $d = mysql_fetch_array($detail);

            $lat = $d['koordinatlat'];
            $long = $d['koordinatlong'];
        ?>

        <div class="detail-card">

            <div class="detail-item">
                <span class="label">Nama Trafo:</span><br>
                <?php echo $d['namatrafo']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Kode Penyulang:</span><br>
                <?php echo $d['kodepenyul']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Jenis:</span><br>
                <?php echo $d['jenis']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Alamat:</span><br>
                <?php echo $d['alamat']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Merk:</span><br>
                <?php echo $d['merk']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Kapasitas Trafo:</span><br>
                <?php echo $d['kaptrafo']; ?>
            </div>

            <div class="detail-item">
                <span class="label">Phasa:</span><br>
                <?php echo $d['phasa']; ?>
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
                    📍 Buka di Google Maps
                </a>

                <a class="btn btn-reset" href="gardu.php">
                    🔄 Reset Pencarian
                </a>
            </div>

        </div>

        <?php } ?>

    </div>

</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Cari Nama Trafo"
    });

    $('.select2').on('change', function() {
        $('#formCari').submit();
    });
});
</script>

</body>
</html>