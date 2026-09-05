<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $stmt->bind_result($id, $fullname, $hashed_password, $role);
        $stmt->fetch();

        if(password_verify($password, $hashed_password)){
            $_SESSION["user_id"] = $id;
            $_SESSION["fullname"] = $fullname;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $role;
            
            if($role == "admin"){
                header("Location: admin_dashboard.php");
            } elseif($role == "supervisor"){
                header("Location: supervisor_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - DormPilot</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}
.login-box {
    background: white;
    padding:40px;
    width:420px;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}
.login-box h2 {
    text-align:center;
    margin-bottom:25px;
    color:#333;
    font-size:28px;
}
.form-group {
    margin-bottom:20px;
}
.form-group label {
    display:block;
    margin-bottom:8px;
    font-weight:bold;
    color:#555;
    font-size:14px;
}
.form-group input {
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:6px;
    background:#f9f9f9;
    font-size:15px;
    box-sizing:border-box;
}
.form-group input:focus {
    border-color:#4a90e2;
    background:#fff;
    outline:none;
}
.login-box button {
    width:100%;
    padding:12px;
    background:#4a90e2;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
    margin-top:10px;
    font-weight:bold;
}
.login-box button:hover {
    background:#357abd;
}
.error {
    color:red;
    font-size:14px;
    text-align:center;
    margin-bottom:15px;
    padding:10px;
    background:#ffe6e6;
    border-radius:5px;
}
.login-box p {
    text-align:center;
    margin-top:20px;
    color:#666;
}
.login-box a {
    color:#4a90e2;
    text-decoration:none;
}
.login-box a:hover {
    text-decoration:underline;
}
.logo-section {
    text-align:center;
    margin-bottom:20px;
}
.logo-section img {
    width:250px;
    height:auto;
}
.role-info {
    text-align:center;
    margin-top:15px;
    padding:10px;
    background:#f0f8ff;
    border-radius:5px;
    font-size:13px;
    color:#555;
}
</style>
</head>
<body>

<div class="login-box">
    <div class="logo-section">
        <img src="logo.png" alt="DormPilot Logo">
    </div>

    <h2>Login</h2>

    <?php if($error != ""){ echo '<div class="error">'.$error.'</div>'; } ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit">Login</button>
        
        <div class="role-info">
            Login for Admin, Supervisor, or Student
        </div>

        <p>Don't have an account? <a href="register.php">Register as Student</a></p>
        <p><a href="index.php">Back to Home</a></p>
    </form>
</div>

</body>
</html>
