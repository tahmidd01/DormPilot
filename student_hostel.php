<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION["fullname"];

// Get rooms from database
$rooms_db = $conn->query("SELECT * FROM rooms ORDER BY room_number");
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
.top-bar .logout {background:#e24a84; color:#fff; border:none; margin-right:20px; cursor:pointer; display:flex; align-items:center;}
.top-bar .logout img, .top-bar .announcement img {width:20px; height:20px; margin-right:8px;}
.top-bar .announcement {background:#ffd6f2; display:flex; align-items:center; color:#333; text-decoration:none;}
.top-bar .announcement:hover {background:#e6a1e6; color:#fff;}
.room-card {width:300px; border:1px solid #ddd; border-radius:10px; margin:15px; padding:10px; text-align:center; background:#fff; box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.room-card img {width:100%; border-radius:10px; margin-bottom:10px; max-height:200px; object-fit:cover;}
.room-container {display:flex; flex-wrap:wrap; justify-content:center;}
.room-card h3 {color:#e24a84; margin-bottom:10px;}
.room-card p {color:#666; margin:5px 0;}
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

    <h2 style="text-align:center; margin-bottom:20px; color:#e24a84;">Hostel Rooms</h2>
    <div class="room-container">
        <?php if($rooms_db && $rooms_db->num_rows > 0): ?>
            <?php while($room = $rooms_db->fetch_assoc()): 
                $occupied = $conn->query("SELECT COUNT(*) as count FROM room_assignments WHERE room_id = ".$room["id"])->fetch_assoc()["count"];
                $available = $room["capacity"] - $occupied;
            ?>
            <div class="room-card">
                <?php if($room["image"]): ?>
                <img src="uploads/rooms/<?php echo htmlspecialchars($room["image"]); ?>" alt="Room">
                <?php else: ?>
                <img src="room/room1.jpg" alt="Room">
                <?php endif; ?>
                <h3>Room <?php echo htmlspecialchars($room["room_number"]); ?></h3>
                <p><strong>Block:</strong> <?php echo htmlspecialchars($room["block"] ?? "N/A"); ?></p>
                <p><strong>Capacity:</strong> <?php echo $room["capacity"]; ?> beds</p>
                <p><strong>Available:</strong> <?php echo $available; ?> seats</p>
                <p><strong>Status:</strong> <?php echo $available > 0 ? "Available" : "Full"; ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <?php
            // Fallback to static rooms if database is empty
            $rooms = [
                ["img"=>"room/room1.jpg", "desc"=>"4 bunker beds", "seats"=>3, "price"=>200],
                ["img"=>"room/room2.jpeg", "desc"=>"3 single beds", "seats"=>2, "price"=>150],
                ["img"=>"room/room3.jpeg", "desc"=>"2 double beds", "seats"=>4, "price"=>180],
                ["img"=>"room/room4.jpg", "desc"=>"5 bunk beds", "seats"=>3, "price"=>220],
                ["img"=>"room/room5.jpeg", "desc"=>"3 single beds", "seats"=>2, "price"=>160],
                ["img"=>"room/room6.webp", "desc"=>"4 bunk beds", "seats"=>3, "price"=>200],
            ];
            foreach($rooms as $room):
            ?>
            <div class="room-card">
                <img src="<?php echo $room["img"]; ?>" alt="Room">
                <h3>Room Type</h3>
                <p><strong>Description:</strong> <?php echo $room["desc"]; ?></p>
                <p><strong>Available seats:</strong> <?php echo $room["seats"]; ?></p>
                <p><strong>Price:</strong> $<?php echo $room["price"]; ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

