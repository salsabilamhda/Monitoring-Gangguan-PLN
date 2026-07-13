<?php
include "connect.php";

// Fungsi ubah tanggal dari mm/dd/yyyy ke yyyy-mm-dd
function formatTanggal($value) {
    $value = trim(str_replace("\r", "", str_replace("\n", "", $value)));
    if ($value == "" || $value == "NULL") return NULL;

    $parts = explode("/", $value);
    if (count($parts) == 3) {
        $bulan = (int)$parts[0];
        $hari  = (int)$parts[1];
        $tahun = (int)$parts[2];
        // Validasi tanggal (checkdate)
        if (checkdate($bulan, $hari, $tahun)) {
            return sprintf("%04d-%02d-%02d", $tahun, $bulan, $hari);
        }
    }
    return NULL;
}

// Fungsi ubah datetime dari mm/dd/yyyy hh:mm ke yyyy-mm-dd hh:mm:ss
function formatDateTime($value) {
    $value = trim(str_replace("\r", "", str_replace("\n", "", $value)));
    if ($value == "" || $value == "NULL") return NULL;

    $parts = explode(" ", $value);
    if (count($parts) == 2) {
        $tgl = formatTanggal($parts[0]);
        $wkt = trim($parts[1]);
        if (substr_count($wkt, ":") == 1) {
            $wkt .= ":00";
        }
        if ($tgl !== NULL) {
            return $tgl . " " . $wkt;
        }
    }
    return NULL;
}

// Proses upload
if (!empty($_FILES['filecsv']['tmp_name'])) {
    $file = $_FILES['filecsv']['tmp_name'];

    // Deteksi otomatis pemisah (koma atau tab)
    $firstLine = fgets(fopen($file, 'r'));
    $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle, 10000, $delimiter); // Ambil header
        $jumlah_kolom = count($header);

        $row = 1;
        $sukses = 0;
        $gagal = 0;

        while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
            $values = array();
            for ($i = 0; $i < $jumlah_kolom; $i++) {
                $values[$i] = isset($data[$i]) ? trim($data[$i]) : "";
            }

            // Kolom tanggalpengukuran (index 8)
            // Kolom waktupengukuran   (index 9)
            if (isset($values[8])) $values[8] = formatTanggal($values[8]);
            if (isset($values[9])) $values[9] = formatDateTime($values[9]);

            // Escape SQL
            foreach ($values as $k => $v) {
                $values[$k] = ($v === NULL || $v === "") ? "NULL" : "'" . mysql_real_escape_string($v) . "'";
            }

            // Buat query INSERT
            $query = "INSERT INTO ukurgardu VALUES (NULL," . implode(",", $values) . ")";
            $result = mysql_query($query);

            if ($result) $sukses++; else $gagal++;
            $row++;
        }

        fclose($handle);

        echo "<script>
        alert('Upload selesai!\\nBerhasil: $sukses data\\nGagal: $gagal data');
        window.location='ukurgardu.php';
        </script>";
    } else {
        echo "<script>alert('Gagal membuka file CSV!');window.history.back();</script>";
    }
} else {
    echo "<script>alert('Belum ada file yang dipilih!');window.history.back();</script>";
}
?>
