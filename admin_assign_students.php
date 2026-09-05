<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Assign student to room and supervisor
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["assign"])){
    $user_id = $_POST["user_id"];
    $room_id = $_POST["room_id"];
    $supervisor_id = !empty($_POST["supervisor_id"]) ? $_POST["supervisor_id"] : null;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Assign/Update room
        $check = $conn->prepare("SELECT id FROM room_assignments WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows > 0){
            // Update existing assignment
            $stmt = $conn->prepare("UPDATE room_assignments SET room_id = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $room_id, $user_id);
        } else {
            // Create new assignment
            $stmt = $conn->prepare("INSERT INTO room_assignments (user_id, room_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $room_id);
        }
        $stmt->execute();
        $stmt->close();
        
        // Assign/Update/Remove supervisor
        $check_sup = $conn->prepare("SELECT id FROM supervisor_assignments WHERE student_id = ?");
        $check_sup->bind_param("i", $user_id);
        $check_sup->execute();
        $result_sup = $check_sup->get_result();
        
        if($supervisor_id) {
            // Assign or update supervisor
            if($result_sup->num_rows > 0){
                // Update existing supervisor assignment
                $stmt_sup = $conn->prepare("UPDATE supervisor_assignments SET supervisor_id = ? WHERE student_id = ?");
                $stmt_sup->bind_param("ii", $supervisor_id, $user_id);
            } else {
                // Create new supervisor assignment
                $stmt_sup = $conn->prepare("INSERT INTO supervisor_assignments (supervisor_id, student_id) VALUES (?, ?)");
                $stmt_sup->bind_param("ii", $supervisor_id, $user_id);
            }
            $stmt_sup->execute();
            $stmt_sup->close();
        } else {
            // Remove supervisor assignment if exists
            if($result_sup->num_rows > 0){
                $stmt_sup = $conn->prepare("DELETE FROM supervisor_assignments WHERE student_id = ?");
                $stmt_sup->bind_param("i", $user_id);
                $stmt_sup->execute();
                $stmt_sup->close();
            }
        }
        $check_sup->close();
        
        $conn->commit();
        $message = "Student assigned to room and supervisor successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch data
$students = $conn->query("SELECT u.id, u.fullname FROM users u WHERE u.role='student'");
$rooms = $conn->query("SELECT r.*, COUNT(ra.user_id) as current_occupancy FROM rooms r LEFT JOIN room_assignments ra ON r.id = ra.room_id GROUP BY r.id HAVING current_occupancy < r.capacity");
$supervisors = $conn->query("SELECT u.id, u.fullname FROM users u WHERE u.role='supervisor'");
$assignments = $conn->query("SELECT ra.id as assign_id, u.id as user_id, u.fullname, 
    r.room_number, r.block, 
    s.fullname as supervisor_name, s.id as supervisor_id
    FROM room_assignments ra 
    JOIN users u ON ra.user_id = u.id 
    JOIN rooms r ON ra.room_id = r.id
    LEFT JOIN supervisor_assignments sa ON u.id = sa.student_id
    LEFT JOIN users s ON sa.supervisor_id = s.id
    ORDER BY u.fullname");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Students - Admin</title>
<style>
body {margin:0; font-family: Arial; background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);}
body.with-admin-sidebar {margin-left:260px;}
.main-content {padding:20px;}
.top-bar {display:flex; justify-content:flex-end; margin-bottom:20px;}
.top-bar .logout {padding:10px 18px; font-size:18px; border-radius:5px; display:flex; align-items:center; background:#e24a84; color:#fff; border:none; cursor:pointer;}
.top-bar .logout img {width:20px; height:20px; margin-right:8px;}
.container {max-width:1200px; margin:0 auto;}
.message {padding:12px; margin-bottom:20px; border-radius:5px; background:#d4edda; color:#155724; text-align:center;}
.card {background:white; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.15); padding:30px; margin-bottom:25px;}
.card h2 {margin-bottom:25px; color:#333; text-align:center;}
.form-row {display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;}
.form-group {display:flex; flex-direction:column;}
.form-group label {margin-bottom:8px; font-weight:bold; color:#555;}
.form-group select {padding:12px; font-size:15px; border:1px solid #ddd; border-radius:6px;}
.btn {padding:12px 25px; background:#e24a84; color:white; border:none; border-radius:6px; cursor:pointer; font-size:15px;}
.btn:hover {background:#ad276a;}
table {width:100%; border-collapse:collapse; margin-top:15px;}
table th, table td {padding:14px; text-align:left; border-bottom:1px solid #ddd; font-size:14px;}
table th {background:#f8f9fa;}
</style>
</head>
<body class="with-admin-sidebar">

<?php include "admin_sidebar.php"; ?>

<div class="main-content">
<div class="top-bar">
    <form action="logout.php" method="post" style="margin:0;">
        <button type="submit" class="logout">
            <img src="icons/logout.png" alt="Logout Icon"> Logout
        </button>
    </form>
</div>

<div class="container">
<?php if($message != "") echo '<div class="message">'.$message.'</div>'; ?>

<div class="card">
    <h2>Assign Student to Room and Supervisor</h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Select Student</label>
                <select name="user_id" required>
                    <option value="">Choose student...</option>
                    <?php 
                    $students->data_seek(0);
                    while($row = $students->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row["id"]; ?>">
                        <?php echo htmlspecialchars($row["fullname"]); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select Room (Available)</label>
                <select name="room_id" required>
                    <option value="">Choose room...</option>
                    <?php 
                    $rooms->data_seek(0);
                    while($row = $rooms->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row["id"]; ?>">
                        <?php echo htmlspecialchars($row["room_number"]); ?> (<?php echo htmlspecialchars($row["block"] ?? "-"); ?>) - <?php echo $row["current_occupancy"]; ?>/<?php echo $row["capacity"]; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Select Supervisor (Optional)</label>
                <select name="supervisor_id">
                    <option value="">No supervisor assigned</option>
                    <?php 
                    $supervisors->data_seek(0);
                    while($row = $supervisors->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row["id"]; ?>">
                        <?php echo htmlspecialchars($row["fullname"]); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="assign" class="btn">Assign</button>
    </form>
</div>

<div class="card">
    <h2>Current Assignments</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Room</th>
                <th>Supervisor</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $assignments->data_seek(0);
            while($row = $assignments->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                <td><?php echo htmlspecialchars($row["room_number"] . " (" . ($row["block"] ?? "-") . ")"); ?></td>
                <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "Not assigned"); ?></td>
                <td>
                    <a href="admin_view_assign_students.php?id=<?php echo $row['user_id']; ?>" class="btn">View</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div>
</div>
</body>
</html>
