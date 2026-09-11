<?php
include "connect.php";

// Fetch distinct years from database for filter dropdown
$years = [];
$qy = mysql_query("SELECT DISTINCT YEAR(tglgangguan) as tahun FROM datagangguan WHERE tglgangguan > '2000-01-01 00:00:00' ORDER BY tahun DESC");
while ($ry = mysql_fetch_assoc($qy)) {
    if ($ry['tahun'] > 2000) {
        $years[] = $ry['tahun'];
    }
}
if (empty($years)) {
    $years[] = date('Y');
}

// Fetch units for filter dropdown
$units = [];
$qu = mysql_query("SELECT * FROM kodeunit ORDER BY uraian ASC");
while ($ru = mysql_fetch_assoc($qu)) {
    $units[] = $ru;
}

// Setup Filters
$selected_tahun = isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : 'ALL';
$selected_bulan = isset($_REQUEST['bulan']) ? $_REQUEST['bulan'] : 'ALL';
$selected_unit = isset($_REQUEST['unit']) ? $_REQUEST['unit'] : 'ALL';

$month_names = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Construct WHERE clauses
$where_clauses = ["g.tglgangguan > '2000-01-01 00:00:00'"];
$where_clauses_no_month = ["g.tglgangguan > '2000-01-01 00:00:00'"];

