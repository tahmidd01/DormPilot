<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];
$message = "";

// Add announcement
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_announcement"])){
    $title = $_POST["title"];
    $message_text = $_POST["message"];
    
    $stmt = $conn->prepare("INSERT INTO announcements (supervisor_id, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $supervisor_id, $title, $message_text);
    
    if($stmt->execute()){
        $message = "Announcement sent successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

$announcements = $conn->query("SELECT a.*, u.fullname FROM announcements a LEFT JOIN users u ON a.supervisor_id = u.id ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send Announcements - Supervisor</title>
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
    max-width:1000px;
    margin:0 auto;
    padding:0 20px;
}
.form-section, .list-section {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.form-section h2, .list-section h2 {
    margin-bottom:20px;
    color:#333;
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
.form-group input, .form-group textarea {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
    font-family:Arial;
}
.form-group textarea {
    height:120px;
    resize:vertical;
}
.btn {
    padding:10px 20px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
}
.message {
    padding:10px;
    margin-bottom:15px;
    border-radius:5px;
    background:#d4edda;
    color:#155724;
}
.announcement-item {
    padding:15px;
    background:#f8f9fa;
    border-radius:5px;
    margin-bottom:15px;
    border-left:4px solid #28a745;
}
.announcement-item h3 {
    margin-bottom:8px;
    color:#333;
}
.announcement-item p {
    color:#666;
    margin-bottom:5px;
}
.announcement-item .meta {
    font-size:12px;
    color:#999;
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
    
    <div class="form-section">
        <h2>Create New Announcement</h2>
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" required></textarea>
            </div>
            <button type="submit" name="add_announcement" class="btn">Send Announcement</button>
        </form>
    </div>

    <div class="list-section">
        <h2>Previous Announcements</h2>
        <?php while($row = $announcements->fetch_assoc()): ?>
        <div class="announcement-item">
            <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($row["message"])); ?></p>
            <div class="meta">
                By: <?php echo htmlspecialchars($row["fullname"] ?? "Admin"); ?> | 
                Date: <?php echo date("Y-m-d H:i", strtotime($row["created_at"])); ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</div>

</body>
</html>

