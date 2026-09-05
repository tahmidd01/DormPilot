<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$supervisor_id = 1; 


$sql = "SELECT * FROM messages WHERE 
        (sender_id='$user_id' AND receiver_id='$supervisor_id') OR
        (sender_id='$supervisor_id' AND receiver_id='$user_id')
        ORDER BY created_at ASC";

$result = mysqli_query($conn, $sql);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat with Supervisor</title>
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


.chat-box{
    max-width:700px;margin:20px auto;background:#fff;padding:20px;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,0.1);
}
.chat-box h2{text-align:center;color:#e24a84;margin-bottom:20px;}
.messages-container{
    max-height:400px;overflow-y:auto;padding:10px;background:#f5f5f5;border-radius:10px;margin-bottom:15px;display:flex;flex-direction:column;
}
.message{
    padding:10px 14px;border-radius:20px;margin-bottom:10px;max-width:70%;position:relative;word-wrap: break-word;
}
.student{background:#e24a84;color:#fff;align-self:flex-end;}
.supervisor{background:#ccc;color:#000;align-self:flex-start;}
.message .time{font-size:10px;color:#333;margin-top:5px;text-align:right;}

#chatForm{display:flex;}
#chatForm input[type="text"]{flex:1;padding:10px;border-radius:20px;border:1px solid #ccc;font-size:16px;}
#chatForm button{padding:10px 20px;background:#e24a84;color:#fff;border:none;border-radius:20px;font-size:16px;margin-left:10px;cursor:pointer;}
#chatForm button:hover{background:#ad276a;}
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

    <div class="chat-box">
        <h2>Chat with Supervisor</h2>

        <div class="messages-container" id="messagesContainer">
            <?php foreach($messages as $msg): ?>
                <div class="message <?php echo ($msg['sender_id']==$user_id) ? 'student' : 'supervisor'; ?>">
                    <?php echo htmlspecialchars($msg['message']); ?>
                    <div class="time"><?php echo date("H:i", strtotime($msg['created_at'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form id="chatForm" action="supervisor_process.php" method="post">
            <input type="hidden" name="receiver_id" value="<?php echo $supervisor_id; ?>">
            <input type="text" name="message" placeholder="Type your message..." required autocomplete="off">
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>

var container = document.getElementById("messagesContainer");
container.scrollTop = container.scrollHeight;
</script>

</body>
</html>
