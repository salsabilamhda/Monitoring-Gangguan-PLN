<?php
if (file_exists(__DIR__ . '/mysql_shim.php')) {
    require_once __DIR__ . '/mysql_shim.php';
}

// Deteksi apakah sedang berjalan di server lokal atau hosting (kompatibel PHP 5.6 - 8.x)
$host_name = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
$is_local  = (
    $host_name === '' || 
    strpos($host_name, 'localhost') !== false || 
    strpos($host_name, '127.0.0.1') !== false || 
    strpos($host_name, '.test') !== false || 
    (function_exists('php_sapi_name') && php_sapi_name() === 'cli')
);

if ($is_local) {
    // Pengaturan database Lokal (Laragon / XAMPP)
    $host  = "localhost";
    $user  = "root";
    $pass  = "";
    $dbase = "jart2779_jaringan";
} else {
    // Pengaturan database Hosting cPanel
    $host  = "localhost";
    $user  = "jart2779_jart2779";
    $pass  = "Ponorogo_1234";
    $dbase = "jart2779_jaringan";
}

$koneksi = @mysql_connect($host, $user, $pass);

// Fallback otomatis jika salah satu koneksi gagal
if (!$koneksi) {
    if ($is_local) {
        $koneksi = @mysql_connect("localhost", "jart2779_jart2779", "Ponorogo_1234");
    } else {
        $koneksi = @mysql_connect("localhost", "root", "");
    }
}

if (!$koneksi) {
    die("Koneksi database gagal: " . (function_exists('mysql_error') ? mysql_error() : ''));
}

if (!mysql_select_db($dbase, $koneksi)) {
    die("Koneksi database berhasil, namun database '{$dbase}' gagal dipilih: " . (function_exists('mysql_error') ? mysql_error() : ''));
}

@mysql_query("SET NAMES 'utf8'", $koneksi);
?>

