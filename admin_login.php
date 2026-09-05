<?php
session_start();
include "db.php"; 

$error = "";


if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $stmt->bind_result($id, $fullname, $hashed_password);
        $stmt->fetch();

        if(password_verify($password, $hashed_password)){
            $_SESSION["admin_id"] = $id;
            $_SESSION["admin_name"] = $fullname;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Admin not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | DormPilot</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}
.login-box {
    background: linear-gradient(to right, #ffd6f2, #cfe7ff, #e6d4ff);
    padding:30px;
    width:400px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.15);
}
.login-box h2 {
    text-align:center;
    margin-bottom:20px;
    color:#333;
}
.form-group {
    display:flex;
    align-items:center;
    margin:10px 0;
}
.form-group label {
    margin-right:5px;
    width:100px;
    font-weight:bold;
    color:#333;
}
.form-group input {
    flex:1;
    padding:10px;
    border:1px solid #a17878;
    border-radius:6px;
    background:#fffdfd;
    font-size:15px;
    outline:none;
}
.form-group input:focus {
    border-color:#4a90e2;
    background:#fff;
}
.login-box button {
    width:100%;
    padding:12px;
    background:#e24a84;
    color:#141414;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
    margin-top:15px;
}
.login-box button:hover {
    background:#ad276a;
}
.error {
    color:red;
    font-size:14px;
    text-align:center;
    margin-top:10px;
}
.login-box p {
    text-align:center;
    margin-top:10px;
}
.login-box a{
    color:#e24a84;
    text-decoration:none;
}
.login-box a:hover{
    text-decoration:underline;
}
</style>
</head>
<body>

<div class="login-box">

   
    <div style="text-align:center; margin-bottom:15px;">
        <img src="logo.png" alt="DormPilot Logo" style="width:300px; height:auto;">
    </div>

    <h2>Admin Login</h2>

    <?php if($error != ""){ echo '<p class="error">'.$error.'</p>'; } ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit">Login</button>

        <p>Back to <a href="index.php">Home</a></p>
    </form>
</div>

</body>
</html>
