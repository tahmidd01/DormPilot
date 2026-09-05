<?php
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm = $_POST["confirm-password"];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required!";
    } elseif ($password != $confirm) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'student')");
        $stmt->bind_param("sss", $fullname, $email, $hashed_password);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(to bottom right, #ffd9ec, #cfe7ff);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}
.register-box {
    background: linear-gradient(to right, #e6d4ff, #cfe7ff, #ffd6f2);
    padding:30px;
    width:400px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.15);
}
.register-box h2 {
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
.register-box button {
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
.register-box button:hover {
    background:#ad276a;
}
.error {
    color:red;
    font-size:14px;
    text-align:center;
    margin-top:10px;
}
.register-box p {
    text-align:center;
    margin-top:10px;
}
</style>
</head>
<body>

<div class="register-box">

    
    <div style="text-align:center; margin-bottom:15px;">
        <img src="logo.png" alt="Logo" style="width:300px; height:auto;">
    </div>

<h2>Register</h2>

<?php if($error != ""){ echo '<p class="error">'.$error.'</p>'; } ?>

<form method="POST" action="">
    <div class="form-group">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" placeholder="Enter full name">
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Enter email">
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password">
    </div>

    <div class="form-group">
        <label for="confirm-password">Confirm:</label>
        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm password">
    </div>

    <button type="submit">Register</button>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</form>
</div>

</body>
</html>
