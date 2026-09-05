<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$message = "";

// Approve room change
if(isset($_GET["approve"])){
    $id = $_GET["approve"];
    $stmt = $conn->prepare("UPDATE room_change_requests SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Room change request approved!";
    }
    $stmt->close();
}

// Reject room change
if(isset($_GET["reject"])){
    $id = $_GET["reject"];
    $stmt = $conn->prepare("UPDATE room_change_requests SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Room change request rejected!";
    }
    $stmt->close();
}

$requests = $conn->query("SELECT r.*, u.fullname, u.email FROM room_change_requests r JOIN users u ON r.user_id = u.id ORDER BY r.request_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Change Requests - Supervisor</title>
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
body.with-supervisor-sidebar {
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
.reason-cell {
    max-width:300px;
    word-wrap:break-word;
    white-space:normal;
    line-height:1.4;
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
</style>
</head>
<body class="with-supervisor-sidebar">

<?php include "supervisor_sidebar.php"; ?>

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
        <h2>All Room Change Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Current Room</th>
                    <th>New Room</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                    <td><?php echo htmlspecialchars($row["current_room"]); ?></td>
                    <td><?php echo htmlspecialchars($row["new_room"]); ?></td>
                    <td class="reason-cell"><?php echo nl2br(htmlspecialchars($row["reason"])); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"]; ?>"><?php echo ucfirst($row["status"]); ?></span></td>
                    <td><?php echo date("Y-m-d", strtotime($row["request_date"])); ?></td>
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

