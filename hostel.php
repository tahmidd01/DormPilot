<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION["fullname"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Hostel</title>
<style>
* {box-sizing:border-box; margin:0; padding:0; font-family: Arial;}
body {display:flex; min-height:100vh; background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);}
.sidebar {width:260px; background: linear-gradient(to bottom, #e6d4ff, #cfe7ff); display:flex; flex-direction:column; align-items:center; padding:20px; border-right:2px solid #ddd;}
.sidebar img.logo {width:200px; height:auto; margin-bottom:20px;}
.sidebar .menu {width:100%;}
.sidebar .menu a {display:flex; align-items:center; padding:14px 20px; color:#333; text-decoration:none; font-weight:bold; font-size:18px; margin:6px 0; border-radius:6px; transition:0.3s;}
.sidebar .menu a:hover {background:#ad276a; color:#fff;}
.sidebar .menu a img.menu-icon {width:28px; height:28px; margin-right:10px;}
.main-content {flex:1; padding:20px;}
.top-bar {display:flex; justify-content:flex-end; align-items:center; margin-bottom:20px;}
.top-bar .logout, .top-bar .announcement {padding:10px 18px; font-size:18px; border-radius:5px; display:flex; align-items:center;}
.top-bar .logout {background:#e24a84; color:#fff; border:none; margin-right:20px;}
.top-bar .logout img, .top-bar .announcement img {width:20px; height:20px; margin-right:8px;}
/* Announcement link styling */
.top-bar .announcement {
    background:#ffd6f2;
    color:#333;
    text-decoration:none;
}
.top-bar .announcement:hover {
    background:#e6a1e6;
    color:#fff;
}
.room-card {width:300px; border:1px solid #ddd; border-radius:10px; margin:15px; padding:10px; text-align:center; background:#fff;}
.room-card img {width:100%; border-radius:10px; margin-bottom:10px;}
.room-container {display:flex; flex-wrap:wrap; justify-content:center;}
</style>
</head>
<body>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="menu">
        <a href="dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
        <a href="admission.php"><img src="icons/admission.png" class="menu-icon">Admission</a>
        <a href="roomchange.php"><img src="icons/room.png" class="menu-icon">Room Change</a>
        <a href="profile.php"><img src="icons/profile.png" class="menu-icon">Profile</a>
        <a href="supervisor.php"><img src="icons/supervisor.png" class="menu-icon">Supervisor</a>
        <a href="hostel.php"><img src="icons/hostel.png" class="menu-icon">View Hostel</a>
        <a href="complain.php"><img src="icons/complain.png" class="menu-icon">Complain</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post" style="margin:0;">
            <button type="submit" class="logout">
                <img src="icons/logout.png" alt="Logout Icon"> Logout
            </button>
        </form>
         <a href="announcements.php" class="announcement">
            <img src="icons/announcement.png" alt="Announcement Icon"> Announcement
        </a>
    </div>

    <h2 style="text-align:center; margin-bottom:20px;">Hostel Rooms</h2>
    <div class="room-container">
        <?php
       
        $rooms = [
            ["img"=>"room/room1.jpg", "desc"=>"4 bunker beds", "seats"=>3, "price"=>200],
            ["img"=>"room/room2.jpeg", "desc"=>"3 single beds", "seats"=>2, "price"=>150],
            ["img"=>"room/room3.jpeg", "desc"=>"2 double beds", "seats"=>4, "price"=>180],
            ["img"=>"room/room4.jpg", "desc"=>"5 bunk beds", "seats"=>3, "price"=>220],
            ["img"=>"room/room5.jpeg", "desc"=>"3 single beds", "seats"=>2, "price"=>160],
            ["img"=>"room/room6.webp", "desc"=>"4 bunk beds", "seats"=>3, "price"=>200],
        ];

        foreach($rooms as $room){
            echo '<div class="room-card">
                    <img src="'.$room["img"].'" alt="Room">
                    <p><strong>Description:</strong> '.$room["desc"].'</p>
                    <p><strong>Available seats:</strong> '.$room["seats"].'</p>
                    <p><strong>Price:</strong> $'.$room["price"].'</p>
                  </div>';
        }
        ?>
    </div>
</div>

</body>
</html>
