<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];

// Fetch only students assigned to this supervisor
$stmt = $conn->prepare("
    SELECT u.id, u.fullname, u.email, u.phone, p.room, sa.assigned_at
    FROM users u 
    INNER JOIN supervisor_assignments sa ON u.id = sa.student_id
    LEFT JOIN profile p ON u.id = p.user_id 
    WHERE sa.supervisor_id = ? AND u.role='student' 
    ORDER BY u.fullname
");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Students - Supervisor</title>
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
}
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
.list-section {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.list-section h2 {
    margin-bottom:20px;
    color:#333;
}
table {
    width:100%;
    border-collapse:collapse;
}
table th, table td {
    padding:12px;
    text-align:left;
    border-bottom:1px solid #ddd;
}
table th {
    background:#f8f9fa;
    font-weight:bold;
}
.btn {
    padding:6px 12px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
}
.btn:hover {
    background:#ad276a;
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
    <div class="list-section">
        <h2>My Assigned Students</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Room</th>
                    <th>Assigned Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($students->num_rows > 0):
                    while($row = $students->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo htmlspecialchars($row["phone"] ?? "-"); ?></td>
                    <td><?php echo htmlspecialchars($row["room"] ?? "-"); ?></td>
                    <td><?php echo !empty($row["assigned_at"]) ? date("Y-m-d", strtotime($row["assigned_at"])) : "-"; ?></td>
                    <td>
                        <a href="supervisor_view_student.php?id=<?php echo $row['id']; ?>" class="btn" style="padding:6px 12px; font-size:13px; text-decoration:none;">View Details</a>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px;">No students assigned to you yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

</body>
</html>

