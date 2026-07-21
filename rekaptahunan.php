<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Rekap Tahunan - Monit Gangguan</title>
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
    body {
        padding: 0px 10px !important;
        background-color: #f4f6f9 !important;
    }
    .page-title {
        margin-top: 5px !important;
        margin-bottom: 10px !important;
    }
    .card {
        margin-bottom: 10px !important;
    }
  </style>
  <script>
    function resizeIframe(obj) {
      if (obj && obj.contentWindow && obj.contentWindow.document) {
        var wrapper = obj.contentWindow.document.getElementById('content-wrapper');
        if (wrapper) {
          obj.style.height = (wrapper.scrollHeight + 40) + 'px';
        }
      }
    }
  </script>
    </head>


    <body class="fixed-left">

    <h4 class="page-title">Rekap Tahunan</h4>
    <form action="monittahunan.php" target="frame23" method="POST" enctype="multipart/form-data">
    <div class="card">
                                       
                                            <div class="row" style = "margin : 0px 0px 0px 10px; padding-top: 15px;">
                                                <div class="col-md-3">
                                                    <h6 class="sub-title mb-3">Tahun Awal</h6>
                                                    <select class="form-control select2" name="tahun_awal" required>
                                                        <?php
                                                        $current_year = (int)date('Y');
                                                        for ($i = 2018; $i <= $current_year + 5; $i++) {
                                                            $selected = ($i == $current_year - 4) ? 'selected' : '';
                                                            echo "<option value='$i' $selected>$i</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <h6 class="sub-title mb-3">Tahun Akhir</h6>
                                                    <select class="form-control select2" name="tahun_akhir" required>
                                                        <?php
                                                        for ($i = 2018; $i <= $current_year + 5; $i++) {
                                                            $selected = ($i == $current_year) ? 'selected' : '';
                                                            echo "<option value='$i' $selected>$i</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                   <h6 class="sub-title mb-3">Kategori Gangguan</h6>
                                                 <div class="form-check-inline my-1">
                                                                 <div class="custom-control custom-radio">
                                                                    <input type="radio" id="customRadio4" required = "" name="option" class="custom-control-input"
                                                                    value = "PMT">
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
                                                                    value = "ALL" checked>
                                                                    <label class="custom-control-label" for="customRadio6">ALL</label>
                                                                </div>
                                                            </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h6 class="sub-title mb-3">Unit</h6>
                                                    <select class="form-control select2" name="unit" required>
                                                        <option value="">Pilih Unit</option>
                                                        <?php
                                                        include "connect.php";
                                                        $v = mysql_query("SELECT * FROM kodeunit ORDER BY uraian");
                                                        while($r = mysql_fetch_assoc($v))
                                                        {
                                                            ?>
                                                            <option value="<?php echo $r['kodeunit']; ?>" <?php echo ($r['kodeunit'] == '5125') ? 'selected' : ''; ?>>
                                                                <?php echo $r['uraian']; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <button type="submit" class="btn btn-primary btn-lg btn-block">Pilih</button>
                                            </div>
                                     </div>  
                                     </form>  
                                      <div style="width: 100%; margin: 10px 0 0 0; overflow: hidden;">
                                          <iframe onload="resizeIframe(this)" allowTransparency="true" frameborder="0" scrolling="no" style="width:100%; border:none;" name="frame23" src="monittahunan.php" ></iframe>
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
        <script src="assets/plugins/select2/select2.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
        <!-- App js -->
        <script src="assets/js/app.js"></script>

    </body>
</html>
