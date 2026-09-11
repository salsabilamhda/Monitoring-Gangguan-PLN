<?php
include "connect.php";

function compressImage($source, $destination, $quality = 70) {
    $info = @getimagesize($source);
    if ($info === false) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if ($image === false) return false;
    $res = imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $res;
}

function saveBase64Image($base64Data, $prefix, $uploadDir = 'uploads/') {
    if (empty($base64Data) || !is_array($base64Data) || empty($base64Data['data'])) {
        return '';
    }
    
    $dataString = $base64Data['data'];
    if (preg_match('/^data:image\/(\w+);base64,/', $dataString, $type)) {
        $dataString = substr($dataString, strpos($dataString, ',') + 1);
        $type = strtolower($type[1]);
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            return '';
        }
        $dataString = base64_decode($dataString);
        if ($dataString === false) {
            return '';
        }
    } else {
        return '';
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $prefix . '_' . date('YmdHis') . '_' . rand(100, 999) . '.jpg';
    $filePath = $uploadDir . $fileName;
    
    if (file_put_contents($filePath, $dataString) !== false) {
        compressImage($filePath, $filePath);
        return $fileName;
    }
    return '';
}

/**
 * Konversi tanggal Excel (angka serial 46030 atau string campuran seperti "4/14/2026 REC")
 * Menjadi format MySQL standar 'YYYY-MM-DD HH:MM:SS'
 */
function parseCustomDateTime($raw, &$extracted_tag = '') {
    $extracted_tag = '';
    if ($raw === null || $raw === '') return '';
    
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    
    // Deteksi jika tanggal diikuti kode seperti "4/14/2026 REC", "4/21/2026 PMT", "4/14/2026 EF", "4/15/2026 OCR"
    if (preg_match('/^(.*?)\s+([A-Za-z0-9\-\*]+)$/', $raw, $m)) {
        $possible_tag = strtoupper(trim($m[2]));
        if (in_array($possible_tag, ['REC', 'PMT', 'EF', 'OCR', 'DGR', 'OCR-INSTANT', 'UFR', 'DIFF', 'DOCR', 'OVR', 'UVR'])) {
            $raw = trim($m[1]);
            $extracted_tag = $possible_tag;
        }
    }
    
    // 1. Jika berupa angka serial Excel (misal 46030, 46133, 46040.5)
    if (is_numeric($raw) && (float)$raw > 1000) {
        $val = (float)$raw;
        $unixTimestamp = ($val - 25569) * 86400;
        return gmdate("Y-m-d H:i:s", (int)round($unixTimestamp));
    }
    
    // 2. Format standar melalui strtotime
    $ts = @strtotime($raw);
    if ($ts !== false && $ts > 0) {
        if (strpos($raw, ':') === false) {
            return date("Y-m-d 00:00:00", $ts);
        }
        return date("Y-m-d H:i:s", $ts);
    }
    
    // 3. Coba parsing d/m/Y atau d-m-Y jika strtotime gagal
    $formats = ['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $raw);
        if ($dt !== false) {
            if (strpos($fmt, 'H') === false) {
                return $dt->format('Y-m-d 00:00:00');
            }
            return $dt->format('Y-m-d H:i:s');
        }
    }
    
    return $raw;
}

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid atau kosong.']);
    exit;
}

// 1. Fetch lookup tables
// Unit lookup (name/code -> code)
$units = [];
$q = mysql_query("SELECT kodeunit, uraian FROM kodeunit");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $units[strtoupper(trim($r['uraian']))] = $r['kodeunit'];
        $units[strtoupper(trim($r['kodeunit']))] = $r['kodeunit'];
    }
}

