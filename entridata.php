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
      padding: 10px 15px 80px 15px !important;
      background-color: #f4f6f9 !important;
      overflow-y: auto !important;
      overflow-x: auto !important;
      -webkit-overflow-scrolling: touch;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      border: none;
      max-width: 100%;
      overflow-x: auto;
    }
  </style>

    </head>


    <body class="fixed-left">

    <h4 class="page-title">Entri Data</h4>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
    <div class="card">
             <div class="m-t-20"  style = "margin : 0px 10px 0px 20px;">
                                                <h6 class="sub-title"><b>Kode Gangguan</b></h6>
                                           <input type="text"  class="form-control" style = "text-transform:uppercase";  name="kodegangguan"  />     
                                               
                                            </div> 
                                        <div class="card-body bootstrap-select-1">
                                        
                                                    <h6 class="sub-title my-3">Tanggal Gangguan</h6>
                                                    <input type="text" id="date-format" class="form-control" name = "tglgangguan" 
                                                    required = "" >
                                                    
                                                </div>
                                                <div class="row" style = "margin : 0px 10px 0px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Pilih Kategori Gangguan</h6>
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
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Pilih Dulu Kategori Gangguan</h6>
                                                    <div id="content1" class="content active">
                                                    <select class="select2 form-control mb-3 custom-select" disabled>
                                                <option></option>
                                                       </select>
  </div>
  <div id="content2" class="content">
  <select class="select2 form-control mb-3 custom-select" name = "pmt" >
                                                <option value = ""></option>
                                                <?php
include "connect.php";
		$v = mysql_query("select * from v_penyulang");
		while($vata = mysql_fetch_object($v))
		{
			echo "<option value= $vata->unit|$vata->kodepenyul >$vata->uraian|$vata->uraianpenyul</option>";
		}
		
	

?>
                                                       </select>
  </div>
  <div id="content3" class="content">
  <select class="select2 form-control mb-3 custom-select" name = "rec" >
                                                <option value = ""></option>
                                                <?php
include "connect.php";
		$v = mysql_query("select * from v_keypoint");
		while($vata = mysql_fetch_object($v))
		{
			echo "<option value= $vata->unit|$vata->kodepenyul|$vata->idkeypoint >$vata->uraian|$vata->uraianpenyul|$vata->keterangan</option>";
		}
		
	

?>
                                                       </select>
  </div>

  <script>
    const radios = document.querySelectorAll('input[name="option"]');
    const content1 = document.getElementById('content1');
    const content2 = document.getElementById('content2');
    const content3 = document.getElementById('content3');

    radios.forEach(radio => {
      radio.addEventListener('change', () => {
        if (radio.value === 'PMT' && radio.checked) {
          content1.classList.remove('active');
          content2.classList.add('active');
          content3.classList.remove('active');
        } else if (radio.value === 'REC' && radio.checked) {
          content1.classList.remove('active');
          content2.classList.remove('active');
          content3.classList.add('active');
        }
      });
    });
  </script>
                                                </div>
                                            </div> 
                                            <div class="row" style = "margin : 0px 10px 0px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Pilih Kategori</h6>
                                               
                                                </div>
                                                <div class="col-md-6">
                                                   <div class="form-check-inline my-1">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" required = "" id="customRadio6" name="kategori" class="custom-control-input"
                                                                    value = "TEMPORER">
                                                                    <label class="custom-control-label" for="customRadio6">TEMPORER</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-check-inline my-1">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" required = "" id="customRadio7" name="kategori" class="custom-control-input" 
                                                                    value = "PERMANEN">
                                                                    <label class="custom-control-label" for="customRadio7">PERMANEN</label>
                                                                </div>
                                                            </div>
                                                            
                                            </div>  </div>
                                                  <div class="card-body bootstrap-select-1">
                                        
                                                    <h6 class="sub-title my-3">Tanggal Masuk</h6>
                                                    <input type="text" id="date-format2" class="form-control" name = "tglmasuk" 
                                                    required = "" >
                                                    
                                                </div>
                                            <div class="row" style = "margin : 0px 10px 0px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Relay Kerja</h6>
                                               
                                                </div>
                                                <div class="col-md-6">
                                                <select class="select2 form-control mb-3 custom-select" name = "relay">
                                                <option value = ""></option>
                                                <option value = "DGR">DGR</option>
                                                <option value = "EF">EF</option>
                                                <option value = "OCR">OCR</option>
                                                <option value = "OCR-INSTANT">OCR-INSTANT</option>
                                             
                                                       </select>
                                                            
                                            </div>  </div>
                                            <div class="row" style = "margin : 0px 10px 0px 10px;">
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">Fasa</h6>                                            
                                                    <select class="select2 form-control mb-3 custom-select" name = "fasa" >
                                                <option value = ""></option>
                                                <option value = "R">R</option>
                                                <option value = "RS">RS</option>
                                                <option value = "RST">RST</option>
                                                <option value = "RT">RT</option>
                                                <option value = "S">S</option>
                                                <option value = "ST">ST</option>
                                                <option value = "T">T</option>
                                             
                                                       </select>
                                                </div>                                    
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">KV 0</h6>                                            
                                                    <input type="number" value="0" class="form-control"  name="kv0"  />
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">I N</h6>                                           
                                                    <input type="number"  value="0" class="form-control"  name="inol"  />
                                                </div>
                                            </div>                                
                                              <div class="row" style = "margin : 0px 10px 0px 10px;">
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">I R</h6>                                            
                                                    <input type="number"  value="0" class="form-control"  name="ir"  />
                                                </div>                                    
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">I S</h6>                                            
                                                    <input type="number"  value="0" class="form-control"  name="ies"  />
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="sub-title mb-3">I T</h6>                                           
                                                    <input type="number"  value="0" class="form-control"  name="it"  />
                                                </div>
                                            </div>
                                            <div class="row" style = "margin : 0px 0px 10px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Cuaca</h6>
                                                  <select class="select2 form-control mb-3 custom-select" name = "cuaca" required=""  >
                                                <option value = ""></option>
                                                <?php
