<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <title>Upload CSV | Zoter Dashboard</title>

  <link rel="shortcut icon" href="assets/images/favicon.ico">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="assets/css/icons.css" rel="stylesheet" type="text/css">
  <link href="assets/css/style.css" rel="stylesheet" type="text/css">

  <style>
    body {
      background: #f8f9fa;
    }
    .upload-card {
      max-width: 100%;
      margin: 30px auto;
      border-radius: 0px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .card-body {
      padding: 30px;
    }
  </style>
</head>

<body class="fixed-left">

  <div class="container">
    <div class="card upload-card">
      <div class="card-body">
        <h4 class="page-title mb-4">Upload File CSV Ukur Gardu</h4>

        <form action="prosesuploadgardu.php" method="POST" enctype="multipart/form-data">

       
          <div class="form-group">
            <label for="filecsv">Pilih File CSV</label>
            <input type="file" id="filecsv" name="filecsv" class="form-control-file" accept=".csv" required>
            <small class="form-text text-muted">Hanya file berekstensi .csv yang diperbolehkan.</small>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">Upload</button>
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery & Bootstrap -->
  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!-- DateTime Picker -->
  <script src="assets/plugins/timepicker/moment.js"></script>
  <script src="assets/plugins/timepicker/bootstrap-material-datetimepicker.js"></script>
  <link href="assets/plugins/timepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">

  <script>
    // Aktifkan date-time picker
    $('#tglgangguan').bootstrapMaterialDatePicker({
      format: 'YYYY-MM-DD HH:mm',
      lang: 'id',
      weekStart: 1,
      cancelText: 'Batal',
      nowButton: true,
      time: true,
      date: true
    });

    // Validasi file hanya CSV
    $('#filecsv').on('change', function () {
      const file = this.files[0];
      if (file && file.name.split('.').pop().toLowerCase() !== 'csv') {
        alert('Hanya file CSV yang diperbolehkan!');
        this.value = ''; // reset input
      }
    });
  </script>
</body>
</html>