// Penyulang lookup (name/code -> code)
$penyulangs = [];
$penyulang_list = [];
$q = mysql_query("SELECT kodepenyul, uraianpenyul FROM kodepenyulang");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $kode = strtoupper(trim($r['kodepenyul']));
        $uraian = strtoupper(trim($r['uraianpenyul']));
        
        $penyulangs[$kode] = $kode;
        $penyulangs[$uraian] = $kode;
        
        // Key without spaces/hyphens
        $clean_key = str_replace([' ', '-', '_'], '', $uraian);
        $penyulangs[$clean_key] = $kode;
        
        $penyulang_list[] = [
            'kode' => $kode,
            'uraian' => $uraian,
            'clean' => $clean_key
        ];
    }
}

// Alias dan variasi penulisan penyulang
$penyulang_aliases = [
    'LOROG' => 'LOROK',
    'LOROK' => 'LOROK',
    'KARANG TURI TGK' => 'KTURI',
    'KARANG TURI' => 'KTURI',
    'KARANGTURI' => 'KTURI',
    'WATU KARUNG' => 'WKARU',
    'WATUKARUNG' => 'WKARU',
    'TEGAL OMBO' => 'T-OMB',
    'TEGALOMBO' => 'T-OMB',
    'KEBON AGUNG' => 'KBAGU',
    'KEBONAGUNG' => 'KBAGU',
    'KEBUN AGUNG' => 'KBAGU',
    'KEBUNAGUNG' => 'KBAGU',
    'NAWANGAN BLG' => 'NAWGN',
    'NAWANGAN' => 'NAWGN',
    'GANDUSARI' => 'GDSRI',
    'BADEGAN' => 'BDGAN',
    'JENANGAN' => 'JENNG',
    'KADIPATEN' => 'KDPTN',
    'SELOAJI' => 'SLOJI',
    'MLARAK' => 'MLARK',
    'SUMOROTO' => 'SUMOR',
    'MUNJUNGAN' => 'MJGAN',
    'MUJUNGAN' => 'MJGAN',
    'PASAR PON' => 'PSRPO',
    'NONGKODONO' => 'NKDNO',
    'KAMPAC' => 'KMPAK',
    'KAMPAK' => 'KMPAK',
    'WADUK BENDO' => 'WBNDO'
];
foreach ($penyulang_aliases as $alias => $target) {
    $penyulangs[$alias] = $target;
    $penyulangs[str_replace(' ', '', $alias)] = $target;
}

// Map penyulang -> default unit dari kodekeypoint
$penyulang_to_unit = [];
$q_pu = mysql_query("SELECT kodepenyul, unit, count(*) as c FROM kodekeypoint GROUP BY kodepenyul, unit ORDER BY c DESC");
if ($q_pu) {
    while ($r = mysql_fetch_assoc($q_pu)) {
        $pk = strtoupper(trim($r['kodepenyul']));
        if (!isset($penyulang_to_unit[$pk]) && !empty($r['unit'])) {
            $penyulang_to_unit[$pk] = trim($r['unit']);
        }
    }
}
// Tambahan manual mapping unit penyulang
$penyulang_to_unit['LOROK'] = '51542';
$penyulang_to_unit['LOROG'] = '51542';
$penyulang_to_unit['GDSRI'] = '51543';
$penyulang_to_unit['SUMOR'] = '51540';
$penyulang_to_unit['BDGAN'] = '51540';
$penyulang_to_unit['JENNG'] = '51540';
$penyulang_to_unit['KDPTN'] = '51540';
$penyulang_to_unit['SLOJI'] = '51540';
$penyulang_to_unit['MLARK'] = '51540';
$penyulang_to_unit['KTURI'] = '51543';
$penyulang_to_unit['KBAGU'] = '51542';
$penyulang_to_unit['WKARU'] = '51542';
$penyulang_to_unit['T-OMB'] = '51542';

