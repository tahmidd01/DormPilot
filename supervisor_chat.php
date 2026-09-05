<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];

// Send message - only allow if student is assigned to this supervisor
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_message"])){
    $receiver_id = intval($_POST["receiver_id"]);
    $message = trim($_POST["message"]);
    
    // Verify that the receiver is an assigned student
    $check = $conn->prepare("SELECT id FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ?");
    $check->bind_param("ii", $supervisor_id, $receiver_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if($check_result->num_rows > 0 && !empty($message)){
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $supervisor_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();
}

// Get only assigned students who have messaged this supervisor or vice versa
$students_query = "SELECT DISTINCT u.id, u.fullname, u.email, 
    (SELECT COUNT(*) FROM messages m WHERE m.sender_id = u.id AND m.receiver_id = $supervisor_id AND m.created_at > 
        (SELECT COALESCE(MAX(created_at), '1970-01-01') FROM messages WHERE (sender_id = $supervisor_id AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = $supervisor_id))
    ) as unread_count
    FROM users u 
    INNER JOIN supervisor_assignments sa ON u.id = sa.student_id
    WHERE u.role = 'student' 
    AND sa.supervisor_id = $supervisor_id
    AND (u.id IN (SELECT sender_id FROM messages WHERE receiver_id = $supervisor_id) 
         OR u.id IN (SELECT receiver_id FROM messages WHERE sender_id = $supervisor_id))
    ORDER BY u.fullname";

$students = $conn->query($students_query);

// Get all assigned students if none have messaged yet
if($students->num_rows == 0){
    $students = $conn->query("
        SELECT u.id, u.fullname, u.email, 0 as unread_count 
        FROM users u 
        INNER JOIN supervisor_assignments sa ON u.id = sa.student_id
        WHERE u.role = 'student' AND sa.supervisor_id = $supervisor_id
        ORDER BY u.fullname
    ");
}

// Get selected student or first one
$selected_student = $_GET["student"] ?? ($students->num_rows > 0 ? $students->fetch_assoc()["id"] : 0);
$students->data_seek(0);

// Get messages with selected student (only if student is assigned)
$messages_result = null;
if($selected_student > 0){
    // Verify student is assigned
    $verify = $conn->prepare("SELECT id FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ?");
    $verify->bind_param("ii", $supervisor_id, $selected_student);
    $verify->execute();
    $verify_result = $verify->get_result();
    
    if($verify_result->num_rows > 0){
        $messages = $conn->prepare("SELECT m.*, u.fullname as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
        $messages->bind_param("iiii", $supervisor_id, $selected_student, $selected_student, $supervisor_id);
        $messages->execute();
        $messages_result = $messages->get_result();
        $messages->close();
    }
    $verify->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat with Students - Supervisor</title>
<style>
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
    max-width:1200px;
    margin:0 auto;
    padding:0 20px;
}
.chat-section {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
    display:grid;
    grid-template-columns:300px 1fr;
    gap:20px;
    min-height:600px;
}
.student-list {
    border-right:1px solid #ddd;
    padding-right:20px;
    overflow-y:auto;
    max-height:600px;
}
.student-list h2 {
    margin-bottom:15px;
    color:#333;
    font-size:18px;
}
.student-item {
    padding:12px;
    margin-bottom:8px;
    background:#f8f9fa;
    border-radius:5px;
    text-decoration:none;
    color:#333;
    display:block;
    border:2px solid transparent;
    position:relative;
}
.student-item:hover {
    background:#e8f5e9;
    border-color:#28a745;
}
.student-item.active {
    background:#e24a84;
    color:white;
}
.student-item strong {
    display:block;
    margin-bottom:3px;
}
.student-item span {
    font-size:12px;
    color:#666;
}
.student-item.active span {
    color:rgba(255,255,255,0.9);
}
.unread-badge {
    position:absolute;
    top:8px;
    right:8px;
    background:#dc3545;
    color:white;
    border-radius:50%;
    width:20px;
    height:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:11px;
    font-weight:bold;
}
.chat-area {
    display:flex;
    flex-direction:column;
}
.chat-header {
    padding-bottom:15px;
    border-bottom:1px solid #ddd;
    margin-bottom:15px;
}
.chat-header h2 {
    color:#333;
    font-size:18px;
}
.messages-container {
    flex:1;
    overflow-y:auto;
    border:1px solid #ddd;
    border-radius:5px;
    padding:15px;
    margin-bottom:15px;
    background:#f9f9f9;
    min-height:400px;
    max-height:450px;
}
.message {
    margin-bottom:15px;
    padding:10px;
    border-radius:5px;
}
.message.sent {
    background:#e24a84;
    color:white;
    margin-left:20%;
}
.message.received {
    background:#e9ecef;
    color:#333;
    margin-right:20%;
}
.message .sender {
    font-weight:bold;
    font-size:12px;
    margin-bottom:5px;
    opacity:0.9;
}
.message .text {
    word-wrap:break-word;
}
.message .time {
    font-size:11px;
    margin-top:5px;
    opacity:0.7;
}
.message-form {
    display:flex;
    gap:10px;
}
.message-form textarea {
    flex:1;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
    font-family:Arial;
    resize:vertical;
    min-height:60px;
}
.btn {
    padding:10px 20px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
    height:fit-content;
}
.btn:hover {
    background:#ad276a;
}
.no-student {
    display:flex;
    align-items:center;
    justify-content:center;
    height:100%;
    color:#666;
    text-align:center;
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
    <button class="btn" onclick="location.reload();" style="margin-left:10px;">Refresh</button>
</div>

<div class="container">
    <div class="chat-section">
        <div class="student-list">
            <h2>Students</h2>
            <?php if($students->num_rows > 0): ?>
                <?php while($student = $students->fetch_assoc()): ?>
                <a href="?student=<?php echo $student["id"]; ?>" class="student-item <?php echo $selected_student == $student["id"] ? 'active' : ''; ?>">
                    <strong><?php echo htmlspecialchars($student["fullname"]); ?></strong>
                    <span><?php echo htmlspecialchars($student["email"]); ?></span>
                    <?php if(isset($student["unread_count"]) && $student["unread_count"] > 0): ?>
                    <span class="unread-badge"><?php echo $student["unread_count"]; ?></span>
                    <?php endif; ?>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#666; padding:10px;">No students found.</p>
            <?php endif; ?>
        </div>

        <div class="chat-area">
            <?php if($selected_student > 0): ?>
                <?php 
                $student_info = $conn->query("SELECT fullname, email FROM users WHERE id = $selected_student")->fetch_assoc();
                ?>
                <div class="chat-header">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h2>Chat with <?php echo htmlspecialchars($student_info["fullname"]); ?></h2>
                        <button type="button" onclick="location.reload()" style="padding:8px 15px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer; font-size:13px;">Refresh</button>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <?php if($messages_result && $messages_result->num_rows > 0): ?>
                        <?php while($msg = $messages_result->fetch_assoc()): ?>
                        <div class="message <?php echo $msg["sender_id"] == $supervisor_id ? 'sent' : 'received'; ?>">
                            <div class="sender"><?php echo htmlspecialchars($msg["sender_name"]); ?></div>
                            <div class="text"><?php echo nl2br(htmlspecialchars($msg["message"])); ?></div>
                            <div class="time"><?php echo date("Y-m-d H:i", strtotime($msg["created_at"])); ?></div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; color:#999; padding:20px;">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" class="message-form" id="messageForm">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_student; ?>">
                    <textarea name="message" placeholder="Type your message..." required id="messageInput"></textarea>
                    <button type="submit" name="send_message" class="btn">Send</button>
                </form>
            <?php else: ?>
                <div class="no-student">
                    <div>
                        <p>Select a student from the list to start chatting</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
// Auto scroll to bottom
var container = document.getElementById('messagesContainer');
if(container){
    container.scrollTop = container.scrollHeight;
}
</script>

</body>
</html>

