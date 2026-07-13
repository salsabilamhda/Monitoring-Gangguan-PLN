<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "jart2779_jaringan"
);

if (!$conn) {
    die(mysqli_connect_error());
}

echo "Koneksi berhasil";
?>