// Keypoint lookup (description/id -> idkeypoint)
$keypoints = [];
$keypoint_details = [];
$q = mysql_query("SELECT idkeypoint, keterangan, kodepenyul, unit FROM kodekeypoint");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $id = $r['idkeypoint'];
        $ket = strtoupper(trim($r['keterangan']));
        $keypoints[$ket] = $id;
        $keypoints[(string)$id] = $id;
        
        $clean = preg_replace('/^(REC\b\.?|CO\b\.?|PMCB\b\.?|LBSM?\b\.?|FCO\b\.?|PTCT\b\.?|DS\b\.?)\s*/i', '', $r['keterangan']);
        $clean = strtoupper(trim($clean));
        if (!empty($clean)) {
            $keypoints[$clean] = $id;
            $keypoint_details[] = [
                'id' => $id,
                'full' => $ket,
                'clean' => $clean,
                'penyul' => strtoupper(trim($r['kodepenyul'])),
                'unit' => trim($r['unit'])
            ];
        }
    }
}

// Cuaca lookup (uraiancuaca/id -> idcuaca)
$cuacas = [];
$q = mysql_query("SELECT idcuaca, uraiancuaca FROM kodecuaca");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $id = $r['idcuaca'];
        $uraian = strtoupper(trim($r['uraiancuaca']));
        $cuacas[$uraian] = $id;
        $cuacas[str_replace(' ', '_', $uraian)] = $id;
        $cuacas[(string)$id] = $id;
    }
}

// Jenis Gangguan lookup (uraian/id -> idjenisgangguan)
$jenis_gangguans = [];
$q = mysql_query("SELECT idjenisgangguan, uraianjenisgangguan FROM kodejenisgangguan");
if ($q) {
    while ($r = mysql_fetch_assoc($q)) {
        $id = $r['idjenisgangguan'];
        $uraian = strtoupper(trim($r['uraianjenisgangguan']));
        $jenis_gangguans[$uraian] = $id;
        $jenis_gangguans[str_replace(' ', '_', $uraian)] = $id;
        $jenis_gangguans[(string)$id] = $id;
    }
}

$inserted = 0;
$updated = 0;
$skipped = 0;
$errors = [];

