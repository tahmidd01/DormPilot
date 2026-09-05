<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student room from room_assignments or profile
$room_query = "SELECT r.room_number 
               FROM room_assignments ra 
               JOIN rooms r ON ra.room_id = r.id 
               WHERE ra.user_id = $user_id 
               LIMIT 1";
$room_result = $conn->query($room_query);
$assigned_room = null;
if($room_result && $room_result->num_rows > 0){
    $assigned_room = $room_result->fetch_assoc()["room_number"];
} else {
    // Fallback to profile table
    $profile = $conn->query("SELECT room FROM profile WHERE user_id = $user_id")->fetch_assoc();
    $assigned_room = $profile["room"] ?? null;
}

$fullname = $_SESSION["fullname"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
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
.top-bar .logout, .top-bar .announcement {
    padding:10px 18px; 
    font-size:18px; 
    border-radius:5px; 
    display:flex; 
    align-items:center;
}
.top-bar .logout {
    background:#e24a84; 
    color:#fff; 
    border:none; 
    margin-right:20px;
    cursor:pointer;
}
.top-bar .logout img, .top-bar .announcement img {
    width:20px; 
    height:20px; 
    margin-right:8px;
}

.top-bar .announcement {
    background:#ffd6f2;
    color:#333;
    text-decoration:none;
}
.top-bar .announcement:hover {
    background:#e6a1e6;
    color:#fff;
}

.dashboard-title {
    font-size:90px; 
    font-weight:bold;
    text-align:center;
    margin-top:100px;
    margin-bottom:20px;
    color:#333;
}
.dashboard-welcome {
    font-size:45px; 
    text-align:center;
    margin-top:20px;
    color:#555;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="menu">
        <a href="student_dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
        <a href="student_admission.php"><img src="icons/admission.png" class="menu-icon">Admission</a>
        <a href="student_room_change.php"><img src="icons/room.png" class="menu-icon">Room Change</a>
        <a href="student_roommates.php"><img src="icons/profile.png" class="menu-icon">Room & Roommates</a>
        <a href="student_chat.php"><img src="icons/supervisor.png" class="menu-icon">Supervisor</a>
        <a href="student_hostel.php"><img src="icons/hostel.png" class="menu-icon">View Hostel</a>
        <a href="student_complaints.php"><img src="icons/complain.png" class="menu-icon">Complain</a>
        <a href="student_announcements.php"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post" style="margin:0;">
            <button type="submit" class="logout">
                <img src="icons/logout.png" alt="Logout Icon"> Logout
            </button>
        </form>
        <a href="student_announcements.php" class="announcement">
            <img src="icons/announcement.png" alt="Announcement Icon"> Announcement
        </a>
    </div>

    <div class="dashboard-title">Dashboard</div>
    <div class="dashboard-welcome">Hello, <?php echo htmlspecialchars($fullname); ?>!</div>
</div>

</body>
</html>
