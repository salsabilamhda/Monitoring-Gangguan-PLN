<?php
include "connect.php";

$sql = "CREATE TABLE IF NOT EXISTS `ukurgardu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namaunit` varchar(100) DEFAULT NULL,
  `namaulp` varchar(100) DEFAULT NULL,
  `namapenyulang` varchar(100) DEFAULT NULL,
  `namaaset` varchar(100) DEFAULT NULL,
  `kapasitasaset` varchar(100) DEFAULT NULL,
  `beban_r` varchar(50) DEFAULT NULL,
  `beban_s` varchar(50) DEFAULT NULL,
  `beban_t` varchar(50) DEFAULT NULL,
  `tanggalpengukuran` date DEFAULT NULL,
  `waktupengukuran` datetime DEFAULT NULL,
  `pembebanantrafo` decimal(10,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

if (mysql_query($sql)) {
    echo "<h2>Tabel 'ukurgardu' berhasil dibuat!</h2>";
    echo "<p>Silakan kembali ke <a href='index.php'>Halaman Utama</a>.</p>";
} else {
    echo "<h2>Gagal membuat tabel:</h2> <p>" . mysql_error() . "</p>";
}
?>