foreach ($data as $index => $row) {
    $rowNum = $index + 2; // Row number in Excel (header is row 1)
    
    $raw_unit_check = isset($row['Unit']) ? strtoupper(trim($row['Unit'])) : '';
    $raw_penyulang_check = isset($row['Penyulang']) ? strtoupper(trim($row['Penyulang'])) : '';
    if (in_array($raw_unit_check, ['NIHIL', '-', 'NONE', 'TIDAK ADA']) || in_array($raw_penyulang_check, ['NIHIL', '-', 'NONE', 'TIDAK ADA', 'NORMAL', 'AMAN', 'KOSONG'])) {
        $skipped++;
        continue;
    }

    // 1. Tanggal Gangguan & Kategori Gangguan (PMT / REC)
    $raw_tglgangguan = isset($row['Tanggal Gangguan']) ? trim($row['Tanggal Gangguan']) : '';
    $tag_tgl = '';
    $tglgangguan = parseCustomDateTime($raw_tglgangguan, $tag_tgl);
    
    $raw_kat = isset($row['Kategori Gangguan']) ? strtoupper(trim($row['Kategori Gangguan'])) : '';
    if (!empty($raw_kat)) {
        $kat_gangguan = $raw_kat;
    } elseif (!empty($tag_tgl) && in_array($tag_tgl, ['REC', 'PMT'])) {
        $kat_gangguan = $tag_tgl;
    } elseif (stripos($raw_tglgangguan, 'REC') !== false) {
        $kat_gangguan = 'REC';
    } elseif (stripos($raw_tglgangguan, 'PMT') !== false) {
        $kat_gangguan = 'PMT';
    } elseif (!empty($row['Keypoint ID'])) {
        $kat_gangguan = 'REC';
    } else {
        $kat_gangguan = 'PMT';
    }
    
    // Pastikan nilai kat_gangguan hanya PMT atau REC
    if (strpos($kat_gangguan, 'PMT') !== false) {
        $kat_gangguan = 'PMT';
    } else {
        $kat_gangguan = 'REC';
    }
    
    // 2. Kode Gangguan
    $kodegangguan = isset($row['Kode Gangguan']) ? mysql_real_escape_string(strtoupper(trim($row['Kode Gangguan']))) : '';
    
    // 3. Penyulang
    $raw_penyulang = isset($row['Penyulang']) ? strtoupper(trim($row['Penyulang'])) : '';
    
    // Jika baris berisi 'NIHIL', '-', '0', atau kosong (artinya tidak ada gangguan di shift/hari tersebut), lewati tanpa error
    if (empty($raw_penyulang) || in_array($raw_penyulang, ['NIHIL', '-', '0', 'NONE', 'TIDAK ADA', 'TIDAK GANGGUAN', 'AMAN', 'NORMAL', 'KOSONG', 'NIL'])) {
        $skipped++;
        continue;
    }
    if ($kodegangguan === 'NIHIL' || stripos($kodegangguan, 'NIHIL') !== false) {
        $skipped++;
        continue;
    }
    
    $penyulang_code = '';
    if (!empty($raw_penyulang)) {
        if (isset($penyulangs[$raw_penyulang])) {
            $penyulang_code = $penyulangs[$raw_penyulang];
        } else {
            $no_space = str_replace([' ', '-', '_'], '', $raw_penyulang);
            if (isset($penyulangs[$no_space])) {
                $penyulang_code = $penyulangs[$no_space];
            } else {
                // Handle variasi akhiran G -> K (misal LOROG -> LOROK)
                $alt_k = preg_replace('/G$/i', 'K', $raw_penyulang);
                $alt_k_nospace = str_replace([' ', '-', '_'], '', $alt_k);
                if (isset($penyulangs[$alt_k])) {
                    $penyulang_code = $penyulangs[$alt_k];
                } elseif (isset($penyulangs[$alt_k_nospace])) {
                    $penyulang_code = $penyulangs[$alt_k_nospace];
                } else {
                    $alt = str_replace('KEBON', 'KEBUN', $raw_penyulang);
                    $alt_nospace = str_replace([' ', '-', '_'], '', $alt);
                    if (isset($penyulangs[$alt])) {
                        $penyulang_code = $penyulangs[$alt];
                    } elseif (isset($penyulangs[$alt_nospace])) {
                        $penyulang_code = $penyulangs[$alt_nospace];
                    } else {
                        $stripped = preg_replace('/\s+(TGK|PCT|PNG|BLG|TRENGGALEK|PACITAN|PONOROGO|BALONG)$/i', '', $raw_penyulang);
                        $stripped_nospace = str_replace([' ', '-', '_'], '', $stripped);
                        if (isset($penyulangs[$stripped])) {
                            $penyulang_code = $penyulangs[$stripped];
                        } elseif (isset($penyulangs[$stripped_nospace])) {
                            $penyulang_code = $penyulangs[$stripped_nospace];
                        } else {
                            foreach ($penyulang_list as $p) {
                                if (strpos($raw_penyulang, $p['uraian']) !== false || strpos($p['uraian'], $raw_penyulang) !== false) {
                                    $penyulang_code = $p['kode'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Fuzzy matching jika belum cocok sama sekali (kemiripan >= 75%)
        if (empty($penyulang_code)) {
            $bestKode = null;
            $highestSim = 0;
            $cleanInput = str_replace([' ', '-', '_'], '', $raw_penyulang);
            foreach ($penyulang_list as $p) {
                similar_text($raw_penyulang, $p['uraian'], $sim);
                if ($sim > $highestSim) {
                    $highestSim = $sim;
                    $bestKode = $p['kode'];
                }
                similar_text($cleanInput, $p['clean'], $simNoSpace);
                if ($simNoSpace > $highestSim) {
                    $highestSim = $simNoSpace;
                    $bestKode = $p['kode'];
                }
            }
            if ($highestSim >= 75) {
                $penyulang_code = $bestKode;
            }
        }
    }
    
    // 4. Unit
    $raw_unit = isset($row['Unit']) ? strtoupper(trim($row['Unit'])) : '';
    $unit = '';
    if (!empty($raw_unit)) {
        if (isset($units[$raw_unit])) {
            $unit = $units[$raw_unit];
        } else {
            foreach ($units as $uk => $uv) {
                if (strpos($raw_unit, $uk) !== false || strpos($uk, $raw_unit) !== false) {
                    $unit = $uv;
                    break;
                }
            }
        }
    }
    // Jika unit di Excel kosong, otomatis cari dari kode penyulang
    if (empty($unit) && !empty($penyulang_code) && isset($penyulang_to_unit[$penyulang_code])) {
        $unit = $penyulang_to_unit[$penyulang_code];
    }
    // Fallback jika unit masih belum terisi
    if (empty($unit)) {
        if (stripos($raw_unit, 'TRENGGALEK') !== false) $unit = '51543';
        elseif (stripos($raw_unit, 'PACITAN') !== false) $unit = '51542';
        elseif (stripos($raw_unit, 'BALONG') !== false) $unit = '51541';
        elseif (stripos($raw_unit, 'PONOROGO') !== false) $unit = '51540';
        else $unit = '51540'; // Default ULP Ponorogo
    }
    
    // 5. Keypoint ID
    $raw_keypoint = isset($row['Keypoint ID']) ? strtoupper(trim($row['Keypoint ID'])) : '';
    $keypointid = '';
    if (!empty($raw_keypoint)) {
        $raw_kp_nospace = str_replace([' ', '-', '_'], '', $raw_keypoint);
        $raw_kp_clean = strtoupper(trim(preg_replace('/^(REC\b\.?|CO\b\.?|PMCB\b\.?|LBSM?\b\.?|FCO\b\.?|PTCT\b\.?|DS\b\.?)\s*/i', '', $raw_keypoint)));
        $raw_kp_clean_nospace = str_replace([' ', '-', '_'], '', $raw_kp_clean);

        // 1. PRIORITAS UTAMA: Cari pada PENYULANG YANG SAMA (menghindari salah sambung antar-wilayah)
        if (!empty($penyulang_code)) {
            // 1a. Exact match pada penyulang yang sama
            foreach ($keypoint_details as $kd) {
                if ($kd['penyul'] === $penyulang_code) {
                    if ($kd['full'] === $raw_keypoint || $kd['clean'] === $raw_keypoint || $kd['clean'] === $raw_kp_clean) {
                        $keypointid = $kd['id'];
                        break;
                    }
                }
            }

            // 1b. Exact match tanpa spasi pada penyulang yang sama
            if (empty($keypointid)) {
                foreach ($keypoint_details as $kd) {
                    if ($kd['penyul'] === $penyulang_code) {
                        $kd_full_nospace = str_replace([' ', '-', '_'], '', $kd['full']);
                        $kd_clean_nospace = str_replace([' ', '-', '_'], '', $kd['clean']);
                        if ($kd_clean_nospace === $raw_kp_clean_nospace || $kd_clean_nospace === $raw_kp_nospace || $kd_full_nospace === $raw_kp_nospace) {
                            $keypointid = $kd['id'];
                            break;
                        }
                    }
                }
            }

            // 1c. Substring match pada penyulang yang sama (hanya jika panjang nama >= 4 karakter)
            if (empty($keypointid) && strlen($raw_kp_clean_nospace) >= 4) {
                foreach ($keypoint_details as $kd) {
                    if ($kd['penyul'] === $penyulang_code) {
                        $kd_clean_nospace = str_replace([' ', '-', '_'], '', $kd['clean']);
                        if (strlen($kd_clean_nospace) >= 4) {
                            if (strpos($kd_clean_nospace, $raw_kp_clean_nospace) !== false || strpos($raw_kp_clean_nospace, $kd_clean_nospace) !== false) {
                                $keypointid = $kd['id'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        // 2. Direct ID check jika Excel berisi angka ID langsung
        if (empty($keypointid) && is_numeric($raw_keypoint) && isset($keypoints[(string)$raw_keypoint])) {
            $keypointid = (int)$raw_keypoint;
        }

        // 3. AUTO-CREATE KEYPOINT JIKA BELUM ADA (Otomatis didaftarkan ke Penyulang & Unit yang sesuai di Excel)
        if (empty($keypointid) && !empty($penyulang_code)) {
            $detected_jenis = 'REC';
            if (preg_match('/^(PMCB|LBSM?|CO|FCO|PTCT)\b/i', $raw_keypoint, $mj)) {
                $detected_jenis = strtoupper($mj[1]);
            }
            $auto_ket = (strpos($raw_keypoint, $detected_jenis) === false) ? "$detected_jenis " . $raw_keypoint : $raw_keypoint;
            $auto_unit = !empty($unit) ? $unit : (!empty($penyulang_to_unit[$penyulang_code]) ? $penyulang_to_unit[$penyulang_code] : "51540");
            
            $escaped_ket = mysql_real_escape_string($auto_ket);
            $escaped_penyul = mysql_real_escape_string($penyulang_code);
            $escaped_unit = mysql_real_escape_string($auto_unit);
            $escaped_jenis = mysql_real_escape_string($detected_jenis);

            $insert_kp = mysql_query("INSERT INTO kodekeypoint (kodepenyul, jenis, keterangan, unit, zona, latitud, longitud, id_keypint) 
                                      VALUES ('$escaped_penyul', '$escaped_jenis', '$escaped_ket', '$escaped_unit', '1', '0', '0', 0)");
            if ($insert_kp) {
                $new_id = mysql_insert_id();
                $keypointid = $new_id;
                
                // Daftarkan ke cache memori agar baris berikutnya di Excel ini langsung mengenalnya
                $clean_new = strtoupper(trim(preg_replace('/^(REC\b\.?|CO\b\.?|PMCB\b\.?|LBSM?\b\.?|FCO\b\.?|PTCT\b\.?|DS\b\.?)\s*/i', '', $auto_ket)));
                $keypoint_details[] = [
                    'id' => $new_id,
                    'full' => $auto_ket,
                    'clean' => $clean_new,
                    'penyul' => $penyulang_code,
                    'unit' => $auto_unit
                ];
                $keypoints[$raw_keypoint] = $new_id;
                $keypoints[$auto_ket] = $new_id;
            }
        }
    }
    
    // 6. Kategori Gangguan (TEMPORER / PERMANEN)
    $raw_kategori = isset($row['Kategori']) ? strtoupper(trim($row['Kategori'])) : '';
    if (strpos($raw_kategori, 'PERM') !== false) {
        $kategorigangguan = 'PERMANEN';
    } else {
        $kategorigangguan = 'TEMPORER';
    }
    
    // 7. Tanggal Masuk & Relay Kerja
    $raw_tglmasuk = isset($row['Tanggal Masuk']) ? trim($row['Tanggal Masuk']) : '';
    $tag_masuk = '';
    $tglmasuk = parseCustomDateTime($raw_tglmasuk, $tag_masuk);
    if (empty($tglmasuk)) {
        $tglmasuk = $tglgangguan;
    }
    
    $relay = isset($row['Relay Kerja']) ? strtoupper(trim($row['Relay Kerja'])) : '';
    if (empty($relay) && !empty($tag_masuk)) {
        $relay = $tag_masuk;
    }
    
    $fasa = isset($row['Fasa']) ? mysql_real_escape_string(strtoupper(trim($row['Fasa']))) : '';
    
    $kv0 = isset($row['KV 0']) ? floatval($row['KV 0']) : 0;
    $inetral = isset($row['I N']) ? floatval($row['I N']) : 0;
    $ir = isset($row['I R']) ? floatval($row['I R']) : 0;
    $ies = isset($row['I S']) ? floatval($row['I S']) : 0;
    $it = isset($row['I T']) ? floatval($row['I T']) : 0;
    
    // 8. Cuaca
    $raw_cuaca = isset($row['Cuaca']) ? strtoupper(trim($row['Cuaca'])) : '';
    $raw_cuaca_clean = str_replace('_', ' ', $raw_cuaca);
    $cuacakode = '';
    if (!empty($raw_cuaca)) {
        if (isset($cuacas[$raw_cuaca])) {
            $cuacakode = $cuacas[$raw_cuaca];
        } elseif (isset($cuacas[$raw_cuaca_clean])) {
            $cuacakode = $cuacas[$raw_cuaca_clean];
        } else {
            foreach ($cuacas as $ck => $cid) {
                if (!is_numeric($ck) && (strpos($ck, $raw_cuaca_clean) !== false || strpos($raw_cuaca_clean, $ck) !== false)) {
                    $cuacakode = $cid;
                    break;
                }
            }
        }
    }
    if (empty($cuacakode)) {
        $cuacakode = isset($cuacas['CERAH']) ? $cuacas['CERAH'] : 2;
    }
    
    // 9. Jenis Gangguan
    $raw_jenis = isset($row['Jenis Gangguan']) ? strtoupper(trim($row['Jenis Gangguan'])) : '';
    $raw_jenis_clean = str_replace('_', ' ', $raw_jenis);
    $raw_jenis_clean = preg_replace('/\s+/', ' ', $raw_jenis_clean);
    
    $jeniskode = '';
    if (!empty($raw_jenis)) {
        if (isset($jenis_gangguans[$raw_jenis])) {
            $jeniskode = $jenis_gangguans[$raw_jenis];
        } elseif (isset($jenis_gangguans[$raw_jenis_clean])) {
            $jeniskode = $jenis_gangguans[$raw_jenis_clean];
        } elseif (strpos($raw_jenis_clean, 'TERENCANA') !== false) {
            $jeniskode = isset($jenis_gangguans['TIDAK TERENCANA']) ? $jenis_gangguans['TIDAK TERENCANA'] : 23;
        } else {
            // Auto-create jenis gangguan baru jika ada nama baru
            $escaped_j = mysql_real_escape_string($raw_jenis_clean);
            $ins_j = mysql_query("INSERT INTO kodejenisgangguan (uraianjenisgangguan) VALUES ('$escaped_j')");
            if ($ins_j) {
                $new_jid = mysql_insert_id();
                $jeniskode = $new_jid;
                $jenis_gangguans[$raw_jenis] = $new_jid;
                $jenis_gangguans[$raw_jenis_clean] = $new_jid;
            }
        }
    }
    // Fallback jika kosong ke 22 (TIDAK DITEMUKAN)
    if (empty($jeniskode)) {
        $jeniskode = isset($jenis_gangguans['TIDAK DITEMUKAN']) ? $jenis_gangguans['TIDAK DITEMUKAN'] : 22;
    }
    
    $latlokasi = isset($row['Latitude']) ? mysql_real_escape_string(trim($row['Latitude'])) : '';
    $longlokasi = isset($row['Longitude']) ? mysql_real_escape_string(trim($row['Longitude'])) : '';
    $hasiltemuan = isset($row['Hasil Temuan']) ? mysql_real_escape_string(strtoupper(trim($row['Hasil Temuan']))) : '';
    
    // Validasi esensial
    if (empty($tglgangguan)) {
        $errors[] = "Baris $rowNum: Tanggal Gangguan kosong atau tidak valid.";
        continue;
    }
    if (empty($penyulang_code)) {
        $errors[] = "Baris $rowNum: Penyulang '$raw_penyulang' tidak ditemukan di database.";
        continue;
    }
    
    // Process base64 photo uploads
    $foto1 = isset($row['foto1']) ? saveBase64Image($row['foto1'], 'FILE1') : '';
    $foto2 = isset($row['foto2']) ? saveBase64Image($row['foto2'], 'FILE2') : '';

    // Check for duplicate data in database - if exists, UPDATE (timpa dengan data terbaru)
    $dup_where = "";
    if (!empty($kodegangguan)) {
        $dup_where = "kodegangguan = '$kodegangguan'";
    } else {
        $dup_where = "tglgangguan = '$tglgangguan' AND unit = '$unit' AND penyulang = '$penyulang_code' AND keypointid = '$keypointid'";
    }
    
    $escaped_tgl = mysql_real_escape_string($tglgangguan);
    $escaped_tglmasuk = mysql_real_escape_string($tglmasuk);
    $escaped_kat = mysql_real_escape_string($kat_gangguan);
    $escaped_kategori = mysql_real_escape_string($kategorigangguan);
    $escaped_relay = mysql_real_escape_string($relay);
    
    $check = mysql_query("SELECT idgangguan, foto1, foto2 FROM datagangguan WHERE $dup_where LIMIT 1");
    if ($check && mysql_num_rows($check) > 0) {
        $existing = mysql_fetch_assoc($check);
        $existing_id = $existing['idgangguan'];
        
        // Tetap gunakan foto lama jika di file baru tidak menyertakan foto
        $final_foto1 = !empty($foto1) ? $foto1 : $existing['foto1'];
        $final_foto2 = !empty($foto2) ? $foto2 : $existing['foto2'];

        $update_sql = "UPDATE datagangguan SET 
            tglgangguan = '$escaped_tgl',
            kat_gangguan = '$escaped_kat',
            unit = '$unit',
            penyulang = '$penyulang_code',
            keypointid = '$keypointid',
            kategorigangguan = '$escaped_kategori',
            tglmasuk = '$escaped_tglmasuk',
            relay = '$escaped_relay',
            fasa = '$fasa',
            kv0 = '$kv0',
            inetral = '$inetral',
            ir = '$ir',
            ies = '$ies',
            it = '$it',
            cuacakode = '$cuacakode',
            jeniskode = '$jeniskode',
            hasiltemuan = '$hasiltemuan',
            foto1 = '$final_foto1',
            foto2 = '$final_foto2',
            latlokasi = '$latlokasi',
            longlokasi = '$longlokasi'
            WHERE idgangguan = $existing_id";
        
        $res_up = mysql_query($update_sql);
        if ($res_up) {
            $updated++;
        } else {
            $errors[] = "Baris $rowNum: Gagal memperbarui data (" . mysql_error() . ")";
        }
        continue;
    }

    // SQL insert jika data belum ada
    $sql = "INSERT INTO datagangguan (
        tglgangguan, kat_gangguan, unit, penyulang, keypointid, kategorigangguan, 
        tglmasuk, relay, fasa, kv0, inetral, ir, ies, it, cuacakode, jeniskode, 
        hasiltemuan, foto1, foto2, latlokasi, longlokasi, kodegangguan
    ) VALUES (
        '$escaped_tgl', '$escaped_kat', '$unit', '$penyulang_code', '$keypointid', '$escaped_kategori',
        '$escaped_tglmasuk', '$escaped_relay', '$fasa', '$kv0', '$inetral', '$ir', '$ies', '$it', 
        '$cuacakode', '$jeniskode', '$hasiltemuan', '$foto1', '$foto2', '$latlokasi', '$longlokasi', '$kodegangguan'
    )";
    
    $res = mysql_query($sql);
    if ($res) {
        $inserted++;
    } else {
        $errors[] = "Baris $rowNum: Gagal menyimpan data ke database (" . mysql_error() . ")";
    }
}

echo json_encode([
    'success' => count($errors) === 0 || $inserted > 0 || $updated > 0,
    'inserted' => $inserted,
    'updated' => $updated,
    'errors' => $errors
]);
