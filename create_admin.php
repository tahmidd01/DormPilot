<?php
// Script to create Admin and Supervisor accounts
// Run this file once to create default admin and supervisor accounts

include "db.php";

$message = "";

// Create Admin account
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_admin"])){
    $fullname = $_POST["admin_name"];
    $email = $_POST["admin_email"];
    $password = $_POST["admin_password"];
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);
    
    if($stmt->execute()){
        $message .= "Admin account created successfully!<br>";
    } else {
        $message .= "Error creating admin: " . $conn->error . "<br>";
    }
    $stmt->close();
}

// Create Supervisor account
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_supervisor"])){
    $fullname = $_POST["supervisor_name"];
    $email = $_POST["supervisor_email"];
    $password = $_POST["supervisor_password"];
    $phone = $_POST["supervisor_phone"] ?? "";
    $block = $_POST["supervisor_block"] ?? "";
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, phone, block) VALUES (?, ?, ?, 'supervisor', ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $phone, $block);
    
    if($stmt->execute()){
        $message .= "Supervisor account created successfully!<br>";
    } else {
        $message .= "Error creating supervisor: " . $conn->error . "<br>";
    }
    $stmt->close();
}

// Check existing accounts
$admins = $conn->query("SELECT id, fullname, email FROM users WHERE role='admin'");
$supervisors = $conn->query("SELECT id, fullname, email FROM users WHERE role='supervisor'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Admin/Supervisor Accounts</title>
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
}
body {
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    padding:20px;
}
.container {
    max-width:800px;
    margin:0 auto;
    background:white;
    padding:30px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
h1 {
    color:#333;
    margin-bottom:20px;
}
h2 {
    color:#4a90e2;
    margin:30px 0 15px 0;
    padding-bottom:10px;
    border-bottom:2px solid #4a90e2;
}
.message {
    padding:15px;
    margin-bottom:20px;
    border-radius:5px;
    background:#d4edda;
    color:#155724;
}
.form-group {
    margin-bottom:15px;
}
.form-group label {
    display:block;
    margin-bottom:5px;
    color:#555;
    font-weight:bold;
}
.form-group input {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
}
.form-row {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}
.btn {
    padding:12px 25px;
    background:#4a90e2;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
}
.btn:hover {
    background:#357abd;
}
.btn-success {
    background:#28a745;
}
.btn-success:hover {
    background:#20c997;
}
.existing-accounts {
    margin-top:30px;
    padding:20px;
    background:#f8f9fa;
    border-radius:5px;
}
.existing-accounts h3 {
    margin-bottom:15px;
    color:#333;
}
.existing-accounts ul {
    list-style:none;
    margin-left:0;
}
.existing-accounts li {
    padding:8px;
    background:white;
    margin-bottom:5px;
    border-radius:3px;
}
.note {
    background:#fff3cd;
    padding:15px;
    border-radius:5px;
    margin-bottom:20px;
    border-left:4px solid #ffc107;
}
</style>
</head>
<body>

<div class="container">
    <h1>Create Admin & Supervisor Accounts</h1>
    
    <div class="note">
        <strong>Note:</strong> This page is for initial setup. After creating accounts, you can delete this file for security.
    </div>
    
    <?php if($message != ""): ?>
    <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <h2>Create Admin Account</h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="admin_name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="admin_email" required>
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="admin_password" required>
        </div>
        <button type="submit" name="create_admin" class="btn">Create Admin</button>
    </form>
    
    <h2>Create Supervisor Account</h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="supervisor_name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="supervisor_email" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="supervisor_phone">
            </div>
            <div class="form-group">
                <label>Block</label>
                <input type="text" name="supervisor_block" placeholder="e.g., Block A">
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="supervisor_password" required>
        </div>
        <button type="submit" name="create_supervisor" class="btn btn-success">Create Supervisor</button>
    </form>
    
    <div class="existing-accounts">
        <h3>Existing Accounts</h3>
        <strong>Admins:</strong>
        <ul>
            <?php if($admins->num_rows > 0): ?>
                <?php while($admin = $admins->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($admin["fullname"]); ?> (<?php echo htmlspecialchars($admin["email"]); ?>)</li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No admin accounts found</li>
            <?php endif; ?>
        </ul>
        
        <strong style="display:block; margin-top:15px;">Supervisors:</strong>
        <ul>
            <?php if($supervisors->num_rows > 0): ?>
                <?php while($supervisor = $supervisors->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($supervisor["fullname"]); ?> (<?php echo htmlspecialchars($supervisor["email"]); ?>)</li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No supervisor accounts found</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <p style="margin-top:30px;">
        <a href="login.php" style="color:#4a90e2; text-decoration:none;">← Back to Login</a>
    </p>
</div>

</body>
</html>