include "connect.php";
		$v = mysql_query("select * from kodecuaca");
		while($vata = mysql_fetch_object($v))
		{
			echo "<option value= $vata->idcuaca >$vata->uraiancuaca</option>";
		}
		
	

?>
                                                       </select>
                                                </div>
                                                <div class="col-md-6">
                                                   <h6 class="sub-title mb-3">Jenis Gangguan</h6>
                                                  <select class="select2 form-control mb-3 custom-select" name = "jenisgangguan" required="" >
                                                <option value = ""></option>
                                                <?php
include "connect.php";
		$v = mysql_query("select * from kodejenisgangguan");
		while($vata = mysql_fetch_object($v))
		{
			echo "<option value= $vata->idjenisgangguan >$vata->uraianjenisgangguan</option>";
		}
		
	

?>
                                                       </select>
                                                            
                                            </div>  </div>
                                              <div class="row" style = "margin : 0px 0px 10px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Latitude Lokasi</h6>
                                                <input type="text"  class="form-control" style = "text-transform:uppercase";  name="latlokasi"  />
                                                </div>
                                                <div class="col-md-6">
                                                   <h6 class="sub-title mb-3">Longitude Lokasi</h6>
                                                 <input type="text"   class="form-control" style = "text-transform:uppercase";  name="longlokasi"  />
                                                            
                                            </div>  </div>
                                             <div class="m-t-20"  style = "margin : 0px 10px 0px 20px;">
                                                <h6 class="sub-title"><b>Hasil Temuan</b></h6>
                                              
                                                <textarea id="textarea" class="form-control" style = "text-transform:uppercase";  name="temuan" rows="3" ></textarea>
                                            </div> 
                                          <div class="row" style = "margin : 0px 0px 10px 10px;">
                                                <div class="col-md-6">
                                                    <h6 class="sub-title mb-3">Foto 1</h6>
                                                  <input type="file"  accept="image/*" class="form-control"  name="file1"  />
                                                </div>
                                                <div class="col-md-6">
                                                   <h6 class="sub-title mb-3">Foto 2</h6>
                                                 
                                                  <input type="file" accept="image/*" class="form-control"  name="file2"  />             
                                            </div>  </div>
                                                   <button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
                                    </div>
                                    </div>
                                    </form>  
                                  
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
    lang: 'en',
    weekStart: 1,
    cancelText: 'Batal',
    nowButton: true,
    switchOnClick: true,
    time: true,
    date: true,
    shortTime: false,      // pastikan format 24 jam dan menit tampil
    steppingMinute: 1      // langkah menit = 1
});

$('#date-format2').bootstrapMaterialDatePicker({
    format: 'YYYY-MM-DD HH:mm',
    lang: 'en',
    weekStart: 1,
    cancelText: 'Batal',
    nowButton: true,
    switchOnClick: true,
    time: true,
    date: true,
    shortTime: false,      // pastikan menit muncul
    steppingMinute: 1      // langkah menit 1
});


          </script>
        <!-- Plugins Init js -->
        <script src="assets/pages/form-advanced.js"></script>
         
        <!-- App js -->
        <script src="assets/js/app.js"></script>

    </body>
</html>