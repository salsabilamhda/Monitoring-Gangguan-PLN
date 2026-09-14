<?php
// ========================================================
// SKRIP PENGISIAN DATA MASTER MONITORING PLN
// Mengisi data: kodeunit, kodepenyulang, kodecuaca, kodejenisgangguan, kodekeypoint
// ========================================================

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/connect.php';

$message = '';
$executed = false;
$stats = array();

// Fungsi untuk membaca dan memisahkan query SQL
function executeSqlFile($filePath) {
    if (!file_exists($filePath)) {
        return array('success' => false, 'message' => "File $filePath tidak ditemukan!");
    }

    $content = file_get_contents($filePath);
    // Hapus komentar baris
    $lines = explode("\n", $content);
    $cleanSql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
            continue;
        }
        $cleanSql .= $line . "\n";
    }

    // Pisahkan query berdasarkan titik koma di akhir baris
    $queries = preg_split('/;\s*[\r\n]+/', $cleanSql);
    $successCount = 0;
    $errors = array();

    foreach ($queries as $q) {
        $q = trim($q);
        if (!empty($q)) {
            $res = @mysql_query($q);
            if ($res) {
                $successCount++;
            } else {
                $errors[] = mysql_error();
            }
        }
    }

    return array(
        'success' => count($errors) === 0,
        'query_count' => $successCount,
        'errors' => $errors
    );
}

