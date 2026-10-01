<?php
// Skrip Diagnostik & Perbaikan Database Hosting (Kompatibel PHP 5.4 - 8.x)
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$fix_message = '';

// Aksi perbaikan otomatis jika ditekan
if ($action === 'fix_view' || $action === 'fix_all_views') {
    $errors = array();

    // 1. Perbaiki v_datagangguan
    @mysql_query("DROP VIEW IF EXISTS v_datagangguan");
    $sql_gangguan = "CREATE VIEW v_datagangguan AS
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
    if (!@mysql_query($sql_gangguan)) {
        $errors[] = "v_datagangguan: " . mysql_error();
    }

    // 2. Perbaiki v_penyulang
    @mysql_query("DROP VIEW IF EXISTS v_penyulang");
    $sql_penyulang = "CREATE VIEW v_penyulang AS 
    SELECT DISTINCT 
      a.kodepenyul AS kodepenyul, 
      a.unit AS unit, 
      COALESCE(b.uraian, a.unit) AS uraian, 
      COALESCE(c.uraianpenyul, a.kodepenyul) AS uraianpenyul 
    FROM kodekeypoint a 
    LEFT JOIN kodeunit b ON a.unit = b.kodeunit 
    LEFT JOIN kodepenyulang c ON a.kodepenyul = c.kodepenyul 
    WHERE a.kodepenyul != '' AND a.kodepenyul IS NOT NULL";
    if (!@mysql_query($sql_penyulang)) {
        $errors[] = "v_penyulang: " . mysql_error();
    }

    // 3. Perbaiki v_keypoint
    @mysql_query("DROP VIEW IF EXISTS v_keypoint");
    $sql_keypoint = "CREATE VIEW v_keypoint AS 
    SELECT 
      a.idkeypoint AS idkeypoint, 
      a.kodepenyul AS kodepenyul, 
      a.jenis AS jenis, 
      a.keterangan AS keterangan, 
      a.unit AS unit, 
      a.zona AS zona, 
      a.latitud AS latitud, 
      a.longitud AS longitud, 
      a.id_keypint AS id_keypint, 
      COALESCE(b.uraianpenyul, a.kodepenyul) AS uraianpenyul, 
      COALESCE(c.uraian, a.unit) AS uraian 
    FROM kodekeypoint a 
    LEFT JOIN kodepenyulang b ON a.kodepenyul = b.kodepenyul 
    LEFT JOIN kodeunit c ON a.unit = c.kodeunit";
    if (!@mysql_query($sql_keypoint)) {
        $errors[] = "v_keypoint: " . mysql_error();
    }

    if (empty($errors)) {
        $fix_message = '<div class="alert alert-success">✅ <strong>BERHASIL!</strong> Semua View (<code>v_datagangguan</code>, <code>v_penyulang</code>, <code>v_keypoint</code>) telah berhasil diperbaiki tanpa definer dan hak akses aktif di hosting!</div>';
    } else {
        $fix_message = '<div class="alert alert-danger">❌ <strong>GAGAL:</strong><br>' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</div>';
    }
}