if ($selected_tahun !== 'ALL' && !empty($selected_tahun)) {
    $where_clauses[] = "YEAR(g.tglgangguan) = '" . mysql_real_escape_string($selected_tahun) . "'";
    $where_clauses_no_month[] = "YEAR(g.tglgangguan) = '" . mysql_real_escape_string($selected_tahun) . "'";
}
if ($selected_bulan !== 'ALL' && !empty($selected_bulan)) {
    $where_clauses[] = "MONTH(g.tglgangguan) = '" . mysql_real_escape_string($selected_bulan) . "'";
}
if ($selected_unit !== 'ALL' && !empty($selected_unit) && $selected_unit !== '5125') {
    $where_clauses[] = "g.unit = '" . mysql_real_escape_string($selected_unit) . "'";
    $where_clauses_no_month[] = "g.unit = '" . mysql_real_escape_string($selected_unit) . "'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

$where_sql_no_month = "";
if (count($where_clauses_no_month) > 0) {
    $where_sql_no_month = "WHERE " . implode(" AND ", $where_clauses_no_month);
}

// 1. Overview Cards Data
// Total Gangguan
$q_total = mysql_query("SELECT COUNT(*) as total FROM datagangguan g $where_sql");
$r_total = mysql_fetch_assoc($q_total);
$total_gangguan = $r_total['total'];

// Total PMT (Permanen)
$pmt_clauses = $where_clauses;
$pmt_clauses[] = "g.kategorigangguan = 'PERMANEN'";
$pmt_where_sql = "WHERE " . implode(" AND ", $pmt_clauses);
$q_pmt = mysql_query("SELECT COUNT(*) as total FROM datagangguan g $pmt_where_sql");
$r_pmt = mysql_fetch_assoc($q_pmt);
$total_pmt = $r_pmt['total'];

// Total REC (Temporer)
$rec_clauses = $where_clauses;
$rec_clauses[] = "g.kategorigangguan = 'TEMPORER'";
$rec_where_sql = "WHERE " . implode(" AND ", $rec_clauses);
$q_rec = mysql_query("SELECT COUNT(*) as total FROM datagangguan g $rec_where_sql");
$r_rec = mysql_fetch_assoc($q_rec);
$total_rec = $r_rec['total'];

// Top Cause
$q_top_cause = mysql_query("
    SELECT j.uraianjenisgangguan, COUNT(*) as jumlah 
    FROM datagangguan g 
    JOIN kodejenisgangguan j ON g.jeniskode = j.idjenisgangguan 
    $where_sql 
    GROUP BY g.jeniskode, j.uraianjenisgangguan 
    ORDER BY jumlah DESC 
    LIMIT 1
");
$r_top_cause = mysql_fetch_assoc($q_top_cause);
$top_cause = isset($r_top_cause['uraianjenisgangguan']) ? $r_top_cause['uraianjenisgangguan'] : '-';
$top_cause_count = isset($r_top_cause['jumlah']) ? $r_top_cause['jumlah'] : 0;

// Top Weather
$q_top_weather = mysql_query("
    SELECT c.uraiancuaca, COUNT(*) as jumlah 
    FROM datagangguan g 
    JOIN kodecuaca c ON g.cuacakode = c.idcuaca 
    $where_sql 
    GROUP BY g.cuacakode, c.uraiancuaca 
    ORDER BY jumlah DESC 
    LIMIT 1
");
$r_top_weather = mysql_fetch_assoc($q_top_weather);
$top_weather = isset($r_top_weather['uraiancuaca']) ? $r_top_weather['uraiancuaca'] : '-';

// 2. Data Top ULP Gangguan Permanen & Temporer
$ulp_stats = [];
$ulp_labels = [];
$ulp_permanen = [];
$ulp_temporer = [];

$q_ulp_stats = mysql_query("
    SELECT COALESCE(u.uraian, g.unit) as uraian, g.unit,
           SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'PERMANEN' THEN 1 ELSE 0 END) as permanen,
           SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'TEMPORER' THEN 1 ELSE 0 END) as temporer,
           COUNT(*) as total_all
    FROM datagangguan g
    LEFT JOIN kodeunit u ON TRIM(g.unit) = TRIM(u.kodeunit)
    $where_sql
    GROUP BY g.unit, u.uraian
    ORDER BY total_all DESC
");

if ($q_ulp_stats) {
    while ($row = mysql_fetch_assoc($q_ulp_stats)) {
        $unit_val = trim($row['unit']);
        if ($unit_val === '' || $unit_val === null) continue;

        $name = !empty($row['uraian']) ? $row['uraian'] : $unit_val;
        $upper = strtoupper($name);
        if (strpos($upper, 'TRENGGALEK') !== false || $unit_val === '51543') $short = 'TGK';
        elseif ((strpos($upper, 'PONOROGO') !== false && strpos($upper, 'ULP') !== false) || $unit_val === '51540') $short = 'PNG';
        elseif (strpos($upper, 'PACITAN') !== false || $unit_val === '51542') $short = 'PCT';
        elseif (strpos($upper, 'BALONG') !== false || $unit_val === '51541') $short = 'BLG';
        else $short = $name;

        $p = (int)$row['permanen'];
        $t = (int)$row['temporer'];

        $ulp_stats[] = [
            'label' => $short,
            'permanen' => $p,
            'temporer' => $t
        ];
        $ulp_labels[] = $short;
        $ulp_permanen[] = $p;
        $ulp_temporer[] = $t;
    }
}

// Fallback jika belum ada data agar chart tetap menampilkan kerangka ULP
if (empty($ulp_labels)) {
    $default_ulps = ['PCT', 'TGK', 'PNG', 'BLG'];
    foreach ($default_ulps as $df) {
        $ulp_labels[] = $df;
        $ulp_permanen[] = 0;
        $ulp_temporer[] = 0;
        $ulp_stats[] = [
            'label' => $df,
            'permanen' => 0,
            'temporer' => 0
        ];
    }
}

// 3. Data Gangguan Permanen & Temporer per ULP Bulanan
$ulp_key_map = [
    'ULP BALONG' => 'BALONG',
    'ULP PACITAN' => 'PACITAN',
    'ULP PONOROGO' => 'PONOROGO',
    'ULP TRENGGALEK' => 'TRENGGALEK',
    'UP3 PONOROGO' => 'UP3 PNG'
];

$monthly_data_pmt = [];
$monthly_data_rec = [];
$available_months = [];

$q_monthly_ulp = mysql_query("
    SELECT COALESCE(u.uraian, g.unit) as uraian, MONTH(g.tglgangguan) as bulan,
           SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'PERMANEN' THEN 1 ELSE 0 END) as permanen,
           SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'TEMPORER' THEN 1 ELSE 0 END) as temporer
    FROM datagangguan g
    LEFT JOIN kodeunit u ON TRIM(g.unit) = TRIM(u.kodeunit)
    $where_sql_no_month
    GROUP BY g.unit, u.uraian, MONTH(g.tglgangguan)
");
while ($row = mysql_fetch_assoc($q_monthly_ulp)) {
    $raw_name = $row['uraian'];
    $mapped_name = 'LAINNYA';
    foreach ($ulp_key_map as $k => $v) {
        if (strpos(strtoupper($raw_name), $k) !== false) {
            $mapped_name = $v;
            break;
        }
    }
    $bulan = (int)$row['bulan'];
    $available_months[$bulan] = true;
    
    if (!isset($monthly_data_pmt[$mapped_name])) $monthly_data_pmt[$mapped_name] = [];
    if (!isset($monthly_data_rec[$mapped_name])) $monthly_data_rec[$mapped_name] = [];
    
    $monthly_data_pmt[$mapped_name][$bulan] = (int)$row['permanen'];
    $monthly_data_rec[$mapped_name][$bulan] = (int)$row['temporer'];
}
ksort($available_months);
$available_months_keys = array_keys($available_months);
if (empty($available_months_keys)) {
    $available_months_keys = [(int)date('m')];
}

$month_abbrev = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

$chart_ulps = ['BALONG', 'PACITAN', 'PONOROGO', 'TRENGGALEK', 'UP3 PNG'];
$datasets_pmt = [];
$datasets_rec = [];

$colors_monthly = [
    1 => '#242c6d', // Jan
    2 => '#fd7e14', // Feb
    3 => '#6c757d', // Mar
    4 => '#ffc107', // Apr
    5 => '#17a2b8', // Mei
    6 => '#28a745', // Jun
    7 => '#e83e8c', // Jul
    8 => '#dc3545', // Agt
    9 => '#20c997', // Sep
    10 => '#6610f2', // Okt
    11 => '#a83e8c', // Nov
    12 => '#555555'  // Des
];

foreach ($available_months_keys as $m) {
    $data_pmt_m = [];
    $data_rec_m = [];
    foreach ($chart_ulps as $ulp) {
        $data_pmt_m[] = isset($monthly_data_pmt[$ulp][$m]) ? $monthly_data_pmt[$ulp][$m] : 0;
        $data_rec_m[] = isset($monthly_data_rec[$ulp][$m]) ? $monthly_data_rec[$ulp][$m] : 0;
    }
    
    $color = isset($colors_monthly[$m]) ? $colors_monthly[$m] : '#6c757d';
    
    $datasets_pmt[] = [
        'label' => isset($month_abbrev[$m]) ? $month_abbrev[$m] : ('Bulan ' . $m),
        'data' => $data_pmt_m,
        'backgroundColor' => $color
    ];
    $datasets_rec[] = [
        'label' => isset($month_abbrev[$m]) ? $month_abbrev[$m] : ('Bulan ' . $m),
        'data' => $data_rec_m,
        'backgroundColor' => $color
    ];
}

// 4. Data Trend Gangguan 3 Top Skor Temporer & Permanen Keypoint
$ulp_keypoint_data = [];
$target_ulps = [
    51541 => 'BALONG',
    51542 => 'PACITAN',
    51540 => 'PONOROGO',
    51543 => 'TRENGGALEK'
];

foreach ($target_ulps as $ulp_id => $ulp_name) {
    $q_kp = mysql_query("
        SELECT 
            IF(COALESCE(k.keterangan, '') != '', k.keterangan, 'PMT') as nama_keypoint,
            SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'TEMPORER' THEN 1 ELSE 0 END) as temporer,
            SUM(CASE WHEN UPPER(TRIM(g.kategorigangguan)) = 'PERMANEN' THEN 1 ELSE 0 END) as permanen,
            COUNT(*) as total
        FROM datagangguan g
        LEFT JOIN kodekeypoint k ON g.keypointid = k.idkeypoint
        $where_sql AND g.unit = '$ulp_id'
        GROUP BY nama_keypoint
    ");
    
    $kp_list = [];
    if ($q_kp) {
        while ($r_kp = mysql_fetch_assoc($q_kp)) {
            $clean_name = preg_replace('/^(REC\b\.?|CO\b\.?|PMCB\b\.?|LBS\b\.?|FCO\b\.?)\s*/i', '', $r_kp['nama_keypoint']);
            if ($clean_name === 'TOP') {
                $clean_name = 'PT TOP';
            }
            $kp_list[] = [
                'name' => $clean_name,
                'permanen' => (int)$r_kp['permanen'],
                'temporer' => (int)$r_kp['temporer']
            ];
        }
        // Urutkan alfabetis sesuai tampilan Excel
        usort($kp_list, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
    }
    $ulp_keypoint_data[$ulp_name] = $kp_list;
}

$keypoint_labels = [];
$keypoint_pmt = [];
$keypoint_rec = [];
$ulp_groups = [];

$curr_idx = 0;
foreach ($ulp_keypoint_data as $ulp_name => $kp_list) {
    $cnt = count($kp_list);
    if ($cnt > 0) {
        $ulp_groups[] = [
            'name' => $ulp_name,
            'startIndex' => $curr_idx,
            'endIndex' => $curr_idx + $cnt - 1,
            'count' => $cnt
        ];
        foreach ($kp_list as $kp) {
            $keypoint_labels[] = $kp['name'];
            $keypoint_pmt[] = $kp['permanen'];
            $keypoint_rec[] = $kp['temporer'];
            $curr_idx++;
        }
    }
}

// 5. Data Hari Tanpa Padam (Calendar Grid)
// Opsi Akumulasi: Jika bulan dipilih spesifik, tampilkan bulan tersebut. Jika "Semua Bulan", akumulasikan seluruh bulan.
$is_all_months = ($selected_bulan === 'ALL' || empty($selected_bulan) || !is_numeric($selected_bulan));
$is_all_years = ($selected_tahun === 'ALL' || empty($selected_tahun) || !is_numeric($selected_tahun));

if (!$is_all_months) {
    $grid_bulan = (int)$selected_bulan;
    $ref_tahun = !$is_all_years ? (int)$selected_tahun : (!empty($years[0]) ? (int)$years[0] : (int)date('Y'));
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $grid_bulan, $ref_tahun);
    $grid_month_name = isset($month_names[$grid_bulan]) ? $month_names[$grid_bulan] : 'Bulan ' . $grid_bulan;
    $grid_title_text = "Hari Tanpa Padam - " . $grid_month_name . " " . (!$is_all_years ? $selected_tahun : $ref_tahun);
} else {
    $days_in_month = 31; // Representasi tanggal 1 s/d 31 sepanjang tahun
    $grid_title_text = "Hari Tanpa Padam - Akumulasi " . (!$is_all_years ? ("Tahun " . $selected_tahun) : "Semua Tahun");
}

// Filter kalender: mendeteksi SEMUA gangguan Permanen & Temporer
$grid_where_clauses = ["g.tglgangguan > '2000-01-01 00:00:00'"];

if (!$is_all_years) {
    $grid_where_clauses[] = "YEAR(g.tglgangguan) = " . (int)$selected_tahun;
}

if (!$is_all_months) {
    $grid_where_clauses[] = "MONTH(g.tglgangguan) = " . (int)$selected_bulan;
}

if ($selected_unit !== 'ALL' && !empty($selected_unit) && $selected_unit !== '5125') {
    $grid_where_clauses[] = "g.unit = '" . mysql_real_escape_string($selected_unit) . "'";
}

$grid_where_sql = "WHERE " . implode(" AND ", $grid_where_clauses);

$outage_days = [];
$outage_details = [];
$q_outages = mysql_query("
    SELECT g.unit, DAY(g.tglgangguan) as hari, g.kategorigangguan, COUNT(*) as jml
    FROM datagangguan g
    $grid_where_sql
    GROUP BY g.unit, DAY(g.tglgangguan), g.kategorigangguan
");
if ($q_outages) {
    while ($row = mysql_fetch_assoc($q_outages)) {
        $raw_unit = $row['unit'];
        $day = (int)$row['hari'];
        $kat = !empty($row['kategorigangguan']) ? $row['kategorigangguan'] : 'GANGGUAN';
        $jml = (int)$row['jml'];
        
        $ulp_name = '';
        if ($raw_unit == 51540) $ulp_name = 'PONOROGO';
        elseif ($raw_unit == 51541) $ulp_name = 'BALONG';
        elseif ($raw_unit == 51542) $ulp_name = 'PACITAN';
        elseif ($raw_unit == 51543) $ulp_name = 'TRENGGALEK';
        
        if ($ulp_name !== '') {
            $outage_days[$ulp_name][$day] = true;
            if (!isset($outage_details[$ulp_name][$day])) {
                $outage_details[$ulp_name][$day] = [];
            }
            $outage_details[$ulp_name][$day][] = "$jml $kat";
        }
    }
}

$selected_month_name = ($selected_bulan !== 'ALL' && isset($month_names[$selected_bulan])) ? $month_names[$selected_bulan] : 'Semua Bulan';
$selected_year_name = ($selected_tahun !== 'ALL') ? $selected_tahun : 'Semua Tahun';

// 8. Recloser Trip Data - queried directly from datagangguan table with joins (immune to view/definer issues)
$where_parts_rec = ["g.tglgangguan > '2000-01-01 00:00:00'"];

if ($selected_tahun !== 'ALL' && is_numeric($selected_tahun)) {
    $where_parts_rec[] = "YEAR(g.tglgangguan) = " . (int)$selected_tahun;
}

if ($selected_bulan !== 'ALL' && is_numeric($selected_bulan)) {
    $where_parts_rec[] = "MONTH(g.tglgangguan) = " . (int)$selected_bulan;
}

if ($selected_unit !== 'ALL' && !empty($selected_unit) && $selected_unit !== '5125') {
    $where_parts_rec[] = "g.unit = '" . mysql_real_escape_string($selected_unit) . "'";
}

$where_sql_rec = "WHERE " . implode(" AND ", $where_parts_rec);

// Find latest date separately for Temporer & Permanen (for tambahan calculation)
$latest_date_temp = null;
$q_latest_temp = mysql_query("SELECT MAX(DATE(g.tglgangguan)) FROM datagangguan g $where_sql_rec AND UPPER(TRIM(g.kategorigangguan)) = 'TEMPORER'");
if ($q_latest_temp && mysql_num_rows($q_latest_temp) > 0) {
    $row_lt = mysql_fetch_array($q_latest_temp);
    $latest_date_temp = $row_lt[0];
}

$latest_date_perm = null;
$q_latest_perm = mysql_query("SELECT MAX(DATE(g.tglgangguan)) FROM datagangguan g $where_sql_rec AND UPPER(TRIM(g.kategorigangguan)) = 'PERMANEN'");
if ($q_latest_perm && mysql_num_rows($q_latest_perm) > 0) {
    $row_lp = mysql_fetch_array($q_latest_perm);
    $latest_date_perm = $row_lp[0];
}

// Build tambahan expressions safely based on each category's own latest date
$tambahan_expr_temp = !empty($latest_date_temp)
    ? "SUM(IF(DATE(g.tglgangguan) = '" . mysql_real_escape_string($latest_date_temp) . "', COALESCE(g.hitung, 1), 0))"
    : "0";

$tambahan_expr_perm = !empty($latest_date_perm)
    ? "SUM(IF(DATE(g.tglgangguan) = '" . mysql_real_escape_string($latest_date_perm) . "', COALESCE(g.hitung, 1), 0))"
    : "0";

// Query Temporer recloser trips
$q_temp = mysql_query("
    SELECT 
        IF(COALESCE(d.keterangan, '') != '', d.keterangan, CONCAT('PMT ', COALESCE(c.uraianpenyul, g.penyulang))) as recloser_name,
        CASE 
            WHEN g.unit = '51540' THEN 'PNG'
            WHEN g.unit = '51541' THEN 'BLG'
            WHEN g.unit = '51542' THEN 'PCT'
            WHEN g.unit = '51543' THEN 'TGK'
            ELSE 'UP3'
        END as ulp,
        SUM(COALESCE(g.hitung, 1)) as jumlah_trip,
        $tambahan_expr_temp as tambahan
    FROM datagangguan g
    LEFT JOIN kodepenyulang c ON g.penyulang = c.kodepenyul
    LEFT JOIN kodekeypoint d ON g.keypointid = d.idkeypoint
    $where_sql_rec AND UPPER(TRIM(g.kategorigangguan)) = 'TEMPORER'
    GROUP BY recloser_name, ulp
    ORDER BY jumlah_trip DESC, tambahan DESC
    LIMIT 10
");

// Query Permanen recloser trips
$q_perm = mysql_query("
    SELECT 
        IF(COALESCE(d.keterangan, '') != '', d.keterangan, CONCAT('PMT ', COALESCE(c.uraianpenyul, g.penyulang))) as recloser_name,
        CASE 
            WHEN g.unit = '51540' THEN 'PNG'
            WHEN g.unit = '51541' THEN 'BLG'
            WHEN g.unit = '51542' THEN 'PCT'
            WHEN g.unit = '51543' THEN 'TGK'
            ELSE 'UP3'
        END as ulp,
        SUM(COALESCE(g.hitung, 1)) as jumlah_trip,
        $tambahan_expr_perm as tambahan
    FROM datagangguan g
    LEFT JOIN kodepenyulang c ON g.penyulang = c.kodepenyul
    LEFT JOIN kodekeypoint d ON g.keypointid = d.idkeypoint
    $where_sql_rec AND UPPER(TRIM(g.kategorigangguan)) = 'PERMANEN'
    GROUP BY recloser_name, ulp
    ORDER BY jumlah_trip DESC, tambahan DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Visualisasi Gangguan - PLN</title>
  
  <!-- CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f4f6f9;
      color: #333;
      padding: 20px 20px 80px 20px !important;
      margin: 0 !important;
    }
    .page-title {
      font-weight: 700;
      color: #242c6d;
      margin-bottom: 20px;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      background-color: #ffffff;
      margin-bottom: 20px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }
    .metric-card {
      position: relative;
      overflow: hidden;
    }
    .metric-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
    }
    .border-primary-custom::before { background-color: #242c6d; }
    .border-danger-custom::before { background-color: #dc3545; }
    .border-success-custom::before { background-color: #28a745; }
    .border-warning-custom::before { background-color: #ffc107; }
    .border-info-custom::before { background-color: #17a2b8; }
    
    .metric-value {
      font-size: 28px;
      font-weight: 700;
      color: #242c6d;
      margin-bottom: 5px;
    }
    .metric-title {
      font-size: 13px;
      text-transform: uppercase;
      font-weight: 600;
      color: #6c757d;
      letter-spacing: 0.5px;
    }
    .metric-icon {
      font-size: 32px;
      opacity: 0.15;
      position: absolute;
      right: 15px;
      bottom: 15px;
    }
    .filter-card {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
    }
    .chart-container {
      position: relative;
      margin: auto;
      height: 280px;
      width: 100%;
    }
    .chart-title {
      font-size: 15px;
      font-weight: 600;
      color: #242c6d;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    /* Print & PDF Export 1-Page A4 Portrait */
    @page {
      size: A4 portrait;
      margin: 6mm;
    }
    @media print {
      html, body {
        height: 100% !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .no-print, .filter-card, .btn {
        display: none !important;
      }
      #content-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .page-title {
        font-size: 15px !important;
        margin-bottom: 2px !important;
      }
      .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
        margin-bottom: 5px !important;
        border-radius: 4px !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
      }
      .card-body {
        padding: 4px 6px !important;
      }
      .chart-title {
        font-size: 10px !important;
        padding-bottom: 2px !important;
        margin-bottom: 3px !important;
      }
      .metric-value {
        font-size: 15px !important;
        margin-bottom: 0 !important;
      }
      .metric-title {
        font-size: 8px !important;
      }
      .metric-icon {
        font-size: 16px !important;
        right: 6px !important;
        bottom: 4px !important;
      }
      .chart-container {
        height: 120px !important;
      }
      .chart-container[style*="min-width"] {
        min-width: 100% !important;
        height: 145px !important;
      }
      div[style*="overflow-x: auto"] {
        overflow: visible !important;
      }
      .table {
        font-size: 8px !important;
        margin-bottom: 0 !important;
      }
      .table th, .table td {
        padding: 1px 3px !important;
        height: auto !important;
      }
      .hari-tanpa-padam-table td {
        height: 14px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .hari-tanpa-padam-table th {
        padding: 1px !important;
        font-size: 8px !important;
      }
      .row {
        --bs-gutter-x: 6px;
        --bs-gutter-y: 5px;
      }
    }

  </style>

  <!-- JS Chart.js, html2canvas & jsPDF CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

<div id="content-wrapper">
  
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h3 class="page-title mb-1"><i class="fa fa-chart-line me-2"></i>Visualisasi Data Gangguan</h3>
      <span class="text-secondary small fw-bold">
        PLN UP3 Ponorogo &bull; Periode: <span class="badge bg-primary text-white"><?php echo $selected_month_name . ' ' . $selected_year_name; ?></span>
      </span>
    </div>
    <div class="no-print">
      <button type="button" class="btn btn-danger btn-sm shadow-sm d-flex align-items-center gap-1" id="btnExportPdf" onclick="exportToPdf()">
        <i class="fa fa-file-pdf"></i> <span>Export PDF</span>
      </button>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="card filter-card">
    <form method="GET" action="" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="tahun" class="form-label fw-semibold text-secondary small">Filter Tahun</label>
        <select class="form-select form-select-sm" id="tahun" name="tahun">
          <option value="ALL" <?php echo $selected_tahun == 'ALL' ? 'selected' : ''; ?>>Semua Tahun</option>
          <?php foreach ($years as $y): ?>
            <option value="<?php echo $y; ?>" <?php echo $selected_tahun == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label for="bulan" class="form-label fw-semibold text-secondary small">Filter Bulan</label>
        <select class="form-select form-select-sm" id="bulan" name="bulan">
          <option value="ALL" <?php echo $selected_bulan == 'ALL' ? 'selected' : ''; ?>>Semua Bulan</option>
          <?php foreach ($month_names as $m_num => $m_name): ?>
            <option value="<?php echo $m_num; ?>" <?php echo $selected_bulan == $m_num ? 'selected' : ''; ?>><?php echo $m_name; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label for="unit" class="form-label fw-semibold text-secondary small">Filter Unit / ULP</label>
        <select class="form-select form-select-sm" id="unit" name="unit">
          <option value="ALL" <?php echo $selected_unit == 'ALL' ? 'selected' : ''; ?>>Semua ULP / Unit</option>
          <?php foreach ($units as $u): ?>
            <option value="<?php echo $u['kodeunit']; ?>" <?php echo $selected_unit == $u['kodeunit'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['uraian']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-primary btn-sm" style="background-color: #242c6d; border-color: #242c6d;">
          <i class="fa fa-filter me-1"></i> Terapkan Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Metrics Row -->
  <div class="row">
    <!-- Card 1: Total Gangguan -->
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card metric-card border-primary-custom">
        <div class="card-body">
          <div class="metric-value"><?php echo number_format($total_gangguan); ?></div>
          <div class="metric-title">Total Gangguan</div>
          <i class="fa fa-bolt metric-icon text-primary"></i>
        </div>
      </div>
    </div>
    <!-- Card 2: Total PMT -->
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card metric-card border-danger-custom">
        <div class="card-body">
          <div class="metric-value"><?php echo number_format($total_pmt); ?></div>
          <div class="metric-title">Permanen</div>
          <i class="fa fa-toggle-off metric-icon text-danger"></i>
        </div>
      </div>
    </div>
    <!-- Card 3: Total REC -->
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card metric-card border-success-custom">
        <div class="card-body">
          <div class="metric-value"><?php echo number_format($total_rec); ?></div>
          <div class="metric-title">Temporer</div>
          <i class="fa fa-retweet metric-icon text-success"></i>
        </div>
      </div>
    </div>
    <!-- Card 4: Top Cause & Weather -->
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card metric-card border-warning-custom">
        <div class="card-body py-3">
          <div class="lh-1 mb-1">
            <span class="text-secondary small fw-bold uppercase">Penyebab:</span>
            <span class="fw-bold text-dark text-truncate d-block" style="font-size: 13px; max-width: 170px;"><?php echo $top_cause; ?> (<?php echo $top_cause_count; ?>)</span>
          </div>
          <div class="lh-1">
            <span class="text-secondary small fw-bold uppercase">Cuaca Terbanyak:</span>
            <span class="fw-bold text-dark text-truncate d-block" style="font-size: 13px; max-width: 170px;"><?php echo $top_weather; ?></span>
          </div>
          <i class="fa fa-cloud-showers-heavy metric-icon text-warning"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 1: Stacked Horizontal Bar Chart & Calendar Grid -->
  <div class="row g-4 mb-4">
    <!-- Top ULP Gangguan Permanen & Temporer -->
    <div class="col-lg-6 col-md-12 d-flex">
      <div class="card h-100 w-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <div class="chart-title">
              <i class="fa fa-align-left me-2"></i>Top ULP Gangguan Permanen & Temporer
            </div>
            <div class="chart-container" style="height: 320px; margin-top: 15px;">
              <canvas id="ulpStackedChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Hari Tanpa Padam Grid -->
    <div class="col-lg-6 col-md-12 d-flex">
      <div class="card h-100 w-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <div class="chart-title">
              <i class="fa fa-calendar-alt me-2"></i><?php echo $grid_title_text; ?>
            </div>
            <div class="table-responsive" style="margin-top: 25px;">
              <table class="table table-bordered text-center align-middle hari-tanpa-padam-table" style="font-size: 11px; margin-bottom: 0;">
                <thead>
                  <tr class="table-dark">
                    <th style="min-width: 90px; text-align: left; font-size: 10px;">ULP</th>
                    <?php for ($d = 1; $d <= $days_in_month; $d++): ?>
                      <th style="padding: 3px !important; font-size: 9px;"><?php echo $d; ?></th>
                    <?php endfor; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (['BALONG', 'PACITAN', 'PONOROGO', 'TRENGGALEK'] as $ulp): ?>
                    <tr>
                      <td class="fw-bold text-start" style="padding: 5px !important; font-size: 10px; height: 42px;"><?php echo $ulp; ?></td>
                      <?php for ($d = 1; $d <= $days_in_month; $d++): ?>
                        <?php 
                          $has_outage = isset($outage_days[$ulp][$d]);
                          $bg_color = $has_outage ? '#dc3545' : '#ffc107'; // Red vs Yellow
                          $detail_txt = $has_outage ? ('Ada Gangguan: ' . implode(', ', $outage_details[$ulp][$d])) : 'Tanpa Padam / Gangguan';
                        ?>
                        <td style="background-color: <?php echo $bg_color; ?>; padding: 0 !important; height: 42px;" 
                            title="<?php echo $ulp . ' - Tanggal ' . $d . ': ' . $detail_txt; ?>">
                          <!-- Empty space to show color -->
                        </td>
                      <?php endfor; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-center gap-3 mt-3" style="font-size: 11px;">
            <div class="d-flex align-items-center"><span class="d-inline-block rounded-1 me-1" style="width:12px; height:12px; background-color:#ffc107;"></span> Tanpa Padam / Gangguan</div>
            <div class="d-flex align-items-center"><span class="d-inline-block rounded-1 me-1" style="width:12px; height:12px; background-color:#dc3545;"></span> Ada Gangguan (Permanen / Temporer)</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: Monthly Permanen & Temporer per ULP -->
  <div class="row">
    <!-- Gangguan Permanen per ULP -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title">
            <i class="fa fa-ban me-2"></i>Gangguan Permanen Per ULP
          </div>
          <div class="chart-container" style="height: 300px;">
            <canvas id="monthlyPmtChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Gangguan Temporer per ULP -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title">
            <i class="fa fa-clock me-2"></i>Gangguan Temporer Per ULP
          </div>
          <div class="chart-container" style="height: 300px;">
            <canvas id="monthlyRecChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 3: Trend Gangguan 3 Top Skor Temporer & Permanen -->
  <div class="row">
    <div class="col-lg-12 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title">
            <i class="fa fa-chart-bar me-2"></i>Trend Gangguan 3 Top Skor Temporer & Permanen - <?php echo $selected_month_name . ' ' . $selected_year_name; ?>
          </div>
          <div style="width: 100%; overflow-x: auto;">
            <div class="chart-container" style="height: 420px; min-width: 1100px;">
              <canvas id="keypointChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 4: Recloser Trip Tertinggi -->
  <div class="row g-4 mb-4">
    <!-- 10 Temporer Recloser Trip Tertinggi -->
    <div class="col-lg-6 col-md-12 d-flex">
      <div class="card h-100 w-100">
        <div class="card-body">
          <div class="chart-title">
            <i class="fa fa-retweet me-2"></i>10 Temporer Recloser Trip Tertinggi (<?php echo $selected_month_name . ' ' . $selected_year_name; ?>)
          </div>
          <div class="table-responsive" style="margin-top: 15px;">
            <table class="table table-striped table-bordered text-center align-middle" style="font-size: 13px;">
              <thead class="table-dark">
                <tr>
                  <th style="width: 8%;">No</th>
                  <th>Recloser</th>
                  <th style="width: 15%;">ULP</th>
                  <th style="width: 25%; background-color: #242c6d; color: white;">Jumlah Trip</th>
                  <th style="width: 20%;">Tambahan</th>
                  <th style="width: 15%; font-weight: bold;">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no_temp = 1;
                while ($r = mysql_fetch_assoc($q_temp)):
                  $tambahan_temp = (int)$r['tambahan'];
                  $jumlah_trip_temp = (int)$r['jumlah_trip'] - $tambahan_temp;
                  $total = (int)$r['jumlah_trip'];
                ?>
                  <tr>
                    <td><?php echo $no_temp++; ?></td>
                    <td class="text-start fw-bold"><?php echo htmlspecialchars($r['recloser_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['ulp']); ?></span></td>
                    <td style="background-color: #fcf8e3; font-weight: bold; color: #242c6d;"><?php echo $jumlah_trip_temp; ?></td>
                    <td><?php echo $tambahan_temp > 0 ? '<span class="badge bg-warning text-dark">+' . $tambahan_temp . '</span>' : '-'; ?></td>
                    <td class="fw-bold text-primary"><?php echo $total; ?></td>
                  </tr>
                <?php endwhile; ?>
                <?php if ($no_temp == 1): ?>
                  <tr>
                    <td colspan="6" class="text-muted">Tidak ada data untuk filter bulan/tahun ini.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 10 Permanen Recloser Trip Tertinggi -->
    <div class="col-lg-6 col-md-12 d-flex">
      <div class="card h-100 w-100">
        <div class="card-body">
          <div class="chart-title">
            <i class="fa fa-ban me-2"></i>10 Permanen Recloser Trip Tertinggi (<?php echo $selected_month_name . ' ' . $selected_year_name; ?>)
          </div>
          <div class="table-responsive" style="margin-top: 15px;">
            <table class="table table-striped table-bordered text-center align-middle" style="font-size: 13px;">
              <thead class="table-dark">
                <tr>
                  <th style="width: 8%;">No</th>
                  <th>Recloser</th>
                  <th style="width: 15%;">ULP</th>
                  <th style="width: 25%; background-color: #dc3545; color: white;">Jumlah Trip</th>
                  <th style="width: 20%;">Tambahan</th>
                  <th style="width: 15%; font-weight: bold;">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no_perm = 1;
                while ($r = mysql_fetch_assoc($q_perm)):
                  $tambahan_perm = (int)$r['tambahan'];
                  $jumlah_trip_perm = (int)$r['jumlah_trip'] - $tambahan_perm;
                  $total = (int)$r['jumlah_trip'];
                  $bg_class = ($no_perm == 1) ? 'style="background-color: #f8d7da; color: #721c24;"' : '';
                ?>
                  <tr <?php echo $bg_class; ?>>
                    <td><?php echo $no_perm++; ?></td>
                    <td class="text-start fw-bold"><?php echo htmlspecialchars($r['recloser_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['ulp']); ?></span></td>
                    <td style="background-color: #fcf8e3; font-weight: bold; color: #dc3545;"><?php echo $jumlah_trip_perm; ?></td>
                    <td><?php echo $tambahan_perm > 0 ? '<span class="badge bg-danger">+' . $tambahan_perm . '</span>' : '-'; ?></td>
                    <td class="fw-bold text-danger"><?php echo $total; ?></td>
                  </tr>
                <?php endwhile; ?>
                <?php if ($no_perm == 1): ?>
                  <tr>
                    <td colspan="6" class="text-muted">Tidak ada data untuk filter bulan/tahun ini.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  // Colors setup
  const primaryColor = '#242c6d';
  const orangeColor = '#fd7e14';
  const greenColor = '#28a745';
  
  // 1. Top ULP Gangguan Permanen & Temporer Stacked Horizontal Chart
  const ctxUlpStacked = document.getElementById('ulpStackedChart').getContext('2d');
  new Chart(ctxUlpStacked, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode(!empty($ulp_labels) ? $ulp_labels : array_column($ulp_stats, 'label')); ?>,
      datasets: [
        {
          label: 'Temporer',
          data: <?php echo json_encode(!empty($ulp_temporer) ? $ulp_temporer : array_column($ulp_stats, 'temporer')); ?>,
          backgroundColor: primaryColor,
          borderRadius: 4
        },
        {
          label: 'Permanen',
          data: <?php echo json_encode(!empty($ulp_permanen) ? $ulp_permanen : array_column($ulp_stats, 'permanen')); ?>,
          backgroundColor: orangeColor,
          borderRadius: 4
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 12, font: { size: 11 } }
        }
      },
      scales: {
        x: {
          stacked: true,
          beginAtZero: true,
          ticks: { stepSize: 1 }
        },
        y: {
          stacked: true
        }
      }
    }
  });

  // 2. Gangguan Permanen Per ULP Chart
  const ctxMonthlyPmt = document.getElementById('monthlyPmtChart').getContext('2d');
  new Chart(ctxMonthlyPmt, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($chart_ulps); ?>,
      datasets: <?php echo json_encode($datasets_pmt); ?>
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, font: { size: 10 } }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });

  // 3. Gangguan Temporer Per ULP Chart
  const ctxMonthlyRec = document.getElementById('monthlyRecChart').getContext('2d');
  new Chart(ctxMonthlyRec, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($chart_ulps); ?>,
      datasets: <?php echo json_encode($datasets_rec); ?>
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, font: { size: 10 } }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });

  // 4. Trend Gangguan 3 Top Skor Keypoint Chart (Grouped by ULP)
  const ulpGroups = <?php echo json_encode($ulp_groups); ?>;

  // Custom plugin to show values on top of the bars
  const topValuesPlugin = {
    id: 'topValues',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      ctx.save();
      ctx.font = 'bold 9px sans-serif';
      ctx.fillStyle = '#444';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'bottom';
      
      chart.data.datasets.forEach((dataset, datasetIndex) => {
        const meta = chart.getDatasetMeta(datasetIndex);
        meta.data.forEach((bar, index) => {
          const val = dataset.data[index];
          if (val > 0) {
            ctx.fillText(val, bar.x, bar.y - 2);
          }
        });
      });
      ctx.restore();
    }
  };

  // Custom plugin to render lower-tier ULP group labels with divider lines (matching Excel)
  const ulpGroupPlugin = {
    id: 'ulpGroups',
    afterDraw(chart) {
      const { ctx, chartArea, scales: { x } } = chart;
      if (!ulpGroups || ulpGroups.length === 0) return;

      ctx.save();
      
      const tierHeight = 28;
      const tierBottom = x.bottom - 4;
      const tierTop = tierBottom - tierHeight;
      const textY = tierTop + (tierHeight / 2);

      // Garis horizontal pembatas antara ticks dan grup ULP
      ctx.strokeStyle = '#d0d4dc';
      ctx.lineWidth = 1;
      
      ctx.beginPath();
      ctx.moveTo(chartArea.left, tierTop);
      ctx.lineTo(chartArea.right, tierTop);
      ctx.stroke();

      // Garis horizontal paling bawah grup ULP
      ctx.beginPath();
      ctx.moveTo(chartArea.left, tierBottom);
      ctx.lineTo(chartArea.right, tierBottom);
      ctx.stroke();

      ctx.font = 'bold 11px "Segoe UI", sans-serif';
      ctx.fillStyle = '#495057';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';

      const tickCount = x.ticks.length;
      const halfBarWidth = (tickCount > 1) 
        ? (x.getPixelForTick(1) - x.getPixelForTick(0)) / 2 
        : (chartArea.width / 2);

      ulpGroups.forEach((grp, idx) => {
        const startX = x.getPixelForTick(grp.startIndex) - halfBarWidth;
        const endX = x.getPixelForTick(grp.endIndex) + halfBarWidth;
        const centerX = (startX + endX) / 2;

        // Render teks nama ULP di tengah area kelompoknya
        ctx.fillText(grp.name, centerX, textY);

        // Garis vertikal pemisah di sisi kanan grup (jika bukan yang terakhir)
        if (idx < ulpGroups.length - 1) {
          // Garis putus-putus lembut memanjang ke atas grafik
          ctx.beginPath();
          ctx.moveTo(endX, chartArea.top);
          ctx.lineTo(endX, tierBottom);
          ctx.strokeStyle = '#e2e6ea';
          ctx.setLineDash([3, 3]);
          ctx.stroke();

          // Garis solid pembatas di kotak tier ULP
          ctx.beginPath();
          ctx.moveTo(endX, tierTop);
          ctx.lineTo(endX, tierBottom);
          ctx.strokeStyle = '#adb5bd';
          ctx.setLineDash([]);
          ctx.stroke();
        }
      });

      // Garis vertikal di ujung paling kiri dan kanan
      ctx.beginPath();
      ctx.moveTo(chartArea.left, tierTop);
      ctx.lineTo(chartArea.left, tierBottom);
      ctx.moveTo(chartArea.right, tierTop);
      ctx.lineTo(chartArea.right, tierBottom);
      ctx.strokeStyle = '#adb5bd';
      ctx.setLineDash([]);
      ctx.stroke();

      ctx.restore();
    }
  };

  const ctxKeypoint = document.getElementById('keypointChart').getContext('2d');
  new Chart(ctxKeypoint, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($keypoint_labels); ?>,
      datasets: [
        {
          label: 'Permanen',
          data: <?php echo json_encode($keypoint_pmt); ?>,
          backgroundColor: '#4472c4', // Excel Royal Blue
          borderRadius: 2,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        },
        {
          label: 'Temporer',
          data: <?php echo json_encode($keypoint_rec); ?>,
          backgroundColor: '#ed7d31', // Excel Warm Orange
          borderRadius: 2,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        }
      ]
    },
    plugins: [topValuesPlugin, ulpGroupPlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 12, font: { size: 11 }, padding: 15 }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        },
        x: {
          ticks: {
            font: { size: 10, weight: '500' },
            color: '#333',
            maxRotation: 90,
            minRotation: 90,
            autoSkip: false
          },
          grid: {
            display: false
          },
          afterFit: (scale) => {
            scale.height += 35; // Memberikan ruang di bawah rotated ticks untuk tier nama ULP
          }
        }
      }
    }
  });

  // Fungsi Export PDF menjadi tepat 1 Halaman Kertas A4 Landscape
  async function exportToPdf() {
    const btn = document.getElementById('btnExportPdf');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span>Menyiapkan 1 Halaman A4...</span>';
    btn.disabled = true;

    // Sembunyikan tombol & filter agar hasil bersih
    const noPrintElements = document.querySelectorAll('.no-print, .filter-card');
    noPrintElements.forEach(el => el.style.display = 'none');

    const wrapper = document.getElementById('content-wrapper');

    try {
      // Tangkap seluruh visualisasi dashboard secara utuh tanpa distorsi
      const canvas = await html2canvas(wrapper, {
        scale: 2, // Resolusi jernih tajam
        useCORS: true,
        backgroundColor: '#ffffff',
        logging: false,
        scrollX: 0,
        scrollY: 0
      });

      const imgData = canvas.toDataURL('image/jpeg', 0.98);

      // Inisialisasi dokumen jsPDF format A4 Portrait (210 x 297 mm)
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });

      const pageWidth = 210;
      const pageHeight = 297;
      const margin = 6; // margin 6mm di tepi kertas
      const maxWidth = pageWidth - (margin * 2);   // 198mm
      const maxHeight = pageHeight - (margin * 2);  // 285mm

      // Hitung skala proporsional agar memenuhi 1 lembar A4 Portrait
      const canvasRatio = canvas.width / canvas.height;
      const pageRatio = maxWidth / maxHeight;

      let finalWidth, finalHeight;
      if (canvasRatio > pageRatio) {
        finalWidth = maxWidth;
        finalHeight = finalWidth / canvasRatio;
      } else {
        finalHeight = maxHeight;
        finalWidth = finalHeight * canvasRatio;
      }

      // Posisikan tepat di tengah lembar A4 Portrait
      const x = margin + (maxWidth - finalWidth) / 2;
      const y = margin + (maxHeight - finalHeight) / 2;

      // Masukkan gambar ke halaman tunggal (Page 1)
      pdf.addImage(imgData, 'JPEG', x, y, finalWidth, finalHeight);

      const fileName = 'Visualisasi_Gangguan_PLN_<?php echo preg_replace('/[^a-zA-Z0-9_-]/', '_', $selected_month_name . '_' . $selected_year_name); ?>.pdf';
      pdf.save(fileName);
    } catch (err) {
      console.error('PDF export error:', err);
      alert('Gagal mengekspor PDF. Silakan coba lagi.');
    } finally {
      noPrintElements.forEach(el => el.style.display = '');
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    }
  }
</script>
</body>
</html>
