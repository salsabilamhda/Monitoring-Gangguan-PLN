<?php

$host  = "localhost";
$user  = "jart2779_jart2779";
$pass  = "Ponorogo_1234";
$dbase = "jart2779_jaringan";

$koneksi = mysql_connect($host,$user,$pass);

if(!$koneksi){
    die("Koneksi gagal");
}

mysql_select_db($dbase,$koneksi);

?>