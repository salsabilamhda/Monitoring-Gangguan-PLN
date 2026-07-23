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
$selected_unit = isset($_REQUEST['unit']) ? $_REQUEST['unit'] : 'ALL';

// Construct WHERE clauses
$where_clauses = ["g.tglgangguan > '2000-01-01 00:00:00'"];
if ($selected_tahun !== 'ALL' && !empty($selected_tahun)) {
    $where_clauses[] = "YEAR(g.tglgangguan) = '" . mysql_real_escape_string($selected_tahun) . "'";
}
if ($selected_unit !== 'ALL' && !empty($selected_unit)) {
    $where_clauses[] = "g.unit = '" . mysql_real_escape_string($selected_unit) . "'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
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

// 2. Trend Bulanan Data
$monthly_trend = array_fill(1, 12, 0);
$q_monthly = mysql_query("
    SELECT MONTH(g.tglgangguan) as bulan, COUNT(*) as jumlah 
    FROM datagangguan g 
    $where_sql 
    GROUP BY MONTH(g.tglgangguan)
");
while ($row = mysql_fetch_assoc($q_monthly)) {
    $monthly_trend[(int)$row['bulan']] = (int)$row['jumlah'];
}
$monthly_trend_values = array_values($monthly_trend);

// 3. Kategori Gangguan Data
$kat_labels = [];
$kat_values = [];
$q_kat = mysql_query("
    SELECT g.kat_gangguan, COUNT(*) as jumlah 
    FROM datagangguan g 
    $where_sql 
    GROUP BY g.kat_gangguan
");
while ($row = mysql_fetch_assoc($q_kat)) {
    $kat_labels[] = $row['kat_gangguan'] == 'REC' ? 'REC / PMCB' : $row['kat_gangguan'];
    $kat_values[] = (int)$row['jumlah'];
}

// 4. Top 10 Penyebab Gangguan Data
$cause_labels = [];
$cause_values = [];
$q_cause = mysql_query("
    SELECT j.uraianjenisgangguan, COUNT(*) as jumlah 
    FROM datagangguan g 
    JOIN kodejenisgangguan j ON g.jeniskode = j.idjenisgangguan 
    $where_sql 
    GROUP BY g.jeniskode, j.uraianjenisgangguan 
    ORDER BY jumlah DESC 
    LIMIT 10
");
while ($row = mysql_fetch_assoc($q_cause)) {
    $cause_labels[] = $row['uraianjenisgangguan'];
    $cause_values[] = (int)$row['jumlah'];
}

// 5. Gangguan per Unit / ULP Data
$unit_labels = [];
$unit_values = [];
$q_unit_dist = mysql_query("
    SELECT u.uraian, COUNT(*) as jumlah 
    FROM datagangguan g 
    JOIN kodeunit u ON g.unit = u.kodeunit 
    $where_sql 
    GROUP BY g.unit, u.uraian 
    ORDER BY jumlah DESC
");
while ($row = mysql_fetch_assoc($q_unit_dist)) {
    $unit_labels[] = $row['uraian'];
    $unit_values[] = (int)$row['jumlah'];
}

// 6. Gangguan Berdasarkan Cuaca Data
$weather_labels = [];
$weather_values = [];
$q_weather_dist = mysql_query("
    SELECT c.uraiancuaca, COUNT(*) as jumlah 
    FROM datagangguan g 
    JOIN kodecuaca c ON g.cuacakode = c.idcuaca 
    $where_sql 
    GROUP BY g.cuacakode, c.uraiancuaca 
    ORDER BY jumlah DESC
");
while ($row = mysql_fetch_assoc($q_weather_dist)) {
    $weather_labels[] = $row['uraiancuaca'];
    $weather_values[] = (int)$row['jumlah'];
}

// 7. Top 10 Penyulang Terganggu
$feeder_clauses = $where_clauses;
$feeder_clauses[] = "g.penyulang != ''";
$feeder_where_sql = "WHERE " . implode(" AND ", $feeder_clauses);
$feeder_labels = [];
$feeder_values = [];
$q_feeder = mysql_query("
    SELECT g.penyulang, COUNT(*) as jumlah 
    FROM datagangguan g 
    $feeder_where_sql 
    GROUP BY g.penyulang 
    ORDER BY jumlah DESC 
    LIMIT 10
");
while ($row = mysql_fetch_assoc($q_feeder)) {
    $feeder_labels[] = $row['penyulang'];
    $feeder_values[] = (int)$row['jumlah'];
}
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
      padding: 20px !important;
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
      <div class="col-md-4">
        <label for="tahun" class="form-label fw-semibold text-secondary small">Filter Tahun</label>
        <select class="form-select form-select-sm" id="tahun" name="tahun">
          <option value="ALL" <?php echo $selected_tahun == 'ALL' ? 'selected' : ''; ?>>Semua Tahun</option>
          <?php foreach ($years as $y): ?>
            <option value="<?php echo $y; ?>" <?php echo $selected_tahun == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="unit" class="form-label fw-semibold text-secondary small">Filter Unit / ULP</label>
        <select class="form-select form-select-sm" id="unit" name="unit">
          <option value="ALL" <?php echo $selected_unit == 'ALL' ? 'selected' : ''; ?>>Semua ULP / Unit</option>
          <?php foreach ($units as $u): ?>
            <option value="<?php echo $u['kodeunit']; ?>" <?php echo $selected_unit == $u['kodeunit'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['uraian']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-grid">
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

  <!-- Row 1: Line Chart & Pie Chart -->
  <div class="row">
    <!-- Tren Bulanan -->
    <div class="col-lg-8 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-chart-area me-2"></i>Tren Gangguan Bulanan</div>
          <div class="chart-container">
            <canvas id="monthlyTrendChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- Kategori Gangguan -->
    <div class="col-lg-4 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-chart-pie me-2"></i>Kategori Gangguan</div>
          <div class="chart-container">
            <canvas id="katChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: ULP Distribution & Top Causes -->
  <div class="row">
    <!-- Gangguan per ULP -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-map-marker-alt me-2"></i>Distribusi Gangguan per ULP</div>
          <div class="chart-container">
            <canvas id="ulpChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- Top 10 Penyebab -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-list-ol me-2"></i>Top 10 Penyebab Gangguan</div>
          <div class="chart-container">
            <canvas id="causeChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 3: Cuaca & Top 10 Penyulang -->
  <div class="row">
    <!-- Gangguan per Cuaca -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-sun me-2"></i>Faktor Kondisi Cuaca</div>
          <div class="chart-container">
            <canvas id="weatherChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- Top 10 Penyulang -->
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="chart-title"><i class="fa fa-bolt me-2"></i>Top 10 Penyulang Sering Terganggu</div>
          <div class="chart-container">
            <canvas id="feederChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Render Charts Scripts -->
<script>
  // Colors setup
  const primaryColor = '#242c6d';
  const primaryLight = 'rgba(36, 44, 109, 0.2)';
  const hoverColor = '#1d2358';
  
  const colorsPalette = [
    '#242c6d', // Dark Navy
    '#28a745', // Green
    '#dc3545', // Red
    '#ffc107', // Orange/Yellow
    '#17a2b8', // Teal
    '#6610f2', // Purple
    '#e83e8c', // Pink
    '#fd7e14', // Orange
    '#20c997', // Mint
    '#6c757d'  // Gray
  ];

  // 1. Monthly Trend Chart
  const ctxMonthly = document.getElementById('monthlyTrendChart').getContext('2d');
  new Chart(ctxMonthly, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
      datasets: [{
        label: 'Jumlah Gangguan',
        data: <?php echo json_encode($monthly_trend_values); ?>,
        borderColor: primaryColor,
        backgroundColor: primaryLight,
        borderWidth: 3,
        tension: 0.3,
        fill: true,
        pointBackgroundColor: primaryColor,
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });

  // 2. Kategori Gangguan Chart
  const ctxKat = document.getElementById('katChart').getContext('2d');
  new Chart(ctxKat, {
    type: 'doughnut',
    data: {
      labels: <?php echo json_encode($kat_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($kat_values); ?>,
        backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 12, font: { size: 11 } }
        }
      }
    }
  });

  // 3. ULP Distribution Chart
  const ctxUlp = document.getElementById('ulpChart').getContext('2d');
  new Chart(ctxUlp, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($unit_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($unit_values); ?>,
        backgroundColor: primaryColor,
        borderRadius: 5,
        barPercentage: 0.6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      }
    }
  });

  // 4. Top 10 Cause Chart
  const ctxCause = document.getElementById('causeChart').getContext('2d');
  new Chart(ctxCause, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($cause_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($cause_values); ?>,
        backgroundColor: '#dc3545',
        borderRadius: 5,
        barPercentage: 0.6
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: { beginAtZero: true, ticks: { stepSize: 1 } }
      }
    }
  });

  // 5. Weather Distribution Chart
  const ctxWeather = document.getElementById('weatherChart').getContext('2d');
  new Chart(ctxWeather, {
    type: 'polarArea',
    data: {
      labels: <?php echo json_encode($weather_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($weather_values); ?>,
        backgroundColor: colorsPalette.slice(0, <?php echo count($weather_labels); ?>),
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, font: { size: 10 } }
        }
      }
    }
  });

  // 6. Top 10 Feeder Chart
  const ctxFeeder = document.getElementById('feederChart').getContext('2d');
  new Chart(ctxFeeder, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($feeder_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($feeder_values); ?>,
        backgroundColor: '#ffc107',
        borderRadius: 5,
        barPercentage: 0.6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      }
    }
  });
</script>
</body>
</html>
