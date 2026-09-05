<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: admin_assign_students.php");
    exit();
}

$id = intval($_GET['id']);

// Remove assignment if remove query exists
if(isset($_GET['remove'])){
    $user_id = intval($_GET['remove']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from room_assignments table
        $del_stmt = $conn->prepare("DELETE FROM room_assignments WHERE user_id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
        $del_stmt->close();
        
        // Also remove supervisor assignment if exists
        $del_sup = $conn->prepare("DELETE FROM supervisor_assignments WHERE student_id = ?");
        $del_sup->bind_param("i", $user_id);
        $del_sup->execute();
        $del_sup->close();
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }

    // Redirect back to main page
    header("Location: admin_assign_students.php");
    exit();
}

// Fetch student info with assignment date, assignment id, and supervisor
$stmt = $conn->prepare("
    SELECT u.*, p.room, ra.assigned_at, 
           s.fullname as supervisor_name, s.id as supervisor_id, s.email as supervisor_email,
           sa.assigned_at as supervisor_assigned_at
    FROM users u
    LEFT JOIN profile p ON u.id = p.user_id
    LEFT JOIN room_assignments ra ON u.id = ra.user_id
    LEFT JOIN supervisor_assignments sa ON u.id = sa.student_id
    LEFT JOIN users s ON sa.supervisor_id = s.id
    WHERE u.id = ? AND u.role='student'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: admin_assign_students.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Assigned Student - Admin</title>
<style>
body {font-family: Arial, sans-serif; background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec); padding:20px;}
.container {max-width:700px; margin:0 auto; background:white; padding:30px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
h2 {text-align:center; margin-bottom:25px;}
.profile-pic {width:180px; height:180px; border-radius:50%; display:block; margin:0 auto 20px; object-fit:cover;}
.info {margin-bottom:15px;}
.info label {font-weight:bold; color:#555;}
.info span {margin-left:10px; color:#333;}
.btn {padding:10px 20px; background:#e24a84; color:white; border:none; border-radius:5px; cursor:pointer; text-decoration:none; margin:5px;}
.btn:hover {background:#ad276a;}
.btn-danger {background:#dc3545;}
.btn-danger:hover {background:#c82333;}
.top-links {text-align:center; margin-top:20px;}
</style>
</head>
<body>

<div class="container">
    <h2>Student Information</h2>

    <?php if(!empty($student['photo'])): ?>
        <img src="uploads/students/<?php echo htmlspecialchars($student['photo']); ?>" class="profile-pic" alt="Profile Photo">
    <?php else: ?>
        <img src="uploads/students/default.png" class="profile-pic" alt="Profile Photo">
    <?php endif; ?>

    <div class="info"><label>Full Name:</label> <span><?php echo htmlspecialchars($student['fullname']); ?></span></div>
    <div class="info"><label>Email:</label> <span><?php echo htmlspecialchars($student['email']); ?></span></div>
    <div class="info"><label>Phone:</label> <span><?php echo htmlspecialchars($student['phone']); ?></span></div>
    <div class="info"><label>Room:</label> <span><?php echo htmlspecialchars($student['room'] ?? 'Not assigned'); ?></span></div>
    <div class="info"><label>Room Assigned Date:</label> 
        <span><?php echo !empty($student['assigned_at']) ? date("Y-m-d", strtotime($student['assigned_at'])) : 'Not assigned'; ?></span>
    </div>
    <div class="info"><label>Supervisor:</label> 
        <span><?php echo !empty($student['supervisor_name']) ? htmlspecialchars($student['supervisor_name']) : 'Not assigned'; ?></span>
    </div>
    <?php if(!empty($student['supervisor_email'])): ?>
    <div class="info"><label>Supervisor Email:</label> 
        <span><?php echo htmlspecialchars($student['supervisor_email']); ?></span>
    </div>
    <?php endif; ?>
    <div class="info"><label>Supervisor Assigned Date:</label> 
        <span><?php echo !empty($student['supervisor_assigned_at']) ? date("Y-m-d", strtotime($student['supervisor_assigned_at'])) : 'Not assigned'; ?></span>
    </div>
    <div class="info"><label>NID Photo:</label> 
        <?php if(!empty($student['nid_photo'])): ?>
            <span><a href="uploads/nid/<?php echo htmlspecialchars($student['nid_photo']); ?>" target="_blank" class="btn">View NID</a></span>
        <?php else: ?>
            <span>Not uploaded</span>
        <?php endif; ?>
    </div>

    <div class="top-links">
        <a href="admin_assign_students.php" class="btn">Back</a>
        <?php if(!empty($student['assigned_at'])): ?>
            <a href="admin_view_assign_students.php?id=<?php echo $student['id']; ?>&remove=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure to remove this assignment?')">Remove</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
