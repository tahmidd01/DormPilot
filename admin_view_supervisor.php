<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: admin_supervisors.php");
    exit();
}

$id = intval($_GET['id']);
$message = "";

// Remove supervisor
if(isset($_GET["remove"]) && $_GET["remove"] == $id){
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role='supervisor'");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Supervisor removed successfully!";
        header("Location: admin_supervisors.php");
        exit();
    } 
    $stmt->close();
}

// Fetch supervisor info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role='supervisor'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: admin_supervisors.php");
    exit();
}

$supervisor = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Supervisor - Admin</title>
<style>
body {font-family: Arial, sans-serif; background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec); padding:30px;}
.container {max-width:750px; margin:0 auto; background:white; padding:40px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);}
h2 {text-align:center; margin-bottom:30px; font-size:28px; color:#333;}
.profile-pic {width:160px; height:160px; border-radius:50%; display:block; margin:0 auto 30px; object-fit:cover; border:3px solid #e24a84;}
.info {margin-bottom:20px; font-size:18px;}
.info label {font-weight:bold; color:#555; min-width:120px; display:inline-block;}
.info span {margin-left:10px; color:#333;}
.btn {padding:12px 25px; background:#e24a84; color:white; border:none; border-radius:6px; cursor:pointer; text-decoration:none; font-size:16px;}
.btn:hover {background:#ad276a;}
.btn-danger {background:#dc3545;}
.btn-danger:hover {background:#c82333;}
.message {padding:12px; margin-bottom:20px; border-radius:6px; background:#d4edda; color:#155724; font-size:16px; text-align:center;}
.top-links {text-align:center; margin-top:30px;}
.top-links a {margin:0 15px;}
</style>
</head>
<body>

<div class="container">
    <?php if($message != "") echo '<div class="message">'.$message.'</div>'; ?>
    
    <h2>Supervisor Info</h2>
    <img src="uploads/supervisors/<?php echo $supervisor['photo']; ?>" class="profile-pic" alt="Profile Photo">

    <div class="info"><label>Full Name:</label> <span><?php echo htmlspecialchars($supervisor['fullname']); ?></span></div>
    <div class="info"><label>Email:</label> <span><?php echo htmlspecialchars($supervisor['email']); ?></span></div>
    <div class="info"><label>Phone:</label> <span><?php echo htmlspecialchars($supervisor['phone']); ?></span></div>
    <div class="info"><label>Block:</label> <span><?php echo htmlspecialchars($supervisor['block']); ?></span></div>
    <div class="info"><label>NID Photo:</label> 
        <span><a href="uploads/nid/<?php echo $supervisor['nid_photo']; ?>" target="_blank" class="btn">View NID</a></span>
    </div>

    <div class="top-links">
        <a href="admin_supervisors.php" class="btn">Back</a>
        <a href="admin_view_supervisor.php?id=<?php echo $id; ?>&remove=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure to remove this supervisor?')">Remove</a>
    </div>
</div>

</body>
</html>
