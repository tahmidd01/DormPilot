<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Approve admission
if(isset($_GET["approve"])){
    $id = $_GET["approve"];
    $stmt = $conn->prepare("UPDATE admissions SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Admission approved!";
    }
    $stmt->close();
}

// Reject admission
if(isset($_GET["reject"])){
    $id = $_GET["reject"];
    $stmt = $conn->prepare("UPDATE admissions SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Admission rejected!";
    }
    $stmt->close();
}

$admissions = $conn->query("SELECT * FROM admissions ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admission Requests - Admin</title>
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
}
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}

body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
}
body.with-admin-sidebar {
    margin-left:260px;
}
.main-content {
    flex:1;
    padding:20px;
}
.top-bar {
    display:flex;
    justify-content:flex-end;
    align-items:center;
    margin-bottom:20px;
}
.top-bar .logout {
    padding:10px 18px; 
    font-size:18px; 
    border-radius:5px; 
    display:flex; 
    align-items:center;
    background:#e24a84; 
    color:#fff; 
    border:none; 
    cursor:pointer;
}
.top-bar .logout img {
    width:20px; 
    height:20px; 
    margin-right:8px;
}
.container {
    max-width:1200px;
    margin:0 auto;
    padding:0 20px;
}
.list-section {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.list-section h2 {
    margin-bottom:20px;
    color:#333;
}
.message {
    padding:10px;
    margin-bottom:15px;
    border-radius:5px;
    background:#d4edda;
    color:#155724;
}
table {
    width:100%;
    border-collapse:collapse;
}
table th, table td {
    padding:12px;
    text-align:left;
    border-bottom:1px solid #ddd;
}
table th {
    background:#f8f9fa;
    font-weight:bold;
}
.btn {
    padding:8px 15px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:13px;
    text-decoration:none;
    display:inline-block;
    margin-right:5px;
}
.btn-approve {
    background:#e24a84;
    color:white;
}
.btn-reject {
    background:#dc3545;
    color:white;
}
.status-badge {
    padding:5px 10px;
    border-radius:3px;
    font-size:12px;
    font-weight:bold;
}
.status-pending {
    background:#fff3cd;
    color:#856404;
}
.status-approved {
    background:#d1e7dd;
    color:#0f5132;
}
.status-rejected {
    background:#f8d7da;
    color:#721c24;
}
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
    
    <div class="list-section">
        <h2>All Admission Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Preferred Room</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $admissions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo htmlspecialchars($row["phone"]); ?></td>
                    <td><?php echo htmlspecialchars($row["course"]); ?></td>
                    <td><?php echo htmlspecialchars($row["preferred_room"] ?? "-"); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"]; ?>"><?php echo ucfirst($row["status"]); ?></span></td>
                    <td><?php echo date("Y-m-d", strtotime($row["created_at"])); ?></td>
                    <td>
                        <?php if($row["status"] == "pending"): ?>
                        <a href="?approve=<?php echo $row["id"]; ?>" class="btn btn-approve">Approve</a>
                        <a href="?reject=<?php echo $row["id"]; ?>" class="btn btn-reject">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>

