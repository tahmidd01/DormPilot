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
<title>Admission</title>
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
/* Admission Form Styling */
.admission-form {
    background: linear-gradient(to bottom right, #fff0f5, #ffe6f0);
    padding:30px 40px;
    border-radius:15px;
    max-width:700px;
    margin:auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.admission-form h2 {
    text-align:center;
    margin-bottom:30px;
    color:#e24a84;
}
.admission-form input, .admission-form select {
    width:100%;
    padding:12px 15px;
    margin-top:8px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:16px;
}
.admission-form input::placeholder, .admission-form select::placeholder {
    color:#999;
}
.admission-form input:focus, .admission-form select:focus {
    border-color:#e24a84;
    outline:none;
    box-shadow:0 0 5px rgba(226, 74, 132,0.3);
}
.admission-form button {
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
.admission-form button:hover {
    background:#ad276a;
}
.success-msg {
    text-align:center;
    color:green;
    margin-bottom:20px;
    font-weight:bold;
    font-size:18px;
}
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

    <div class="admission-form">
        <h2>Admission Form</h2>

        <?php
        if(isset($_GET['success']) && $_GET['success'] == 1){
            echo '<div class="success-msg">Admission submitted successfully!</div>';
        }
        ?>

        <form action="admission_process.php" method="post">
            <input type="text" id="name" name="name" placeholder="Full Name" required>
            <input type="text" id="phone" name="phone" placeholder="Phone Number" required>
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="text" id="nid" name="nid" placeholder="NID Number" required>
            <select id="course" name="course" required>
                <option value="" disabled selected>Select Course</option>
                <option value="CSE">CSE</option>
                <option value="EEE">EEE</option>
                <option value="BBA">BBA</option>
            </select>
            <input type="text" id="father" name="father" placeholder="Father's Name" required>
            <input type="text" id="father_phone" name="father_phone" placeholder="Father's Phone" required>
            <input type="text" id="mother" name="mother" placeholder="Mother's Name" required>
            <input type="text" id="mother_phone" name="mother_phone" placeholder="Mother's Phone" required>
            <input type="text" id="room" name="room" placeholder="Preferred Room">
            <button type="submit">Submit Admission</button>
        </form>
    </div>
</div>

</body>
</html>
