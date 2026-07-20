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

    <h4 class="page-title">Rekap Harian</h4>
    <form action="monitharian.php" target="frame23" method="POST" enctype="multipart/form-data">
    <div class="card">
                                       
                                            <div class="row" style = "margin : 0px 0px 0px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Range Tanggal</h6>
                                               <div class="input-daterange input-group" id="date-range">
                                                            <input type="text" class="form-control" required="" name="tglawal" placeholder="Start Date" value="<?php echo date('m/d/Y'); ?>" />
                                                            <input type="text" class="form-control" required="" name="tglakhir" placeholder="End Date" value="<?php echo date('m/d/Y'); ?>" />
                                                        </div>
                                                </div>
                                                <div class="col-md-6" style = "margin:0px 0px 10px 0px;">
                                                   <h6 class="sub-title mb-3">Pilih Unit</h6>
                                                     <select class="select2 form-control mb-3 custom-select" name = "unit" required = "" >
                                                <option value = ""></option>
                                                <?php
include "connect.php";
		$v = mysql_query("select * from kodeunit");
		while($vata = mysql_fetch_object($v))
		{
			echo "<option value= $vata->kodeunit >$vata->uraian</option>";
		}
		
	

?>
                                                       </select>
                                                  </div>
                                             
                                               <button type="submit" class="btn btn-primary btn-lg btn-block">Pilih</button>
                                     </div>  
                                     </div>
                                     </form>  
                                      <div style="width: 100%; margin: 10px 0 0 0; overflow: hidden;">
                                          <iframe onload="resizeIframe(this)" allowTransparency="true" frameborder="0" scrolling="no" style="width:100%; border:none;" name="frame23" src="monitharian.php" ></iframe>
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