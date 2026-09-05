<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$fullname = $_SESSION["fullname"] ?? "";
$email    = $_SESSION["email"] ?? "";
$phone    = $_SESSION["phone"] ?? "";
$room     = $_SESSION["room"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial;}

body{
    display:flex;
    min-height:100vh;
    background: linear-gradient(to bottom right, #f0f4ff, #ffe6f0);
}

/* Sidebar */
.sidebar{
    width:260px;
    background: linear-gradient(to bottom, #d9cfff, #cfe7ff);
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:20px;
    border-right:2px solid #ddd;
}
.sidebar img.logo{width:200px;margin-bottom:20px;}
.sidebar a{
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
.sidebar a:hover{
    background:#ad276a;
    color:#fff;
}
.sidebar img.menu-icon{
    width:28px;
    margin-right:10px;
}

/* Main */
.main-content{flex:1;padding:20px;}

.top-bar{
    display:flex;
    justify-content:flex-end;
    margin-bottom:20px;
}
.logout{
    background:#e24a84;
    color:#fff;
    border:none;
    padding:10px 18px;
    font-size:18px;
    border-radius:5px;
    cursor:pointer;
}

/* Profile Form */
.profile-box{
    max-width:700px;
    margin:50px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}
.profile-box h2{
    text-align:center;
    margin-bottom:30px;
    color:#e24a84;
    font-size:32px;
}

.form-row{
    display:flex;
    align-items:center;
    margin-bottom:18px;
}
.form-row label{
    width:180px;
    font-weight:bold;
    font-size:16px;
}
.form-row input{
    flex:1;
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:16px;
}
.form-row input:focus{
    outline:none;
    border-color:#e24a84;
    box-shadow:0 0 5px rgba(226,74,132,0.4);
}

.save-btn{
    width:100%;
    margin-top:25px;
    padding:14px;
    background:#e24a84;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:18px;
    font-weight:bold;
    cursor:pointer;
}
.save-btn:hover{
    background:#ad276a;
}
.success-msg{
    text-align:center;
    color:green;
    font-weight:bold;
    margin-bottom:20px;
}
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
</div>


<div class="main-content">

    <div class="top-bar">
        <form action="logout.php" method="post">
            <button class="logout">Logout</button>
        </form>
    </div>

    <div class="profile-box">
        <h2>My Profile</h2>

        <?php
        if(isset($_GET['success']) && $_GET['success'] == 1){
            echo '<div class="success-msg">Profile updated successfully!</div>';
        }
        ?>

        <form action="profile_process.php" method="post">
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>

            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-row">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>

            <div class="form-row">
                <label>Room Number</label>
                <input type="text" name="room" value="<?php echo htmlspecialchars($room); ?>">
            </div>

            <div class="form-row">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Change password">
            </div>

            <button class="save-btn">Update Profile</button>
        </form>
    </div>

</div>
</body>
</html>
