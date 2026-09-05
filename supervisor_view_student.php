<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: supervisor_students.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];
$student_id = intval($_GET['id']);

// Verify that this student is assigned to this supervisor
$verify = $conn->prepare("SELECT id FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ?");
$verify->bind_param("ii", $supervisor_id, $student_id);
$verify->execute();
$verify_result = $verify->get_result();

if($verify_result->num_rows == 0){
    header("Location: supervisor_students.php");
    exit();
}
$verify->close();

// Fetch student info with all details
$stmt = $conn->prepare("
    SELECT u.*, 
           p.room, 
           ra.assigned_at as room_assigned_at,
           r.room_number, r.block as room_block,
           sa.assigned_at as supervisor_assigned_at,
           a.status as admission_status,
           a.course,
           a.phone as admission_phone,
           a.father_name,
           a.mother_name
    FROM users u
    LEFT JOIN profile p ON u.id = p.user_id
    LEFT JOIN room_assignments ra ON u.id = ra.user_id
    LEFT JOIN rooms r ON ra.room_id = r.id
    LEFT JOIN supervisor_assignments sa ON u.id = sa.student_id
    LEFT JOIN admissions a ON u.id = a.user_id
    WHERE u.id = ? AND u.role='student' AND sa.supervisor_id = ?
");
$stmt->bind_param("ii", $student_id, $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: supervisor_students.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();

// Get student's complaints
$complaints = $conn->prepare("
    SELECT c.*, 
           s.fullname as supervisor_name
    FROM complaints c
    LEFT JOIN users s ON c.supervisor_id = s.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 10
");
$complaints->bind_param("i", $student_id);
$complaints->execute();
$complaints_result = $complaints->get_result();
$complaints->close();

// Get student's tasks
$tasks = $conn->prepare("
    SELECT t.*
    FROM tasks t
    WHERE t.student_id = ? AND t.supervisor_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$tasks->bind_param("ii", $student_id, $supervisor_id);
$tasks->execute();
$tasks_result = $tasks->get_result();
$tasks->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Student Details - Supervisor</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
    padding:20px;
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
    max-width:900px;
    margin:0 auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
h2 {
    text-align:center;
    margin-bottom:25px;
    color:#333;
}
.profile-pic {
    width:180px;
    height:180px;
    border-radius:50%;
    display:block;
    margin:0 auto 20px;
    object-fit:cover;
    border:4px solid #e24a84;
}
.info {
    margin-bottom:15px;
    padding:10px;
    background:#f8f9fa;
    border-radius:5px;
}
.info label {
    font-weight:bold;
    color:#555;
    display:inline-block;
    min-width:180px;
}
.info span {
    color:#333;
}
.section-title {
    margin-top:30px;
    margin-bottom:15px;
    padding-bottom:10px;
    border-bottom:2px solid #e24a84;
    color:#e24a84;
    font-size:20px;
}
.btn {
    padding:10px 20px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    text-decoration:none;
    margin:5px;
    display:inline-block;
}
.btn:hover {
    background:#ad276a;
}
.top-links {
    text-align:center;
    margin-top:30px;
}
table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
table th, table td {
    padding:10px;
    text-align:left;
    border-bottom:1px solid #ddd;
}
table th {
    background:#f8f9fa;
    font-weight:bold;
}
.status-badge {
    padding:4px 8px;
    border-radius:4px;
    font-size:11px;
    font-weight:bold;
}
.status-pending {background:#fff3cd; color:#856404;}
.status-in-progress {background:#cfe2ff; color:#084298;}
.status-resolved {background:#d1e7dd; color:#0f5132;}
.status-approved {background:#d1e7dd; color:#0f5132;}
.description-cell {
    max-width:200px;
    word-wrap:break-word;
    white-space:normal;
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
    <h2>Student Information</h2>

    <?php if(!empty($student['photo'])): ?>
        <img src="uploads/supervisors/<?php echo htmlspecialchars($student['photo']); ?>" class="profile-pic" alt="Profile Photo">
    <?php else: ?>
        <img src="uploads/students/default.png" class="profile-pic" alt="Profile Photo">
    <?php endif; ?>

    <div class="info"><label>Full Name:</label> <span><?php echo htmlspecialchars($student['fullname']); ?></span></div>
    <div class="info"><label>Email:</label> <span><?php echo htmlspecialchars($student['email']); ?></span></div>
    <div class="info"><label>Phone:</label> <span><?php echo htmlspecialchars($student['phone'] ?? $student['admission_phone'] ?? 'Not provided'); ?></span></div>
    <div class="info"><label>Gender:</label> <span><?php echo htmlspecialchars($student['gender'] ?? 'Not specified'); ?></span></div>
    
    <?php if(!empty($student['course'])): ?>
    <div class="info"><label>Course:</label> <span><?php echo htmlspecialchars($student['course']); ?></span></div>
    <?php endif; ?>
    
    <div class="info"><label>Room:</label> <span><?php echo htmlspecialchars($student['room_number'] ?? $student['room'] ?? 'Not assigned'); ?></span></div>
    <?php if(!empty($student['room_block'])): ?>
    <div class="info"><label>Block:</label> <span><?php echo htmlspecialchars($student['room_block']); ?></span></div>
    <?php endif; ?>
    <div class="info"><label>Room Assigned Date:</label> 
        <span><?php echo !empty($student['room_assigned_at']) ? date("Y-m-d", strtotime($student['room_assigned_at'])) : 'Not assigned'; ?></span>
    </div>
    <div class="info"><label>Supervisor Assigned Date:</label> 
        <span><?php echo !empty($student['supervisor_assigned_at']) ? date("Y-m-d", strtotime($student['supervisor_assigned_at'])) : 'Not assigned'; ?></span>
    </div>
    
    <?php if(!empty($student['father_name'])): ?>
    <div class="info"><label>Father's Name:</label> <span><?php echo htmlspecialchars($student['father_name']); ?></span></div>
    <?php endif; ?>
    <?php if(!empty($student['mother_name'])): ?>
    <div class="info"><label>Mother's Name:</label> <span><?php echo htmlspecialchars($student['mother_name']); ?></span></div>
    <?php endif; ?>
    
    <?php if(!empty($student['admission_status'])): ?>
    <div class="info"><label>Admission Status:</label> 
        <span><span class="status-badge status-<?php echo $student['admission_status']; ?>"><?php echo ucfirst($student['admission_status']); ?></span></span>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($student['nid_photo'])): ?>
    <div class="info"><label>NID Photo:</label> 
        <span><a href="uploads/nid/<?php echo htmlspecialchars($student['nid_photo']); ?>" target="_blank" class="btn">View NID</a></span>
    </div>
    <?php endif; ?>

    <h3 class="section-title">Recent Complaints</h3>
    <?php if($complaints_result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>Supervisor</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($comp = $complaints_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($comp['type']); ?></td>
                <td class="description-cell"><?php echo htmlspecialchars(substr($comp['description'], 0, 50)) . (strlen($comp['description']) > 50 ? '...' : ''); ?></td>
                <td><span class="status-badge status-<?php echo $comp['status']; ?>"><?php echo ucfirst($comp['status']); ?></span></td>
                <td><?php echo htmlspecialchars($comp['supervisor_name'] ?? '-'); ?></td>
                <td><?php echo date("Y-m-d", strtotime($comp['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:#666; padding:10px;">No complaints submitted yet.</p>
    <?php endif; ?>

    <h3 class="section-title">Recent Tasks</h3>
    <?php if($tasks_result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Resolution Cost</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($task = $tasks_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($task['title']); ?></td>
                <td class="description-cell"><?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 50)) . (strlen($task['description'] ?? '') > 50 ? '...' : ''); ?></td>
                <td><span class="status-badge status-<?php echo $task['status']; ?>"><?php echo ucfirst($task['status']); ?></span></td>
                <td><?php echo $task['resolution_cost'] > 0 ? '$' . number_format($task['resolution_cost'], 2) : '-'; ?></td>
                <td><?php echo date("Y-m-d", strtotime($task['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:#666; padding:10px;">No tasks assigned yet.</p>
    <?php endif; ?>

    <div class="top-links">
        <a href="supervisor_students.php" class="btn">Back to Students List</a>
    </div>
</div>
</div>

</body>
</html>

