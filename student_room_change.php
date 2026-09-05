<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Get current room from room_assignments or profile
$room_query = "SELECT r.room_number 
               FROM room_assignments ra 
               JOIN rooms r ON ra.room_id = r.id 
               WHERE ra.user_id = $user_id 
               LIMIT 1";
$room_result = $conn->query($room_query);
$current_room = "";
if($room_result && $room_result->num_rows > 0){
    $current_room = $room_result->fetch_assoc()["room_number"];
} else {
    // Fallback to profile table
    $profile = $conn->query("SELECT room FROM profile WHERE user_id = $user_id")->fetch_assoc();
    $current_room = $profile["room"] ?? "";
}

// Submit room change request
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_request"])){
    $student_name = $_POST["student_name"];
    $student_id = $_POST["student_id"];
    $phone = $_POST["phone"];
    $current_room_val = $_POST["current_room"];
    $new_room = $_POST["new_room"];
    $reason = $_POST["reason"];
    
    $stmt = $conn->prepare("INSERT INTO room_change_requests (user_id, student_name, student_id, phone, current_room, new_room, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $student_name, $student_id, $phone, $current_room_val, $new_room, $reason);
    
    if($stmt->execute()){
        $message = "Room change request submitted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

$my_requests = $conn->query("SELECT * FROM room_change_requests WHERE user_id = $user_id ORDER BY request_date DESC");
$fullname = $_SESSION["fullname"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Change</title>
<style>
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}

body {
    display:flex;
    min-height:100vh;
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
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
.sidebar img.logo {width:200px; height:auto; margin-bottom:20px;}
.sidebar .menu {width:100%;}
.sidebar .menu a {display:flex; align-items:center; padding:14px 20px; color:#333; text-decoration:none; font-weight:bold; font-size:18px; margin:6px 0; border-radius:6px; transition:0.3s;}
.sidebar .menu a:hover {background:#ad276a; color:#fff;}
.sidebar .menu a img.menu-icon {width:28px; height:28px; margin-right:10px;}

.main-content {flex:1; padding:20px;}

.top-bar {display:flex; justify-content:flex-end; align-items:center; margin-bottom:20px;}
.top-bar .logout, .top-bar .announcement {padding:10px 18px; font-size:18px; border-radius:5px; display:flex; align-items:center;}
.top-bar .logout {background:#e24a84; color:#fff; border:none; margin-right:20px; cursor:pointer; display:flex; align-items:center;}
.top-bar .logout img, .top-bar .announcement img {width:20px; height:20px; margin-right:8px;}
.top-bar .announcement {background:#ffd6f2; display:flex; align-items:center;}

.top-bar .announcement {
    background:#ffd6f2;
    color:#333;
    text-decoration:none;
}
.top-bar .announcement:hover {
    background:#e6a1e6;
    color:#fff;
}

.roomchange-form {
    background: linear-gradient(to bottom right, #e0f7fa, #fff0f5);
    padding:30px 40px;
    border-radius:15px;
    max-width:600px;
    margin:auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.roomchange-form h2 {
    text-align:center;
    margin-bottom:30px;
    color:#e24a84;
}
.roomchange-form input, .roomchange-form textarea {
    width:100%;
    padding:12px 15px;
    margin-top:8px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:16px;
}
.roomchange-form input::placeholder, .roomchange-form textarea::placeholder {
    color:#999;
}
.roomchange-form input:focus, .roomchange-form textarea:focus {
    border-color:#e24a84;
    outline:none;
    box-shadow:0 0 5px rgba(226, 74, 132,0.3);
}
.roomchange-form button {
    margin-top:25px;
    padding:15px;
    width:100%;
    background:#e24a84;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:18px;
    font-weight:bold;
}
.roomchange-form button:hover {
    background:#ad276a;
}
.success-msg {
    text-align:center;
    color:green;
    margin-bottom:20px;
    font-weight:bold;
    font-size:18px;
}
.requests-list {
    margin-top:30px;
    max-width:900px;
    margin-left:auto;
    margin-right:auto;
}
.request-card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    margin-bottom:15px;
}
.request-card h3 {
    color:#e24a84;
    margin-bottom:15px;
    border-bottom:2px solid #e24a84;
    padding-bottom:10px;
}
.request-info {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-bottom:15px;
}
.request-info div {
    padding:10px;
    background:#f8f9fa;
    border-radius:5px;
}
.request-info label {
    display:block;
    font-weight:bold;
    color:#555;
    margin-bottom:5px;
    font-size:12px;
}
.request-info span {
    color:#333;
    font-size:14px;
}
.reason-box {
    margin-top:15px;
    padding:15px;
    background:#f0f4ff;
    border-left:4px solid #e24a84;
    border-radius:5px;
}
.reason-box label {
    display:block;
    font-weight:bold;
    color:#555;
    margin-bottom:8px;
}
.reason-box .reason-text {
    color:#333;
    line-height:1.6;
    white-space:pre-wrap;
    word-wrap:break-word;
}
.status-badge {
    display:inline-block;
    padding:5px 10px;
    border-radius:5px;
    font-size:12px;
    font-weight:bold;
    margin-top:10px;
}
.status-pending {background:#fff3cd; color:#856404;}
.status-approved {background:#d1e7dd; color:#0f5132;}
.status-rejected {background:#f8d7da; color:#721c24;}
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

    <div class="roomchange-form">
        <h2>Room Change Form</h2>

        <?php if($message != ""): ?>
            <div class="success-msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="student_name" placeholder="Student Name" value="<?php echo htmlspecialchars($fullname); ?>" required>
            <input type="text" name="student_id" placeholder="Student ID" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="text" name="current_room" placeholder="Current Room" value="<?php echo htmlspecialchars($current_room); ?>" required>
            <input type="text" name="new_room" placeholder="New Room" required>
            <textarea name="reason" placeholder="Reason for Change" rows="4" required></textarea>
            <button type="submit" name="submit_request">Submit Request</button>
        </form>
    </div>

    <?php if($my_requests->num_rows > 0): ?>
    <div class="requests-list">
        <h2 style="text-align:center; color:#e24a84; margin-bottom:20px;">My Room Change Requests</h2>
        <?php while($req = $my_requests->fetch_assoc()): ?>
        <div class="request-card">
            <h3>Request #<?php echo $req["id"]; ?></h3>
            <div class="request-info">
                <div>
                    <label>Current Room:</label>
                    <span><?php echo htmlspecialchars($req["current_room"]); ?></span>
                </div>
                <div>
                    <label>New Room:</label>
                    <span><?php echo htmlspecialchars($req["new_room"]); ?></span>
                </div>
                <div>
                    <label>Status:</label>
                    <span><span class="status-badge status-<?php echo $req["status"]; ?>"><?php echo ucfirst($req["status"]); ?></span></span>
                </div>
                <div>
                    <label>Request Date:</label>
                    <span><?php echo date("Y-m-d H:i", strtotime($req["request_date"])); ?></span>
                </div>
            </div>
            <div class="reason-box">
                <label>Reason for Change:</label>
                <div class="reason-text"><?php echo nl2br(htmlspecialchars($req["reason"])); ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
