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
if ($selected_unit !== 'ALL' && !empty($selected_unit)) {
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

// Total PMT
$pmt_clauses = $where_clauses;
$pmt_clauses[] = "g.kat_gangguan = 'PMT'";
$pmt_where_sql = "WHERE " . implode(" AND ", $pmt_clauses);
$q_pmt = mysql_query("SELECT COUNT(*) as total FROM datagangguan g $pmt_where_sql");
$r_pmt = mysql_fetch_assoc($q_pmt);
$total_pmt = $r_pmt['total'];

// Total REC
$rec_clauses = $where_clauses;
$rec_clauses[] = "g.kat_gangguan = 'REC'";
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
$q_ulp_stats = mysql_query("
    SELECT u.uraian, g.unit,
           SUM(CASE WHEN g.kat_gangguan = 'PMT' THEN 1 ELSE 0 END) as permanen,
           SUM(CASE WHEN g.kat_gangguan = 'REC' THEN 1 ELSE 0 END) as temporer
    FROM datagangguan g
    JOIN kodeunit u ON g.unit = u.kodeunit
    $where_sql
    GROUP BY g.unit, u.uraian
    ORDER BY (permanen + temporer) DESC
");
while ($row = mysql_fetch_assoc($q_ulp_stats)) {
    $name = $row['uraian'];
    if (strpos(strtoupper($name), 'TRENGGALEK') !== false) $short = 'TGK';
    elseif (strpos(strtoupper($name), 'PONOROGO') !== false && strpos(strtoupper($name), 'ULP') !== false) $short = 'PNG';
    elseif (strpos(strtoupper($name), 'PACITAN') !== false) $short = 'PCT';
    elseif (strpos(strtoupper($name), 'BALONG') !== false) $short = 'BLG';
    else $short = $name;

    $ulp_stats[] = [
        'label' => $short,
        'permanen' => (int)$row['permanen'],
        'temporer' => (int)$row['temporer']
    ];
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
    SELECT u.uraian, MONTH(g.tglgangguan) as bulan,
           SUM(CASE WHEN g.kat_gangguan = 'PMT' THEN 1 ELSE 0 END) as permanen,
           SUM(CASE WHEN g.kat_gangguan = 'REC' THEN 1 ELSE 0 END) as temporer
    FROM datagangguan g
    JOIN kodeunit u ON g.unit = u.kodeunit
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
        SELECT k.keterangan as nama_keypoint, k.kodepenyul,
               (
                   SELECT COUNT(*) 
                   FROM datagangguan g 
                   $where_sql AND g.keypointid = k.idkeypoint AND g.kat_gangguan = 'REC'
               ) as temporer,
               (
                   SELECT COUNT(*) 
                   FROM datagangguan g 
                   $where_sql AND g.penyulang = k.kodepenyul AND g.kat_gangguan = 'PMT'
               ) as permanen
        FROM kodekeypoint k
        WHERE k.unit = '$ulp_id' 
          AND k.idkeypoint IN (
              SELECT DISTINCT keypointid 
              FROM datagangguan g
              $where_sql AND g.keypointid != '' AND g.keypointid != '0'
          )
        ORDER BY (temporer + permanen) DESC
        LIMIT 7
    ");
    
    $kp_list = [];
    if ($q_kp) {
        while ($r_kp = mysql_fetch_assoc($q_kp)) {
            $clean_name = preg_replace('/^(REC\b\.?|CO\b\.?|PMCB\b\.?|LBS\b\.?)\s*/i', '', $r_kp['nama_keypoint']);
            $kp_list[] = [
                'name' => $clean_name,
                'permanen' => (int)$r_kp['permanen'],
                'temporer' => (int)$r_kp['temporer']
            ];
        }
    }
    $ulp_keypoint_data[$ulp_name] = $kp_list;
}

$keypoint_labels = [];
$keypoint_pmt = [];
$keypoint_rec = [];

foreach ($ulp_keypoint_data as $ulp_name => $kp_list) {
    foreach ($kp_list as $kp) {
        $keypoint_labels[] = [$kp['name'], $ulp_name];
        $keypoint_pmt[] = $kp['permanen'];
        $keypoint_rec[] = $kp['temporer'];
    }
}

// 5. Data Hari Tanpa Padam (Calendar Grid)
$days_in_month = 31;
if ($selected_bulan !== 'ALL' && is_numeric($selected_bulan)) {
    $year = ($selected_tahun !== 'ALL' && is_numeric($selected_tahun)) ? (int)$selected_tahun : (int)date('Y');
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)$selected_bulan, $year);
} else {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)date('m'), ($selected_tahun !== 'ALL' && is_numeric($selected_tahun)) ? (int)$selected_tahun : (int)date('Y'));
}