// Cek apakah tombol dieksekusi atau parameter ?run=1 diberikan
if (isset($_POST['run_seed']) || isset($_GET['run'])) {
    $sqlFile = __DIR__ . '/insert_datamaster.sql';
    
    // Jika dicentang opsi reset/bersihkan kodekeypoint lama agar urutan ID sinkron
    if (isset($_POST['reset_keypoint']) || isset($_GET['reset'])) {
        @mysql_query("TRUNCATE TABLE `kodekeypoint`");
    }
    
    // Pastikan tabel dasar ada terlebih dahulu jika belum dibuat
    @mysql_query("CREATE TABLE IF NOT EXISTS `kodeunit` (
      `kodeunit` int(11) NOT NULL,
      `uraian` varchar(100) NOT NULL,
      PRIMARY KEY (`kodeunit`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    @mysql_query("CREATE TABLE IF NOT EXISTS `kodepenyulang` (
      `kodepenyul` varchar(10) NOT NULL,
      `uraianpenyul` varchar(50) NOT NULL,
      PRIMARY KEY (`kodepenyul`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    @mysql_query("CREATE TABLE IF NOT EXISTS `kodecuaca` (
      `idcuaca` int(11) NOT NULL AUTO_INCREMENT,
      `uraiancuaca` varchar(100) NOT NULL,
      PRIMARY KEY (`idcuaca`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    @mysql_query("CREATE TABLE IF NOT EXISTS `kodejenisgangguan` (
      `idjenisgangguan` int(11) NOT NULL AUTO_INCREMENT,
      `uraianjenisgangguan` varchar(100) NOT NULL,
      PRIMARY KEY (`idjenisgangguan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    @mysql_query("CREATE TABLE IF NOT EXISTS `kodekeypoint` (
      `kodepenyul` varchar(200) NOT NULL,
      `jenis` varchar(200) NOT NULL,
      `keterangan` varchar(300) NOT NULL,
      `unit` varchar(25) NOT NULL,
      `zona` varchar(25) NOT NULL,
      `latitud` varchar(150) NOT NULL,
      `longitud` varchar(150) NOT NULL,
      `id_keypint` int(11) NOT NULL DEFAULT 0,
      `idkeypoint` int(11) NOT NULL AUTO_INCREMENT,
      PRIMARY KEY (`idkeypoint`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

    $result = executeSqlFile($sqlFile);
    $executed = true;

    if ($result['success']) {
        $message = '<div class="alert alert-success">
            <strong> Berhasil!</strong> Seluruh data master telah berhasil dipulihkan dengan pemetaan ID asli.
        </div>';
    } else {
        $message = '<div class="alert alert-warning">
            <strong> Selesai dengan catatan:</strong> ' . $result['query_count'] . ' query berhasil dijalankan.<br>
            <small>' . implode('<br>', array_slice($result['errors'], 0, 5)) . '</small>
        </div>';
    }
}

// Ambil jumlah baris data saat ini
$tables = array(
    'kodeunit' => array('nama' => 'Master Unit Kerja', 'target' => 5),
    'kodepenyulang' => array('nama' => 'Master Penyulang', 'target' => 49),
    'kodecuaca' => array('nama' => 'Master Cuaca', 'target' => 7),
    'kodejenisgangguan' => array('nama' => 'Master Jenis Gangguan', 'target' => 24),
    'kodekeypoint' => array('nama' => 'Master Keypoint (Gardu/LBS/REC/FCO - Gabungan Master + Excel Baru)', 'target' => 1079)
);

foreach ($tables as $tbl => $info) {
    $q = @mysql_query("SELECT COUNT(*) as total FROM `{$tbl}`");
    $cnt = 0;
    if ($q && $r = mysql_fetch_assoc($q)) {
        $cnt = (int)$r['total'];
    }
    $stats[$tbl] = $cnt;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Data Master Database - Monitoring Gangguan PLN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f4f8;
            color: #2c3e50;
            margin: 0;
            padding: 30px 15px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #0056b3;
            font-size: 24px;
            margin: 0 0 8px 0;
        }
        .header p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 15px;
            line-height: 1.5;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
            border: 1px solid #ffecb5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #def7ec;
            color: #03543f;
        }
        .badge-empty {
            background: #fde8e8;
            color: #9b1c1c;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            transition: all 0.2s;
        }
        .btn:hover {
            background: #0056b3;
            box-shadow: 0 6px 16px rgba(0,123,255,0.4);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #64748b;
            color: #fff;
            padding: 10px 18px;
            font-size: 13px;
            box-shadow: none;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        .actions {
            text-align: center;
            margin: 25px 0 15px 0;
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }
        .footer-links a {
            color: #007bff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>⚡ Pengisian Data Master Monitoring PLN</h1>
        <p>Alat pemulihan otomatis tabel: <strong>kodecuaca, kodejenisgangguan, kodekeypoint, kodepenyulang, kodeunit</strong></p>
    </div>

    <?php echo $message; ?>

    <h3>Status Data Tabel Master Saat Ini</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Tabel</th>
                <th>Keterangan</th>
                <th>Jumlah Data Saat Ini</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $tbl => $info): 
                $count = isset($stats[$tbl]) ? $stats[$tbl] : 0;
                $isFilled = $count > 0;
            ?>
            <tr>
                <td><code><?php echo $tbl; ?></code></td>
                <td><?php echo $info['nama']; ?></td>
                <td>
                    <strong><?php echo number_format($count); ?></strong> data
                    <?php if ($count > 0 && $count < $info['target']): ?>
                        <small style="color:#d97706;">(dari ~<?php echo $info['target']; ?>)</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isFilled): ?>
                        <span class="badge badge-success">✓ Terisi</span>
                    <?php else: ?>
                        <span class="badge badge-empty">⚠ Kosong</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="actions">
        <form method="POST" action="">
            <div style="margin-bottom: 16px; text-align: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <label style="font-size: 14px; color: #1e293b; cursor: pointer; display: inline-block;">
                    <input type="checkbox" name="reset_keypoint" value="1" checked style="transform: scale(1.2); margin-right: 8px;">
                    <strong>Reset & Sinkronkan Ulang Urutan ID Keypoint (Disarankan)</strong><br>
                    <small style="color: #64748b;">Mereset urutan ID keypoint kembali ke ID asli sehingga nama recloser di Dashboard Visualisasi kembali 100% cocok dengan laporan Excel.</small>
                </label>
            </div>
            <button type="submit" name="run_seed" class="btn" onclick="return confirm('Jalankan sinkronisasi data master sekarang?');">
                🚀 Pulihkan & Sinkronkan Semua Data Master
            </button>
        </form>
    </div>

    <div class="footer-links">
        <a href="cek_db.php">🔍 Cek Diagnostik Database</a>
        <a href="masterpenyulang.php">📋 Master Penyulang</a>
        <a href="masterunit.php">🏢 Master Unit</a>
        <a href="masterkeypoint.php">📍 Master Keypoint</a>
        <a href="index.php">🏠 Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
