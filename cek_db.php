<?php
// Skrip Diagnostik & Perbaikan Database Hosting (Kompatibel PHP 5.4 - 8.x)
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$fix_message = '';

// Aksi perbaikan otomatis jika ditekan
if ($action === 'fix_view') {
    $drop = @mysql_query("DROP VIEW IF EXISTS v_datagangguan");
    $create_sql = "CREATE VIEW v_datagangguan AS
    SELECT 
      a.kodegangguan AS kodegangguan,
      a.idgangguan AS idgangguan,
      a.tglgangguan AS tglgangguan,
      a.kat_gangguan AS kat_gangguan,
      a.unit AS unit,
      a.penyulang AS penyulang,
      a.keypointid AS keypointid,
      a.kategorigangguan AS kategorigangguan,
      a.tglmasuk AS tglmasuk,
      a.relay AS relay,
      a.fasa AS fasa,
      a.kv0 AS kv0,
      a.inetral AS inetral,
      a.ir AS ir,
      a.ies AS ies,
      a.it AS it,
      a.cuacakode AS cuacakode,
      a.jeniskode AS jeniskode,
      a.hasiltemuan AS hasiltemuan,
      a.foto1 AS foto1,
      a.foto2 AS foto2,
      a.latlokasi AS latlokasi,
      a.longlokasi AS longlokasi,
      a.hitung AS hitung,
      COALESCE(b.uraian, '') AS uraian,
      COALESCE(c.uraianpenyul, '') AS uraianpenyul,
      TIMESTAMPDIFF(MINUTE, a.tglgangguan, a.tglmasuk) AS selisih_menit,
      COALESCE(d.keterangan, '') AS keterangan,
      COALESCE(e.uraiancuaca, '') AS uraiancuaca,
      COALESCE(f.uraianjenisgangguan, '') AS uraianjenisgangguan
    FROM datagangguan a
    LEFT JOIN kodeunit b ON a.unit = b.kodeunit
    LEFT JOIN kodepenyulang c ON a.penyulang = c.kodepenyul
    LEFT JOIN kodekeypoint d ON a.keypointid = d.idkeypoint
    LEFT JOIN kodecuaca e ON a.cuacakode = e.idcuaca
    LEFT JOIN kodejenisgangguan f ON a.jeniskode = f.idjenisgangguan";

    if (@mysql_query($create_sql)) {
        $fix_message = '<div class="alert alert-success">✅ <strong>BERHASIL!</strong> View <code>v_datagangguan</code> telah berhasil diperbaiki dan hak akses telah diperbarui!</div>';
    } else {
        $fix_message = '<div class="alert alert-danger">❌ <strong>GAGAL:</strong> ' . htmlspecialchars(mysql_error()) . '</div>';
    }
}

