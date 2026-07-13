<?php
session_start();

if(isset($_SESSION['login_gembok'])){
    header("Location: gembok_data.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login Gembok</title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}

.card{
    width:350px;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0 4px 20px rgba(0,0,0,.1);
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ddd;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#0d6efd;
    color:white;
    border-radius:8px;
    cursor:pointer;
}
</style>
</head>
<body>

<div class="card">

<h2>🔒 Login Gembok</h2>
<br>

<form method="POST" action="cek_gembok.php">
    <input type="text" name="user" placeholder="User" required>
    <input type="password" name="pass" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

</div>

</body>
</html>