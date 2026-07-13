<?php
session_start();
include "koneksi2.php";

$user=trim($_POST['user']);
$pass=trim($_POST['pass']);

$q=mysql_query("
SELECT * FROM usergembok 
WHERE TRIM(user)=TRIM('$user')
AND TRIM(pass)=TRIM('$pass')
") or die(mysql_error());

if(mysql_num_rows($q)>0){
    $_SESSION['login_gembok']="ok";
    header("Location:gembok_data.php");
}else{
    echo "<script>alert('Login gagal');history.back();</script>";
}
?>