$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Database Monitoring PLN</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { font-size: 24px; color: #0056b3; border-bottom: 2px solid #e9ecef; padding-bottom: 12px; margin-top: 0; }
        .card { background: #fafafa; border: 1px solid #e1e4e8; border-radius: 6px; padding: 16px; margin-bottom: 20px; }
        .card h3 { margin-top: 0; font-size: 16px; color: #495057; }
        .badge-ok { background: #28a745; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-fail { background: #dc3545; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-warn { background: #ffc107; color: #212529; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px 12px; border: 1px solid #dee2e6; text-align: left; font-size: 14px; }
        th { background: #f8f9fa; font-weight: 600; }
        .alert { padding: 14px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .btn { display: inline-block; padding: 10px 18px; font-size: 14px; font-weight: bold; color: white; background: #007bff; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-family: Consolas, monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Diagnostik & Status Database Monitoring PLN</h1>
    <?php echo $fix_message; ?>

    <!-- 1. Status Server & PHP -->
    <div class="card">
        <h3>1. Lingkungan Server & Versi PHP</h3>
        <table>
            <tr><th width="30%">Versi PHP Server</th><td><strong><?php echo phpversion(); ?></strong></td></tr>
            <tr><th>Host Akses</th><td><?php echo htmlspecialchars($current_host); ?></td></tr>
            <tr><th>Folder Eksekusi</th><td><code><?php echo htmlspecialchars(__DIR__); ?></code></td></tr>
        </table>
    </div>

    <!-- 2. Status Koneksi Database -->
    <div class="card">
        <h3>2. Status Koneksi Database</h3>
        <table>
            <tr><th width="30%">Host DB</th><td><?php echo htmlspecialchars($host); ?></td></tr>
            <tr><th>Database Terpilih</th><td><strong><?php echo htmlspecialchars($dbase); ?></strong></td></tr>
            <tr><th>Status Koneksi</th><td><span class="badge-ok">Terhubung Berhasil</span></td></tr>
        </table>
    </div>

    <!-- 3. Pengecekan Tabel Fisik -->
    <div class="card">
        <h3>3. Pengecekan Tabel Utama</h3>
        <table>
            <tr>
                <th>Nama Tabel</th>
                <th>Status</th>
                <th>Jumlah Baris (Records)</th>
            </tr>
            <?php
            $tables_to_check = array('datagangguan', 'kodeunit', 'kodepenyulang', 'kodekeypoint', 'kodecuaca', 'kodejenisgangguan');
            foreach ($tables_to_check as $tbl) {
                $check_q = @mysql_query("SELECT COUNT(*) as total FROM `{$tbl}`");
                if ($check_q) {
                    $row = mysql_fetch_assoc($check_q);
                    $count = isset($row['total']) ? (int)$row['total'] : 0;
                    echo "<tr><td><code>{$tbl}</code></td><td><span class=\"badge-ok\">Ditemukan</span></td><td><strong>" . number_format($count) . " data</strong></td></tr>";
                } else {
                    echo "<tr><td><code>{$tbl}</code></td><td><span class=\"badge-fail\">Tidak Ditemukan</span></td><td>Error: " . htmlspecialchars(mysql_error()) . "</td></tr>";
                }
            }
            ?>
        </table>
    </div>

    <!-- 4. Pengecekan View v_datagangguan -->
    <div class="card">
        <h3>4. Pengecekan View <code>v_datagangguan</code></h3>
        <?php
        $view_q = @mysql_query("SELECT COUNT(*) as total FROM v_datagangguan");
        if ($view_q) {
            $row_v = mysql_fetch_assoc($view_q);
            $v_total = isset($row_v['total']) ? (int)$row_v['total'] : 0;
            echo '<div class="alert alert-success">✅ View <code>v_datagangguan</code> terbaca dengan baik! Total: <strong>' . number_format($v_total) . ' baris</strong>.</div>';
        } else {
            $err = mysql_error();
            echo '<div class="alert alert-danger">❌ <strong>Error membaca view <code>v_datagangguan</code>:</strong><br>' . htmlspecialchars($err) . '</div>';
            
            if (strpos($err, '1449') !== false || strpos($err, 'definer') !== false || strpos($err, 'exist') !== false || strpos($err, "doesn't exist") !== false) {
                echo '<div class="alert alert-warning">⚠️ <strong>Penyebab Terdeteksi:</strong> View <code>v_datagangguan</code> di database hosting belum ada atau terkunci oleh akun <em>definer</em> lama (misal <code>root@localhost</code>). Silakan klik tombol perbaikan di bawah ini untuk memperbaikinya secara otomatis.</div>';
            }
            echo '<p><a href="?action=fix_view" class="btn btn-success" onclick="return confirm(\'Jalankan perbaikan otomatis view v_datagangguan?\')">🛠️ Perbaiki View v_datagangguan Sekarang</a></p>';
        }
        ?>
    </div>

    <!-- 5. Pengecekan Data Gangguan Terbaru -->
    <div class="card">
        <h3>5. Rentang Tanggal Data Gangguan (Untuk Filter)</h3>
        <?php
        $date_q = @mysql_query("SELECT MIN(tglgangguan) as min_date, MAX(tglgangguan) as max_date, COUNT(*) as cnt FROM datagangguan");
        if ($date_q && $row_d = mysql_fetch_assoc($date_q)) {
            echo "<table>";
            echo "<tr><th width='30%'>Data Paling Awal</th><td>" . ($row_d['min_date'] ? $row_d['min_date'] : 'Kosong') . "</td></tr>";
            echo "<tr><th>Data Paling Akhir</th><td><strong>" . ($row_d['max_date'] ? $row_d['max_date'] : 'Kosong') . "</strong></td></tr>";
            echo "<tr><th>Total Rekap Data</th><td>" . number_format((int)$row_d['cnt']) . " records</td></tr>";
            echo "</table>";

            // Cek data hari ini (CURDATE)
            $today_q = @mysql_query("SELECT COUNT(*) as today_cnt FROM datagangguan WHERE DATE(tglgangguan) = CURDATE()");
            $today_cnt = ($today_q && $r_td = mysql_fetch_assoc($today_q)) ? (int)$r_td['today_cnt'] : 0;
            
            if ($today_cnt == 0) {
                echo '<p style="margin-top:12px; font-size:13px; color:#6c757d;">ℹ️ <em>Catatan: Hari ini (<code>CURDATE()</code>) tercatat <strong>0 gangguan</strong>. Oleh karena itu pada halaman <strong>Monitoring Harian</strong>, tabel akan tampak kosong jika belum memilih rentang tanggal dan menekan tombol Filter.</em></p>';
            }
        }
        ?>
    </div>

    <div style="text-align:center; margin-top:20px;">
        <a href="index.php" class="btn">Kembali ke Dashboard Utama</a>
    </div>
</div>
</body>
</html>
