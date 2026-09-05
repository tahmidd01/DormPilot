<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Add supervisor
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_supervisor"])){

    $fullname   = $_POST["fullname"];
    $email      = $_POST["email"];
    $phone      = $_POST["phone"];
    $password   = $_POST["password"];
    $block      = $_POST["block"];

    // Upload directories
    $profile_dir = "uploads/supervisors/";
    $nid_dir     = "uploads/nid/";

    if(!is_dir($profile_dir)) mkdir($profile_dir,0777,true);
    if(!is_dir($nid_dir)) mkdir($nid_dir,0777,true);

    // Profile photo
    $profile_ext  = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
    $profile_name = time() . "_profile." . $profile_ext;
    move_uploaded_file($_FILES["photo"]["tmp_name"], $profile_dir . $profile_name);

    // NID photo
    $nid_ext  = pathinfo($_FILES["nid_photo"]["name"], PATHINFO_EXTENSION);
    $nid_name = time() . "_nid." . $nid_ext;
    move_uploaded_file($_FILES["nid_photo"]["tmp_name"], $nid_dir . $nid_name);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users 
        (fullname,email,password,role,phone,block,photo,nid_photo)
        VALUES (?, ?, ?, 'supervisor', ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssssss",
        $fullname,
        $email,
        $hashed_password,
        $phone,
        $block,
        $profile_name,
        $nid_name
    );

    if($stmt->execute()){
        $message = "Supervisor added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }

    $stmt->close();
}

// Fetch all supervisors
$supervisors = $conn->query("SELECT * FROM users WHERE role='supervisor' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Supervisors - Admin</title>
<style>
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}
body {background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec); font-family: Arial;}
body.with-admin-sidebar {margin-left:260px;}
.main-content {flex:1; padding:20px;}
.top-bar {display:flex; justify-content:flex-end; align-items:center; margin-bottom:20px;}
.top-bar .logout {padding:10px 18px; font-size:18px; border-radius:5px; display:flex; align-items:center; background:#e24a84; color:#fff; border:none; cursor:pointer;}
.top-bar .logout img {width:20px; height:20px; margin-right:8px;}
.container {max-width:1000px; margin:0 auto; padding:0 20px;}
.form-section, .list-section {background:white; padding:25px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px;}
.form-section h2, .list-section h2 {margin-bottom:20px; color:#333;}
.form-row {display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;}
.form-group {margin-bottom:15px;}
.form-group label {display:block; margin-bottom:5px; color:#555; font-weight:bold;}
.form-group input, .form-group select {width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-size:14px;}
.btn {padding:10px 20px; background:#e24a84; color:white; border:none; border-radius:5px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; text-align:center;}
.btn:hover {background:#ad276a;}
.btn-danger {background:#dc3545;}
.btn-danger:hover {background:#c82333;}
.message {padding:10px; margin-bottom:15px; border-radius:5px; background:#d4edda; color:#155724;}
table {width:100%; border-collapse:collapse;}
table th, table td {padding:12px; text-align:left; border-bottom:1px solid #ddd;}
table th {background:#f8f9fa; font-weight:bold;}
img {border-radius:50%;}
</style>
</head>
<body class="with-admin-sidebar">

<?php include "admin_sidebar.php"; ?>

<div class="main-content">
<div class="top-bar">
    <form action="logout.php" method="post" style="margin:0;">
        <button type="submit" class="logout">
            <img src="icons/logout.png" alt="Logout Icon"> Logout
        </button>
    </form>
</div>

<div class="container">
    <?php if($message != "") echo '<div class="message">'.$message.'</div>'; ?>

    <!-- Add Supervisor Form -->
    <div class="form-section">
        <h2>Add New Supervisor</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Block</label>
                    <input type="text" name="block" placeholder="e.g., Block A">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Supervisor Photo</label>
                <input type="file" name="photo" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>NID Photo</label>
                <input type="file" name="nid_photo" accept="image/*" required>
            </div>
            <button type="submit" name="add_supervisor" class="btn">Add Supervisor</button>
        </form>
    </div>

    <!-- Existing Supervisors List -->
    <div class="list-section">
        <h2>Existing Supervisors</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($supervisors->num_rows > 0): ?>
                    <?php while($row = $supervisors->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td>
                                <a href="admin_view_supervisor.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No supervisors found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>
