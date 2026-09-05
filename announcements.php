<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$announcements = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Announcements</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial;}

body{display:flex;min-height:100vh;background:#f0f4ff;}


.sidebar{
    width:260px;background: linear-gradient(to bottom, #d9cfff, #cfe7ff);
    display:flex;flex-direction:column;align-items:center;padding:20px;border-right:2px solid #ddd;
}
.sidebar img.logo{width:200px;margin-bottom:20px;}
.sidebar a{
    width:100%;display:flex;align-items:center;padding:14px 20px;text-decoration:none;color:#333;font-weight:bold;font-size:18px;border-radius:6px;margin:6px 0;
}
.sidebar a:hover{background:#ad276a;color:#fff;}
.sidebar img.menu-icon{width:28px;margin-right:10px;}


.main-content{flex:1;padding:20px;}
.top-bar{display:flex;justify-content:flex-end;margin-bottom:20px;}
.logout{background:#e24a84;color:#fff;border:none;padding:10px 18px;font-size:18px;border-radius:5px;cursor:pointer;}


.announcements{
    max-width:700px;
    margin:20px auto;
}
.announcement-card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    margin-bottom:15px;
}
.announcement-card h3{color:#e24a84;margin-bottom:10px;}
.announcement-card p{color:#333;line-height:1.5;}
.announcement-card .time{font-size:12px;color:#999;margin-top:10px;text-align:right;}
</style>
</head>
<body>


<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <a href="dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
    <a href="admission.php"><img src="icons/admission.png" class="menu-icon">Admission</a>
    <a href="roomchange.php"><img src="icons/room.png" class="menu-icon">Room Change</a>
    <a href="profile.php"><img src="icons/profile.png" class="menu-icon">Profile</a>
    <a href="supervisor.php"><img src="icons/supervisor.png" class="menu-icon">Supervisor</a>
    <a href="hostel.php"><img src="icons/hostel.png" class="menu-icon">View Hostel</a>
    <a href="complain.php"><img src="icons/complain.png" class="menu-icon">Complain</a>
    <a href="announcements.php"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
</div>


<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post">
            <button class="logout">Logout</button>
        </form>
    </div>

    <div class="announcements">
        <h2 style="text-align:center;color:#e24a84;margin-bottom:20px;">Announcements</h2>

        <?php if(count($announcements) > 0): ?>
            <?php foreach($announcements as $ann): ?>
                <div class="announcement-card">
                    <h3><?php echo htmlspecialchars($ann['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($ann['message'])); ?></p>
                    <div class="time"><?php echo date("d M Y, H:i", strtotime($ann['created_at'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;color:#666;">No announcements yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
