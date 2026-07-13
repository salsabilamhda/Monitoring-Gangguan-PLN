<?php
$conn = mysqli_connect(
  "localhost",
  "jart2779_jart2779",
  "Ponorogo_1234",
  "jart2779_jaringan"
);

if(!$conn){
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>