<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Check if already submitted
$existing = $conn->query("SELECT * FROM admissions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Submit admission request
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_admission"])){

    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $father_name = $_POST["father_name"];
    $father_phone = $_POST["father_phone"];
    $mother_name = $_POST["mother_name"];
    $mother_phone = $_POST["mother_phone"];
    $preferred_room = $_POST["preferred_room"];

    // upload folder
    $upload_dir = "uploads/admission/";
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0777, true);
    }

    // Profile Pic
    $profile_pic = time()."_profile_".$_FILES["profile_pic"]["name"];
    move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $upload_dir.$profile_pic);

    // NID Pic
    $nid_pic = time()."_nid_".$_FILES["nid_pic"]["name"];
    move_uploaded_file($_FILES["nid_pic"]["tmp_name"], $upload_dir.$nid_pic);

    // Documents
    $documents = time()."_docs_".$_FILES["documents"]["name"];
    move_uploaded_file($_FILES["documents"]["tmp_name"], $upload_dir.$documents);

    $stmt = $conn->prepare(
        "INSERT INTO admissions 
        (user_id, name, phone, email, father_name, father_phone, mother_name, mother_phone, preferred_room, profile_pic, nid_pic, documents) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isssssssssss",
        $user_id,
        $name,
        $phone,
        $email,
        $father_name,
        $father_phone,
        $mother_name,
        $mother_phone,
        $preferred_room,
        $profile_pic,
        $nid_pic,
        $documents
    );

    if($stmt->execute()){
        $message = "Admission request submitted successfully!";
        $existing = $conn->query("SELECT * FROM admissions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
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
.status-box {
    background:#fff3cd;
    padding:15px;
    border-radius:5px;
    margin-bottom:20px;
    text-align:center;
}
.status-box strong {
    color:#856404;
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

    <div class="admission-form">
        <h2>Admission Form</h2>

        <?php if($message != ""): ?>
            <div class="success-msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if($existing): ?>
        <div class="status-box">
            <strong>You have already submitted an admission request.</strong><br>
            Status: <strong><?php echo ucfirst($existing["status"]); ?></strong><br>
            Submitted on: <?php echo date("Y-m-d H:i", strtotime($existing["created_at"])); ?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($fullname); ?>" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_SESSION["email"] ?? ""); ?>" required>
            <input type="text" name="father_name" placeholder="Father's Name" required>
            <input type="text" name="father_phone" placeholder="Father's Phone" required>
            <input type="text" name="mother_name" placeholder="Mother's Name" required>
            <input type="text" name="mother_phone" placeholder="Mother's Phone" required>
            <input type="text" name="preferred_room" placeholder="Preferred Room">

            <!-- Upload Fields -->
            <label style="margin-top:10px;">Profile Picture:</label>
            <input type="file" name="profile_pic" accept="image/*" required>

            <label style="margin-top:10px;">NID Picture:</label>
            <input type="file" name="nid_pic" accept="image/*" required>

            <label style="margin-top:10px;">Documents (PDF/ZIP):</label>
            <input type="file" name="documents" accept=".pdf,.zip" required>

            <button type="submit" name="submit_admission" <?php echo $existing ? "disabled" : ""; ?>>Submit Admission</button>
        </form>
    </div>
</div>

</body>
</html>