$outage_days = [];
$q_outages = mysql_query("
    SELECT g.unit, DAY(g.tglgangguan) as hari
    FROM datagangguan g
    $where_sql AND g.kat_gangguan = 'PMT'
");
if ($q_outages) {
    while ($row = mysql_fetch_assoc($q_outages)) {
        $raw_unit = $row['unit'];
        $day = (int)$row['hari'];
        
        $ulp_name = '';
        if ($raw_unit == 51540) $ulp_name = 'PONOROGO';
        elseif ($raw_unit == 51541) $ulp_name = 'BALONG';
        elseif ($raw_unit == 51542) $ulp_name = 'PACITAN';
        elseif ($raw_unit == 51543) $ulp_name = 'TRENGGALEK';
        
        if ($ulp_name !== '') {
            $outage_days[$ulp_name][$day] = true;
        }
    }
}

$selected_month_name = ($selected_bulan !== 'ALL' && isset($month_names[$selected_bulan])) ? $month_names[$selected_bulan] : 'Bulan Ini';
$selected_year_name = ($selected_tahun !== 'ALL') ? $selected_tahun : date('Y');

// 8. Recloser Trip Data (Queried dynamically from monthly monitoring view v_datagangguan)
$where_parts_temp = [];

if ($selected_tahun !== 'ALL' && is_numeric($selected_tahun)) {
    $where_parts_temp[] = "YEAR(tglgangguan) = " . (int)$selected_tahun;
} else {
    // If no year selected, default to current year
    $where_parts_temp[] = "YEAR(tglgangguan) = " . (int)date('Y');
}

if ($selected_bulan !== 'ALL' && is_numeric($selected_bulan)) {
    $where_parts_temp[] = "MONTH(tglgangguan) = " . (int)$selected_bulan;
}

if ($selected_unit !== 'ALL') {
    $where_parts_temp[] = "unit = '" . mysql_real_escape_string($selected_unit) . "'";
}

$where_sql_temp = "";
if (!empty($where_parts_temp)) {
    $where_sql_temp = "WHERE " . implode(" AND ", $where_parts_temp);
} else {
    $where_sql_temp = "WHERE 1=1";
}

// Find latest date in selected filter range
$latest_date = null;
$q_latest_date = mysql_query("SELECT MAX(DATE(tglgangguan)) FROM v_datagangguan $where_sql_temp");
if ($q_latest_date && mysql_num_rows($q_latest_date) > 0) {
    $row_date = mysql_fetch_array($q_latest_date);
    $latest_date = $row_date[0];
}

$q_temp = mysql_query("
    SELECT 
        IF(keterangan != '', keterangan, CONCAT('PMT ', uraianpenyul)) as recloser_name,
        CASE 
            WHEN unit = '51540' THEN 'PNG'
            WHEN unit = '51541' THEN 'BLG'
            WHEN unit = '51542' THEN 'PCT'
            WHEN unit = '51543' THEN 'TGK'
            ELSE 'UP3'
        END as ulp,
        SUM(hitung) as total,
        SUM(IF(DATE(tglgangguan) = '$latest_date', hitung, 0)) as tambahan
    FROM v_datagangguan
    $where_sql_temp AND kategorigangguan = 'TEMPORER'
    GROUP BY recloser_name, ulp
    ORDER BY total DESC
    LIMIT 10
");

$q_perm = mysql_query("
    SELECT 
        IF(keterangan != '', keterangan, CONCAT('PMT ', uraianpenyul)) as recloser_name,
        CASE 
            WHEN unit = '51540' THEN 'PNG'
            WHEN unit = '51541' THEN 'BLG'
            WHEN unit = '51542' THEN 'PCT'
            WHEN unit = '51543' THEN 'TGK'
            ELSE 'UP3'
        END as ulp,
        SUM(hitung) as total,
        SUM(IF(DATE(tglgangguan) = '$latest_date', hitung, 0)) as tambahan
    FROM v_datagangguan
    $where_sql_temp AND kategorigangguan = 'PERMANEN'
    GROUP BY recloser_name, ulp
    ORDER BY total DESC
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
  </style>

  <!-- JS Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div id="content-wrapper">
  
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="page-title"><i class="fa fa-chart-line me-2"></i>Visualisasi Data Gangguan</h3>
    <span class="text-secondary small fw-bold">PLN UP3 Ponorogo</span>
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
          <div class="metric-title">Permanen (PMT)</div>
          <i class="fa fa-toggle-off metric-icon text-danger"></i>
        </div>
      </div>
    </div>
    <!-- Card 3: Total REC -->
    <div class="col-lg-3 col-md-6 col-sm-12">
      <div class="card metric-card border-success-custom">
        <div class="card-body">
          <div class="metric-value"><?php echo number_format($total_rec); ?></div>
          <div class="metric-title">Temporer (REC/PMCB)</div>
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
              <i class="fa fa-calendar-alt me-2"></i>Hari Tanpa Padam - <?php echo $selected_month_name . ' ' . $selected_year_name; ?>
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
                  <?php foreach (['PONOROGO', 'BALONG', 'PACITAN', 'TRENGGALEK'] as $ulp): ?>
                    <tr>
                      <td class="fw-bold text-start" style="padding: 5px !important; font-size: 10px; height: 42px;"><?php echo $ulp; ?></td>
                      <?php for ($d = 1; $d <= $days_in_month; $d++): ?>
                        <?php 
                          $has_outage = isset($outage_days[$ulp][$d]);
                          $bg_color = $has_outage ? '#dc3545' : '#ffc107'; // Red vs Yellow
                        ?>
                        <td style="background-color: <?php echo $bg_color; ?>; padding: 0 !important; height: 42px;" 
                            title="<?php echo $ulp . ' - Tanggal ' . $d . ': ' . ($has_outage ? 'Ada Padam (PMT)' : 'Tanpa Padam'); ?>">
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
            <div class="d-flex align-items-center"><span class="d-inline-block rounded-1 me-1" style="width:12px; height:12px; background-color:#ffc107;"></span> Tanpa Padam</div>
            <div class="d-flex align-items-center"><span class="d-inline-block rounded-1 me-1" style="width:12px; height:12px; background-color:#dc3545;"></span> Ada Padam (PMT)</div>
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
          <div class="chart-container" style="height: 350px;">
            <canvas id="keypointChart"></canvas>
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
                  <th style="width: 25%; background-color: #242c6d; color: white;"><?php echo htmlspecialchars(strtoupper($selected_month_name)); ?></th>
                  <th style="width: 20%;">Tambahan</th>
                  <th style="width: 15%; font-weight: bold;">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no_temp = 1;
                while ($r = mysql_fetch_assoc($q_temp)):
                  $tambahan = (int)$r['tambahan'];
                  $baseline = (int)$r['total'] - $tambahan;
                ?>
                  <tr>
                    <td><?php echo $no_temp++; ?></td>
                    <td class="text-start fw-bold"><?php echo htmlspecialchars($r['recloser_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['ulp']); ?></span></td>
                    <td style="background-color: #fcf8e3; font-weight: bold; color: #242c6d;"><?php echo $baseline; ?></td>
                    <td><?php echo $tambahan > 0 ? '<span class="badge bg-warning text-dark">+' . $tambahan . '</span>' : '-'; ?></td>
                    <td class="fw-bold text-primary"><?php echo $r['total']; ?></td>
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
                  <th style="width: 25%; background-color: #dc3545; color: white;"><?php echo htmlspecialchars(strtoupper($selected_month_name)); ?></th>
                  <th style="width: 20%;">Tambahan</th>
                  <th style="width: 15%; font-weight: bold;">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no_perm = 1;
                while ($r = mysql_fetch_assoc($q_perm)):
                  $tambahan = (int)$r['tambahan'];
                  $baseline = (int)$r['total'] - $tambahan;
                  $bg_class = ($no_perm == 1) ? 'style="background-color: #f8d7da; color: #721c24;"' : '';
                ?>
                  <tr <?php echo $bg_class; ?>>
                    <td><?php echo $no_perm++; ?></td>
                    <td class="text-start fw-bold"><?php echo htmlspecialchars($r['recloser_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['ulp']); ?></span></td>
                    <td style="background-color: #fcf8e3; font-weight: bold; color: #dc3545;"><?php echo $baseline; ?></td>
                    <td><?php echo $tambahan > 0 ? '<span class="badge bg-danger">+' . $tambahan . '</span>' : '-'; ?></td>
                    <td class="fw-bold text-danger"><?php echo $r['total']; ?></td>
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
      labels: <?php echo json_encode(array_column($ulp_stats, 'label')); ?>,
      datasets: [
        {
          label: 'Temporer',
          data: <?php echo json_encode(array_column($ulp_stats, 'temporer')); ?>,
          backgroundColor: primaryColor,
          borderRadius: 4
        },
        {
          label: 'Permanen',
          data: <?php echo json_encode(array_column($ulp_stats, 'permanen')); ?>,
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

  const ctxKeypoint = document.getElementById('keypointChart').getContext('2d');
  new Chart(ctxKeypoint, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($keypoint_labels); ?>,
      datasets: [
        {
          label: 'Temporer',
          data: <?php echo json_encode($keypoint_rec); ?>,
          backgroundColor: '#3a75c4', // Slate Blue
          borderRadius: 2,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        },
        {
          label: 'Permanen',
          data: <?php echo json_encode($keypoint_pmt); ?>,
          backgroundColor: '#f28e2b', // Orange
          borderRadius: 2,
          barPercentage: 0.8,
          categoryPercentage: 0.7
        }
      ]
    },
    plugins: [topValuesPlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 12, font: { size: 11 } }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        },
        x: {
          ticks: {
            font: { size: 10 },
            maxRotation: 90,
            minRotation: 90,
            autoSkip: false
          }
        }
      }
    }
  });
</script>
</body>
</html>
