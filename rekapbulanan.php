<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Zoter - Responsive Bootstrap 4 Admin Dashboard</title>
        <meta content="Admin Dashboard" name="description" />
        <meta content="Mannatthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <link href="assets/plugins/timepicker/tempusdominus-bootstrap-4.css" rel="stylesheet" />
        <link href="assets/plugins/timepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">
        <link href="assets/plugins/clockpicker/jquery-clockpicker.min.css" rel="stylesheet" />
        <link href="assets/plugins/colorpicker/asColorPicker.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
        
        <link href="assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="assets/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css">
        <link href="assets/css/style.css" rel="stylesheet" type="text/css">
        <style>
    .content {
      display: none;
          }
    .content.active {
      display: block;
    }
  </style>

    </head>


    <body class="fixed-left">

    <h4 class="page-title">Rekap Bulanan</h4>
    <form action="monitbulanan.php" target="frame23" method="POST" enctype="multipart/form-data">
    <div class="card">
                                       
                                            <div class="row" style = "margin : 0px 0px 0px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Pilih Tahun</h6>
                                                  <select class="select2 form-control mb-3 custom-select" name = "tahun" required="" >
                                                <option value = ""></option>
                                             <?php
                $tahunSekarang = '2020';
                $tahunMaju = 30; // contoh: tampilkan 10 tahun ke depan
                for ($i = $tahunSekarang; $i <= $tahunSekarang + $tahunMaju; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
            ?>
                                                       </select>
                                                </div>
                                                <div class="col-md-6">
                                                   <h6 class="sub-title mb-3">Kategori Gangguan</h6>
                                                 <div class="form-check-inline my-1">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" id="customRadio4" required = "" name="option" class="custom-control-input"
                                                                    value = "PMT" checked>
                                                                    <label class="custom-control-label" for="customRadio4">PMT</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-check-inline my-1">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" id="customRadio5" required = "" name="option" class="custom-control-input" 
                                                                    value = "REC">
                                                                    <label class="custom-control-label" for="customRadio5">REC / PMCB</label>
                                                                </div>
                                                            </div>
                                                             <div class="form-check-inline my-1">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" id="customRadio6" required = "" name="option" class="custom-control-input"
                                                                    value = "ALL">
                                                                    <label class="custom-control-label" for="customRadio6">ALL</label>
                                                                </div>
                                                            </div>
                                                            
                                            </div>  </div>
                                              <div class="card-body bootstrap-select-1">
                                        
                                                    <h6 class="sub-title my-3">Unit</h6>
                                                 <select class="form-control select2" name="unit" required>
    <option value="">Pilih Unit</option>

    <?php
    include "connect.php";

    $v = mysqli_query($koneksi,"SELECT * FROM kodeunit ORDER BY uraian");

    while($r = mysqli_fetch_assoc($v))
    {
        ?>
        <option value="<?php echo $r['kodeunit']; ?>">
            <?php echo $r['uraian']; ?>
        </option>
        <?php
    }
    ?>
</select>
                                                    
                                                </div>
                                              <button type="submit" class="btn btn-primary btn-lg btn-block">Pilih</button>
                                    </div>  </form>  
                                     <div style = "margin : 10px 0px;">   <iframe height="600" allowTransparency="true" frameborder="0" 
                                scrolling="auto" style="width:100%;border:none" name="frame23" src="monitbulanan.php" ></iframe></div>
                            </div>      
                                  
        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/modernizr.min.js"></script>
        <script src="assets/js/detect.js"></script>
        <script src="assets/js/fastclick.js"></script>
        <script src="assets/js/jquery.blockUI.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
 <!-- Plugins js -->
 <script src="assets/plugins/timepicker/moment.js"></script>
        <script src="assets/plugins/timepicker/tempusdominus-bootstrap-4.js"></script>
        <script src="assets/plugins/timepicker/bootstrap-material-datetimepicker.js"></script>
        <script src="assets/plugins/clockpicker/jquery-clockpicker.min.js"></script>
        <script src="assets/plugins/colorpicker/jquery-asColor.js" type="text/javascript"></script>
        <script src="assets/plugins/colorpicker/jquery-asGradient.js" type="text/javascript"></script>
        <script src="assets/plugins/colorpicker/jquery-asColorPicker.min.js" type="text/javascript"></script>
        <script src="assets/plugins/select2/select2.min.js" type="text/javascript"></script>
 
        <script src="assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="assets/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
        <script src="assets/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
        <script>
        $('#date-format').bootstrapMaterialDatePicker({
    format: 'YYYY-MM-DD HH:mm',
    lang: 'en',              // Bahasa
    weekStart: 1,            // Senin sebagai awal minggu
    cancelText: 'Batal',
    nowButton: true,
    switchOnClick: true,
    time: true,
    date: true
});
 $('#date-format2').bootstrapMaterialDatePicker({
    format: 'YYYY-MM-DD HH:mm',
    lang: 'en',              // Bahasa
    weekStart: 1,            // Senin sebagai awal minggu
    cancelText: 'Batal',
    nowButton: true,
    switchOnClick: true,
    time: true,
    date: true
});

          </script>
        <!-- Plugins Init js -->
        <script src="assets/pages/form-advanced.js"></script>
         
        <!-- App js -->
        <script src="assets/js/app.js"></script>

    </body>
</html>