if ($action === 'import_datagangguan') {
    $sql_file = __DIR__ . '/datagangguan.sql';
    if (file_exists($sql_file)) {
        $raw_content = file_get_contents($sql_file);
        
        // Auto-detect and convert UTF-16 if present
        if (substr($raw_content, 0, 2) === "\xFF\xFE") {
            $raw_content = function_exists('mb_convert_encoding') 
                ? mb_convert_encoding($raw_content, 'UTF-8', 'UTF-16LE') 
                : iconv('UTF-16LE', 'UTF-8//IGNORE', $raw_content);
        } elseif (substr($raw_content, 0, 2) === "\xFE\xFF") {
            $raw_content = function_exists('mb_convert_encoding') 
                ? mb_convert_encoding($raw_content, 'UTF-8', 'UTF-16BE') 
                : iconv('UTF-16BE', 'UTF-8//IGNORE', $raw_content);
        }
        
        // Strip UTF-8 BOM
        if (substr($raw_content, 0, 3) === "\xEF\xBB\xBF") {
            $raw_content = substr($raw_content, 3);
        }
        
        $lines = explode("\n", $raw_content);
        $templine = '';
        $err = false;
        $errors = array();
        @mysql_query("SET foreign_key_checks = 0");
        @mysql_query("SET NAMES 'utf8mb4'");
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (substr($trimmed, 0, 2) === '--' || $trimmed === '' || substr($trimmed, 0, 2) === '/*') continue;
            $templine .= $line . "\n";
            if (substr($trimmed, -1, 1) === ';') {
                if (!@mysql_query($templine)) {
                    $errors[] = mysql_error();
                    $err = true;
                    break;
                }
                $templine = '';
            }
        }
        @mysql_query("SET foreign_key_checks = 1");
        if (!$err) {
            $fix_message = '<div class="alert alert-success">✅ <strong>BERHASIL!</strong> Data tabel <code>datagangguan</code> berhasil disinkronkan dari berkas <code>datagangguan.sql</code>!</div>';
        } else {
            $fix_message = '<div class="alert alert-danger">❌ <strong>GAGAL IMPORT:</strong> ' . htmlspecialchars(end($errors)) . '</div>';
        }
    } else {
        $fix_message = '<div class="alert alert-warning">⚠️ Berkas <code>datagangguan.sql</code> tidak ditemukan di folder server! Silakan unggah berkas datagangguan.sql terlebih dahulu.</div>';
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
            $has_empty_master = false;
            foreach ($tables_to_check as $tbl) {
                $check_q = @mysql_query("SELECT COUNT(*) as total FROM `{$tbl}`");
                if ($check_q) {
                    $row = mysql_fetch_assoc($check_q);
                    $count = isset($row['total']) ? (int)$row['total'] : 0;
                    if ($tbl !== 'datagangguan' && $count === 0) {
                        $has_empty_master = true;
                    }
                    echo "<tr><td><code>{$tbl}</code></td><td><span class=\"badge-ok\">Ditemukan</span></td><td><strong>" . number_format($count) . " data</strong></td></tr>";
                } else {
                    echo "<tr><td><code>{$tbl}</code></td><td><span class=\"badge-fail\">Tidak Ditemukan</span></td><td>Error: " . htmlspecialchars(mysql_error()) . "</td></tr>";
                    $has_empty_master = true;
                }
            }
            ?>
        </table>
        <?php if ($has_empty_master): ?>
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px dashed #ced4da;">
                <p style="margin: 0 0 10px 0; color: #856404; font-size: 13px;">⚠️ Terdeteksi tabel master masih kosong. Klik tombol di bawah untuk mengisi data secara otomatis:</p>
                <a href="isi_datamaster.php" class="btn btn-success">⚡ Buka Halaman Pengisian Data Master (1-Klik)</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- 4. Pengecekan View Sistem (v_datagangguan, v_penyulang, v_keypoint) -->
    <div class="card">
        <h3>4. Pengecekan View Sistem (<code>v_datagangguan</code>, <code>v_penyulang</code>, <code>v_keypoint</code>)</h3>
        <table>
            <tr>
                <th>Nama View</th>
                <th>Status</th>
                <th>Jumlah Data Terbaca</th>
                <th>Keterangan</th>
            </tr>
            <?php
            $views_to_check = array(
                'v_datagangguan' => 'Rekap Data Gangguan & Join Relasi',
                'v_penyulang' => 'Pilihan Penyulang (Entri & Monitoring)',
                'v_keypoint' => 'Pilihan Keypoint (Entri & Visualisasi)'
            );
            $has_view_error = false;
            foreach ($views_to_check as $vname => $vdesc) {
                $vq = @mysql_query("SELECT COUNT(*) as total FROM `{$vname}`");
                if ($vq) {
                    $vr = mysql_fetch_assoc($vq);
                    $vtotal = isset($vr['total']) ? (int)$vr['total'] : 0;
                    echo "<tr><td><code>{$vname}</code></td><td><span class=\"badge-ok\">Normal</span></td><td><strong>" . number_format($vtotal) . " baris</strong></td><td>{$vdesc}</td></tr>";
                } else {
                    $has_view_error = true;
                    $verr = mysql_error();
                    echo "<tr><td><code>{$vname}</code></td><td><span class=\"badge-fail\">Error</span></td><td>-</td><td><small style='color:#dc3545;'>" . htmlspecialchars($verr) . "</small></td></tr>";
                }
            }
            ?>
        </table>

        <?php if ($has_view_error): ?>
            <div style="margin-top: 15px;">
                <div class="alert alert-warning" style="margin-bottom: 10px;">
                    ⚠️ <strong>Penyebab Terdeteksi:</strong> View sistem belum dibuat atau terkunci oleh akun <em>definer</em> lama (misal <code>sarc5556@localhost</code> / <code>root@localhost</code>). Hal inilah yang menyebabkan pilihan penyulang atau keypoint suka tiba-tiba kosong di hosting.
                </div>
                <a href="?action=fix_all_views" class="btn btn-success" onclick="return confirm('Jalankan perbaikan otomatis untuk seluruh view database?')">🛠️ Perbaiki Semua View Sekarang (1-Klik)</a>
            </div>
        <?php else: ?>
            <div style="margin-top: 12px; font-size: 13px; color: #155724;">
                ✅ Semua view berjalan normal tanpa error hak akses (definer).
                <a href="?action=fix_all_views" style="margin-left: 10px; color: #0056b3; font-size: 12px; text-decoration: underline;" onclick="return confirm('Jalankan sinkronisasi/re-create ulang seluruh view?')">Segarkan / Buat Ulang Semua View</a>
            </div>
        <?php endif; ?>
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
            echo '<div style="margin-top:15px; padding-top:12px; border-top:1px solid #dee2e6;">';
            echo '<a href="?action=import_datagangguan" class="btn btn-success" onclick="return confirm(\'Sinkronkan data tabel datagangguan dari datagangguan.sql? Data tabel datagangguan akan diperbarui dengan data bersih terbaru.\')">📥 Sinkronkan / Impor Tabel datagangguan dari datagangguan.sql (1-Klik)</a>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- 6. Diagnostik Kode Unit & UP3 Ponorogo -->
    <div class="card">
        <h3>6. Diagnostik Kode Unit & UP3 Ponorogo</h3>
        <table>
            <tr>
                <th>Kode Unit</th>
                <th>Nama Unit (Uraian)</th>
                <th>Tipe</th>
                <th>Jumlah Gangguan Terkait</th>
            </tr>
            <?php
            $uq = @mysql_query("SELECT * FROM kodeunit ORDER BY CASE WHEN uraian LIKE '%UP3%' THEN 0 ELSE 1 END, kodeunit ASC");
            if ($uq && mysql_num_rows($uq) > 0) {
                while ($ur = mysql_fetch_assoc($uq)) {
                    $k_unit = $ur['kodeunit'];
                    $is_up3_row = (stripos($ur['uraian'], 'UP3') !== false || $k_unit == '5125' || $k_unit == '5152');
                    
                    if ($is_up3_row) {
                        $cnt_q = @mysql_query("SELECT COUNT(*) as c FROM datagangguan");
                        $cnt_val = ($cnt_q && $rc = mysql_fetch_assoc($cnt_q)) ? $rc['c'] : 0;
                        $cnt_label = "<strong>" . number_format($cnt_val) . "</strong> (Total Semua ULP)";
                        $type_badge = '<span class="badge-ok">UP3 (Semua Unit)</span>';
                    } else {
                        $cnt_q = @mysql_query("SELECT COUNT(*) as c FROM datagangguan WHERE unit = '" . mysql_real_escape_string($k_unit) . "'");
                        $cnt_val = ($cnt_q && $rc = mysql_fetch_assoc($cnt_q)) ? $rc['c'] : 0;
                        $cnt_label = number_format($cnt_val) . " gangguan";
                        $type_badge = '<span class="badge-warn">ULP</span>';
                    }
                    
                    echo "<tr>
                        <td><code>{$k_unit}</code></td>
                        <td><strong>" . htmlspecialchars($ur['uraian']) . "</strong></td>
                        <td>{$type_badge}</td>
                        <td>{$cnt_label}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-danger'>Tabel <code>kodeunit</code> kosong atau tidak terbaca!</td></tr>";
            }
            ?>
        </table>
    </div>

    <div style="text-align:center; margin-top:20px;">
        <a href="index.php" class="btn">Kembali ke Dashboard Utama</a>
    </div>
</div>
</body>
</html>
