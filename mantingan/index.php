<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Koordinat</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f4f6f9;
        }

        .header{
            background:#0d6efd;
            color:white;
            padding:15px 20px;
            font-size:20px;
            font-weight:bold;
            text-align:center;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }

        .container{
            padding:15px;
        }

        .menu-wrapper{
            overflow-x:auto;
            white-space:nowrap;
            margin-bottom:15px;
            padding-bottom:5px;
        }

        .menu{
            display:inline-flex;
            gap:10px;
        }

        .menu a{
            text-decoration:none;
            padding:12px 18px;
            background:white;
            color:#333;
            border-radius:10px;
            border:1px solid #ddd;
            font-size:14px;
            transition:0.3s;
            box-shadow:0 2px 8px rgba(0,0,0,0.05);
        }

        .menu a:hover{
            background:#0d6efd;
            color:white;
        }

        .content{
            background:white;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
        }

        iframe{
            width:100%;
            height:75vh;
            border:none;
        }

        @media(max-width:768px){
            .header{
                font-size:18px;
                padding:12px;
            }

            .menu a{
                padding:10px 15px;
                font-size:13px;
            }

            iframe{
                height:80vh;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="menu-wrapper">
    <div class="menu">
        <a href="gardu.php" target="contentFrame">⚡ Koordinat Gardu</a>
        <a href="tumpu.php" target="contentFrame">📍 Titik Tumpu</a>
        <a href="pelanggan.php" target="contentFrame">👤 Koordinat Pelanggan</a>
        <a href="banyak_pelanggan.php" target="contentFrame">👥 Koordinat Banyak Pelanggan</a>
        <a href="nometer.php" target="contentFrame">🔢 Cari No Meter</a>
        <a href="keypoint.php" target="contentFrame">🗺 Keypoint</a>
        <a href="singleline.php" target="contentFrame">🔌 Single Line Diagram</a>
        <a href="terdekat1.php" target="contentFrame">📌 Koordinat Terdekat</a>
     <!--   <a href="gembok.php" target="contentFrame">🔒 Gembok</a>-->
        
    </div>
</div>

    <div class="content">
        <iframe name="contentFrame"></iframe>
    </div>

</div>

</body>
</html>