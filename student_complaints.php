<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Create uploads directory if it doesn't exist
if(!file_exists("uploads")){
    mkdir("uploads", 0777, true);
}

// Submit complaint
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_complaint"])){
    $type = $_POST["type"];
    $description = $_POST["description"];
    $photo = "";
    
    if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0){
        $allowed = ["jpg", "jpeg", "png", "gif"];
        $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)){
            $photo = time() . "_" . basename($_FILES["photo"]["name"]);
            move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/" . $photo);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO complaints (user_id, type, description, photo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $type, $description, $photo);
    
    if($stmt->execute()){
        $message = "Complaint submitted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

$my_complaints = $conn->query("SELECT c.*, u.fullname as supervisor_name FROM complaints c LEFT JOIN users u ON c.supervisor_id = u.id WHERE c.user_id = $user_id ORDER BY c.created_at DESC");
$fullname = $_SESSION["fullname"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complain</title>
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
.top-bar .announcement {background:#ffd6f2; display:flex; align-items:center; color:#333; text-decoration:none;}
.top-bar .announcement:hover {background:#e6a1e6; color:#fff;}

.complain-form {
    background: linear-gradient(to bottom right, #fff0f5, #ffe6f0);
    padding:30px 40px;
    border-radius:15px;
    max-width:700px;
    margin:auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.complain-form h2 {
    text-align:center;
    margin-bottom:30px;
    color:#e24a84;
}
.complain-form input, .complain-form select, .complain-form textarea {
    width:100%;
    padding:12px 15px;
    margin-top:8px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:16px;
}
.complain-form textarea {
    min-height:100px;
    resize:vertical;
}
.complain-form input::placeholder, .complain-form textarea::placeholder {
    color:#999;
}
.complain-form input:focus, .complain-form select:focus, .complain-form textarea:focus {
    border-color:#e24a84;
    outline:none;
    box-shadow:0 0 5px rgba(226, 74, 132,0.3);
}
.complain-form button {
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
.complain-form button:hover {
    background:#ad276a;
}
.success-msg {
    text-align:center;
    color:green;
    margin-bottom:20px;
    font-weight:bold;
    font-size:18px;
}
.complaints-list {
    margin-top:30px;
    max-width:700px;
    margin-left:auto;
    margin-right:auto;
}
.complaint-card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    margin-bottom:15px;
}
.complaint-card h3 {
    color:#e24a84;
    margin-bottom:10px;
}
.complaint-card p {
    color:#333;
    margin:5px 0;
}
.complaint-photo {
    max-width:150px;
    max-height:150px;
    border-radius:8px;
    margin-top:10px;
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
.status-in-progress {background:#cfe2ff; color:#084298;}
.status-resolved {background:#d1e7dd; color:#0f5132;}
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

    <div class="complain-form">
        <h2>Submit Complaint</h2>

        <?php if($message != ""): ?>
            <div class="success-msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <select name="type" required>
                <option value="">Select Complaint Type</option>
                <option value="plumbing">Plumbing</option>
                <option value="electricity">Electricity</option>
                <option value="cleaning">Cleaning</option>
                <option value="noise">Noise</option>
                <option value="other">Other</option>
            </select>
            <textarea name="description" placeholder="Describe your complaint" required></textarea>
            <input type="file" name="photo" accept="image/*">
            <button type="submit" name="submit_complaint">Submit Complaint</button>
        </form>
    </div>

    <div class="complaints-list">
        <h2 style="text-align:center; color:#e24a84; margin-bottom:20px;">My Complaints</h2>
        <?php while($row = $my_complaints->fetch_assoc()): ?>
        <div class="complaint-card">
            <h3><?php echo htmlspecialchars($row["type"]); ?></h3>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row["description"])); ?></p>
            <?php if($row["photo"]): ?>
            <img src="uploads/<?php echo htmlspecialchars($row["photo"]); ?>" alt="Photo" class="complaint-photo">
            <?php endif; ?>
            <div>
                <span class="status-badge status-<?php echo $row["status"]; ?>"><?php echo ucfirst($row["status"]); ?></span>
            </div>
            <?php if($row["status"] == "resolved" && !empty($row["resolution_description"])): ?>
            <div style="margin-top:15px; padding:15px; background:#e8f5e9; border-radius:8px; border-left:4px solid #4caf50;">
                <p style="font-weight:bold; color:#2e7d32; margin-bottom:8px;">Resolution Update:</p>
                <p style="color:#333;"><?php echo nl2br(htmlspecialchars($row["resolution_description"])); ?></p>
                <?php if(!empty($row["supervisor_name"])): ?>
                <p style="font-size:12px; color:#666; margin-top:8px;">Resolved by: <?php echo htmlspecialchars($row["supervisor_name"]); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <p style="font-size:12px; color:#999; margin-top:10px;">Submitted: <?php echo date("Y-m-d H:i", strtotime($row["created_at"])); ?></p>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
