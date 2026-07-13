<?php
require_once __DIR__ . '/mysql_shim.php';

$host  = "localhost";
$user  = "root";
$pass  = "";
$dbase = "jart2779_jaringan";

$koneksi = mysql_connect($host,$user,$pass);

if(!$koneksi){
    die("Koneksi gagal");
}

mysql_select_db($dbase,$koneksi);

?>