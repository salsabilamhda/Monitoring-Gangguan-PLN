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

mysql_query($sql);

$sql_apkt = "CREATE TABLE IF NOT EXISTS `data_apkt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recloser_name` varchar(100) NOT NULL,
  `keypointid` varchar(30) DEFAULT NULL,
  `penyulang_code` varchar(30) DEFAULT NULL,
  `ulp` varchar(10) NOT NULL,
  `tipe` varchar(20) NOT NULL,
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `jumlah_apkt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

if (mysql_query($sql_apkt)) {
    // Check if table is empty
    $check = mysql_query("SELECT COUNT(*) FROM data_apkt");
    $row = mysql_fetch_array($check);
    if ($row[0] == 0) {
        $insert_apkt = "INSERT INTO `data_apkt` (`recloser_name`, `keypointid`, `penyulang_code`, `ulp`, `tipe`, `bulan`, `tahun`, `jumlah_apkt`) VALUES
        ('REC KANTOR POS', '738', NULL, 'TGK', 'TEMPORER', 4, 2026, 2),
        ('REC KUTU KULON', '80', NULL, 'BLG', 'TEMPORER', 4, 2026, 2),
        ('REC KOCOR', '159', NULL, 'TGK', 'TEMPORER', 4, 2026, 2),
        ('PMCB SUMBERREJO', '908', NULL, 'TGK', 'TEMPORER', 4, 2026, 1),
        ('PMT LOROK', NULL, 'LOROK', 'PCT', 'TEMPORER', 4, 2026, 1),
        ('REC TOKAWI', '55', NULL, 'BLG', 'TEMPORER', 4, 2026, 1),
        ('PMCB GEMAH', '913', NULL, 'TGK', 'TEMPORER', 4, 2026, 1),
        ('REC TRANJANG 1', '703', NULL, 'PNG', 'TEMPORER', 4, 2026, 1),
        ('PMT RSUD', NULL, 'RSUDP', 'PNG', 'TEMPORER', 4, 2026, 1),
        ('REC KORAMIL', NULL, NULL, 'PCT', 'TEMPORER', 4, 2026, 1),
        ('REC NGADIMULYO', '867', NULL, 'TGK', 'PERMANEN', 4, 2026, 4),
        ('REC MLINJON', '926', NULL, 'TGK', 'PERMANEN', 4, 2026, 3),
        ('PMCB JATISARI', '811', NULL, 'TGK', 'PERMANEN', 4, 2026, 2),
        ('REC KOCOR', '159', NULL, 'TGK', 'PERMANEN', 4, 2026, 2),
        ('PMCB TUMPUK', '786', NULL, 'TGK', 'PERMANEN', 4, 2026, 2),
        ('PMCB HADIWARNO', '289', NULL, 'PCT', 'PERMANEN', 4, 2026, 1),
        ('PMT COKROKEMBANG', NULL, 'COKRO', 'PCT', 'PERMANEN', 4, 2026, 1),
        ('PMCB PELEM', '32', NULL, 'BLG', 'PERMANEN', 4, 2026, 1),
        ('PMCB SUMBERINGIN', '932', NULL, 'TGK', 'PERMANEN', 4, 2026, 1),
        ('PMCB BANJARSARI', '406', NULL, 'PCT', 'PERMANEN', 4, 2026, 1);";
        mysql_query($insert_apkt);
    }
    echo "<h2>Tabel database berhasil di-setup!</h2>";
    echo "<p>Silakan kembali ke <a href='index.php'>Halaman Utama</a>.</p>";
} else {
    echo "<h2>Gagal membuat tabel:</h2> <p>" . mysql_error() . "</p>";
}
?>
