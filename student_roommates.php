<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student's room from room_assignments or profile
$room_query = "SELECT r.room_number 
               FROM room_assignments ra 
               JOIN rooms r ON ra.room_id = r.id 
               WHERE ra.user_id = $user_id 
               LIMIT 1";
$room_result = $conn->query($room_query);
$room = null;
if($room_result && $room_result->num_rows > 0){
    $room = $room_result->fetch_assoc()["room_number"];
} else {
    // Fallback to profile table
    $profile = $conn->query("SELECT room FROM profile WHERE user_id = $user_id")->fetch_assoc();
    $room = $profile["room"] ?? null;
}

// Get roommates (students in same room)
$roommates = null;
if($room){
    // Get room ID first
    $room_id_query = $conn->query("SELECT id FROM rooms WHERE room_number = '".$conn->real_escape_string($room)."' LIMIT 1");
    if($room_id_query && $room_id_query->num_rows > 0){
        $room_id = $room_id_query->fetch_assoc()["id"];
        // Get all students assigned to this room
        $roommates = $conn->query("SELECT u.id, u.fullname, u.email, u.phone 
                                   FROM users u 
                                   JOIN room_assignments ra ON u.id = ra.user_id 
                                   WHERE ra.room_id = $room_id AND u.id != $user_id");
    }
    // Fallback to profile table if no room_assignments found
    if(!$roommates || $roommates->num_rows == 0){
        $roommates = $conn->query("SELECT u.id, u.fullname, u.email, u.phone FROM users u JOIN profile p ON u.id = p.user_id WHERE p.room = '".$conn->real_escape_string($room)."' AND u.id != $user_id");
    }
}

$fullname = $_SESSION["fullname"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room & Roommates</title>
<style>
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}

body {
    display:flex;
    min-height:100vh;
    background: linear-gradient(to bottom right, #f0f4ff, #ffe6f0);
}

.sidebar {
    width:260px;
    background: linear-gradient(to bottom, #d9cfff, #cfe7ff);
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:20px;
    border-right:2px solid #ddd;
}
.sidebar img.logo {width:200px; margin-bottom:20px;}
.sidebar a {
    width:100%;
    display:flex;
    align-items:center;
    padding:14px 20px;
    text-decoration:none;
    color:#333;
    font-weight:bold;
    font-size:18px;
    border-radius:6px;
    margin:6px 0;
}
.sidebar a:hover {
    background:#ad276a;
    color:#fff;
}
.sidebar img.menu-icon {
    width:28px;
    margin-right:10px;
}

.main-content {flex:1; padding:20px;}

.top-bar {
    display:flex;
    justify-content:flex-end;
    margin-bottom:20px;
}
.logout {
    background:#e24a84;
    color:#fff;
    border:none;
    padding:10px 18px;
    font-size:18px;
    border-radius:5px;
    cursor:pointer;
}

.room-info-box {
    max-width:700px;
    margin:50px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}
.room-info-box h2 {
    text-align:center;
    margin-bottom:30px;
    color:#e24a84;
    font-size:32px;
}

.info-row {
    display:flex;
    align-items:center;
    margin-bottom:18px;
    padding:15px;
    background:#f8f9fa;
    border-radius:8px;
}
.info-row label {
    width:180px;
    font-weight:bold;
    font-size:16px;
    color:#333;
}
.info-row p {
    flex:1;
    font-size:16px;
    color:#666;
}

.roommate-card {
    background:#f8f9fa;
    padding:20px;
    border-radius:10px;
    margin-bottom:15px;
    border-left:4px solid #e24a84;
}
.roommate-card h3 {
    color:#e24a84;
    margin-bottom:10px;
}
.roommate-card p {
    color:#666;
    margin:5px 0;
}
</style>
</head>

<body>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <a href="student_dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
    <a href="student_admission.php"><img src="icons/admission.png" class="menu-icon">Admission</a>
    <a href="student_room_change.php"><img src="icons/room.png" class="menu-icon">Room Change</a>
    <a href="student_roommates.php"><img src="icons/profile.png" class="menu-icon">Room & Roommates</a>
    <a href="student_chat.php"><img src="icons/supervisor.png" class="menu-icon">Supervisor</a>
    <a href="student_hostel.php"><img src="icons/hostel.png" class="menu-icon">View Hostel</a>
    <a href="student_complaints.php"><img src="icons/complain.png" class="menu-icon">Complain</a>
    <a href="student_announcements.php"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
</div>

<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post">
            <button class="logout">Logout</button>
        </form>
    </div>

    <div class="room-info-box">
        <h2>Room & Roommates</h2>

        <div class="info-row">
            <label>Room Number</label>
            <p><?php echo $room ? htmlspecialchars($room) : "Not assigned yet"; ?></p>
        </div>

        <h3 style="margin-top:30px; margin-bottom:15px; color:#e24a84;">Your Roommates</h3>
        
        <?php if($room && $roommates && $roommates->num_rows > 0): ?>
            <?php while($mate = $roommates->fetch_assoc()): ?>
            <div class="roommate-card">
                <h3><?php echo htmlspecialchars($mate["fullname"]); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($mate["email"]); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($mate["phone"] ?? "-"); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="roommate-card">
                <p style="color:#666;">No roommates found. <?php echo $room ? "You are the only one in this room." : "You need to be assigned to a room first."; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
