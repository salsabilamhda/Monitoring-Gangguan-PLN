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
mysql_query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

?>