<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student"){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get assigned supervisor for this student
$assigned_supervisor_query = $conn->prepare("
    SELECT u.*, sa.assigned_at 
    FROM supervisor_assignments sa 
    INNER JOIN users u ON sa.supervisor_id = u.id 
    WHERE sa.student_id = ? AND u.role = 'supervisor'
    LIMIT 1
");
$assigned_supervisor_query->bind_param("i", $user_id);
$assigned_supervisor_query->execute();
$assigned_supervisor_result = $assigned_supervisor_query->get_result();
$assigned_supervisor = $assigned_supervisor_result->fetch_assoc();
$assigned_supervisor_query->close();

// Send message - only allow if supervisor is assigned and receiver is the assigned supervisor
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_message"])){
    $receiver_id = intval($_POST["receiver_id"]);
    $message = trim($_POST["message"]);
    
    // Verify that the receiver is the assigned supervisor
    if($assigned_supervisor && $receiver_id == $assigned_supervisor["id"] && !empty($message)){
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Get selected supervisor (only the assigned one)
$selected_supervisor = $assigned_supervisor ? $assigned_supervisor["id"] : 0;

// Get messages with assigned supervisor
$messages_result = null;
if($selected_supervisor > 0){
    $messages = $conn->prepare("SELECT m.*, u.fullname as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
    $messages->bind_param("iiii", $user_id, $selected_supervisor, $selected_supervisor, $user_id);
    $messages->execute();
    $messages_result = $messages->get_result();
    $messages->close();
}

$fullname = $_SESSION["fullname"];
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

.supervisor-list {
    margin-bottom:20px;
    padding:15px;
    background:#f8f9fa;
    border-radius:10px;
    border:1px solid #ddd;
}
.supervisor-list strong {
    display:block;
    margin-bottom:10px;
    color:#333;
    font-size:16px;
}
.supervisor-list a {
    display:inline-block;
    padding:10px 18px;
    margin:5px;
    background:#fff;
    border:2px solid #ddd;
    border-radius:8px;
    text-decoration:none;
    color:#333;
    font-weight:bold;
    transition:all 0.3s;
}
.supervisor-list a:hover {
    background:#e24a84;
    color:#fff;
    border-color:#e24a84;
}
.supervisor-list a.active {
    background:#e24a84;
    color:#fff;
    border-color:#e24a84;
}
.supervisor-info {
    background: linear-gradient(135deg, #e24a84 0%, #ad276a 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.supervisor-info h3 {
    margin: 0 0 15px 0;
    font-size: 22px;
    border-bottom: 2px solid rgba(255,255,255,0.3);
    padding-bottom: 10px;
}
.supervisor-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.supervisor-details div {
    background: rgba(255,255,255,0.15);
    padding: 10px;
    border-radius: 8px;
}
.supervisor-details strong {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    opacity: 0.9;
}
.supervisor-details span {
    font-size: 16px;
}
.no-supervisor {
    text-align: center;
    padding: 40px 20px;
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 10px;
    color: #856404;
}
.no-supervisor h3 {
    margin-bottom: 10px;
    color: #856404;
}
.no-supervisor p {
    font-size: 16px;
    margin: 5px 0;
}
</style>
</head>
<body>

<div class="sidebar">
    <img src="logo.png" class="logo" alt="Logo">
    <a href="student_dashboard.php"><img src="icons/dashboard.png" class="menu-icon">Dashboard</a>
    <a href="student_admission.php"><img src="icons/admission.png" class="menu-icon">Admission</a>
    <a href="student_room_change.php"><img src="icons/room.png" class="menu-icon">Room Change</a>
    <a href="student_roommates.php"><img src="icons/profile.png" class="menu-icon">Room & Roommates</a>
    <a href="student_chat.php"><img src="icons/supervisor.png" class="menu-icon">Supervisor</a>
    <a href="student_hostel.php"><img src="icons/hostel.png" class="menu-icon">View Hostel</a>
    <a href="student_complaints.php"><img src="icons/complain.png" class="menu-icon">Complain</a>
    <a href="student_announcements.php"><img src="icons/announcement.png" class="menu-icon">Announcements</a>
</div>

<div class="main-content">
    <div class="top-bar">
        <form action="logout.php" method="post">
            <button class="logout">Logout</button>
        </form>
    </div>

    <div class="chat-box">
        <h2>Chat with Supervisor</h2>

        <?php if($assigned_supervisor): ?>
            <!-- Supervisor Details Card -->
            <div class="supervisor-info">
                <h3>Your Assigned Supervisor</h3>
                <div class="supervisor-details">
                    <div>
                        <strong>Name:</strong>
                        <span><?php echo htmlspecialchars($assigned_supervisor["fullname"]); ?></span>
                    </div>
                    <div>
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($assigned_supervisor["email"]); ?></span>
                    </div>
                    <?php if(!empty($assigned_supervisor["phone"])): ?>
                    <div>
                        <strong>Phone:</strong>
                        <span><?php echo htmlspecialchars($assigned_supervisor["phone"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($assigned_supervisor["block"])): ?>
                    <div>
                        <strong>Block:</strong>
                        <span><?php echo htmlspecialchars($assigned_supervisor["block"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($assigned_supervisor["assigned_at"])): ?>
                    <div>
                        <strong>Assigned Since:</strong>
                        <span><?php echo date("Y-m-d", strtotime($assigned_supervisor["assigned_at"])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="messages-container" id="messagesContainer">
                <?php if($messages_result && $messages_result->num_rows > 0): ?>
                    <?php while($msg = $messages_result->fetch_assoc()): ?>
                        <div class="message <?php echo ($msg['sender_id']==$user_id) ? 'student' : 'supervisor'; ?>">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            <div class="time"><?php echo date("H:i", strtotime($msg['created_at'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; color:#999; padding:20px;">
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Message Form -->
            <form id="chatForm" method="POST">
                <input type="hidden" name="receiver_id" value="<?php echo $selected_supervisor; ?>">
                <input type="text" name="message" placeholder="Type your message..." required autocomplete="off">
                <button type="submit" name="send_message">Send</button>
            </form>
        <?php else: ?>
            <!-- No Supervisor Assigned -->
            <div class="no-supervisor">
                <h3>No Supervisor Assigned</h3>
                <p>You don't have an assigned supervisor yet.</p>
                <p>Please contact the administration to get assigned to a supervisor.</p>
                <p style="margin-top:15px; font-size:14px;">Once assigned, you'll be able to chat with your supervisor here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
var container = document.getElementById("messagesContainer");
if(container) {
    container.scrollTop = container.scrollHeight;
}
</script>

</body>
</html>
