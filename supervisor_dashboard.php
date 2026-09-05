<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];

// Get statistics
$total_students = $conn->query("SELECT COUNT(DISTINCT u.id) as count FROM users u JOIN profile p ON u.id = p.user_id WHERE u.role='student'")->fetch_assoc()["count"];
$pending_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='pending'")->fetch_assoc()["count"];
$pending_room_changes = $conn->query("SELECT COUNT(*) as count FROM room_change_requests WHERE status='pending'")->fetch_assoc()["count"];
$my_tasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE supervisor_id = $supervisor_id AND status != 'resolved'")->fetch_assoc()["count"];

$fullname = $_SESSION["fullname"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supervisor Dashboard</title>
<style>
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}

body {
    display:flex;
    min-height:100vh;
    background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 50%, #f0f9ff 100%);
    font-family: Arial, sans-serif;
}

.sidebar {
    width:260px;
    background: linear-gradient(to bottom, #e6d4ff, #cfe7ff);
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:20px;
    border-right:2px solid #ddd;
    overflow-y:auto;
    overflow-x:hidden;
    -ms-overflow-style:none;
    scrollbar-width:none;
}
.sidebar::-webkit-scrollbar {
    width:0px;
    display:none;
}
.sidebar img.logo {
    width:200px;
    height:auto;
    margin-bottom:20px;
}
.sidebar .menu {
    width:100%;
}
.sidebar .menu a {
    display:flex; 
    align-items:center;
    padding:14px 20px; 
    color:#333;
    text-decoration:none;
    font-weight:bold;
    font-size:18px; 
    margin:6px 0;
    border-radius:6px;
    transition:0.3s;
}
.sidebar .menu a:hover {
    background:#ad276a;
    color:#fff;
}
.sidebar .menu a img.menu-icon {
    width:28px;  
    height:28px;
    margin-right:10px;
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

.dashboard-title {
    font-size:90px; 
    font-weight:bold;
    text-align:center;
    margin-top:50px;
    margin-bottom:20px;
    color:#333;
}
.dashboard-welcome {
    font-size:45px; 
    text-align:center;
    margin-top:20px;
    color:#555;
}

.stats {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:20px;
    margin-top:50px;
    max-width:1200px;
    margin-left:auto;
    margin-right:auto;
}
.stat-box {
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    text-align:center;
}
.stat-box h3 {
    color:#666;
    font-size:14px;
    margin-bottom:10px;
}
.stat-box .number {
    font-size:32px;
    font-weight:bold;
    color:#e24a84;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="menu">
        <a href="supervisor_dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
        <a href="supervisor_students.php"><img src="icons/profile.png" class="menu-icon">Students</a>
        <a href="supervisor_complaints.php"><img src="icons/complain.png" class="menu-icon">Complaints</a>
        <a href="supervisor_room_changes.php"><img src="icons/room.png" class="menu-icon">Room Changes</a>
        <a href="supervisor_announcements.php"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
        <a href="supervisor_tasks.php"><img src="icons/complain.png" class="menu-icon">Tasks</a>
        <a href="supervisor_chat.php"><img src="icons/supervisor.png" class="menu-icon">Chat</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post" style="margin:0;">
            <button type="submit" class="logout">
                <img src="icons/logout.png" alt="Logout Icon"> Logout
            </button>
        </form>
    </div>

    <div class="dashboard-title">Dashboard</div>
    <div class="dashboard-welcome">Hello, <?php echo htmlspecialchars($fullname); ?>!</div>

    <div class="stats">
        <div class="stat-box">
            <h3>Total Students</h3>
            <div class="number"><?php echo $total_students; ?></div>
        </div>
        <div class="stat-box">
            <h3>Pending Complaints</h3>
            <div class="number"><?php echo $pending_complaints; ?></div>
        </div>
        <div class="stat-box">
            <h3>Pending Room Changes</h3>
            <div class="number"><?php echo $pending_room_changes; ?></div>
        </div>
        <div class="stat-box">
            <h3>My Tasks</h3>
            <div class="number"><?php echo $my_tasks; ?></div>
        </div>
    </div>
</div>

</body>
</html>
