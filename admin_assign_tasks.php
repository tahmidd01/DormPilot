<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Assign task
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["assign_task"])){
    $supervisor_id = $_POST["supervisor_id"];
    $student_id = $_POST["student_id"] ?: NULL;
    $title = $_POST["title"];
    $description = $_POST["description"];

    $stmt = $conn->prepare(
        "INSERT INTO tasks (supervisor_id, student_id, title, description) 
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iiss", $supervisor_id, $student_id, $title, $description);

    if($stmt->execute()){
        $message = "Task assigned successfully!";
    } else {
        $message = "Error assigning task!";
    }
    $stmt->close();
}

$supervisors = $conn->query("SELECT id, fullname FROM users WHERE role='supervisor'");

// Get all students with their supervisor assignments
$all_students = $conn->query("
    SELECT u.id, u.fullname, sa.supervisor_id 
    FROM users u 
    LEFT JOIN supervisor_assignments sa ON u.id = sa.student_id 
    WHERE u.role='student'
    ORDER BY u.fullname
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Tasks - Admin</title>
<style>
body{
    font-family: Arial;
    background: linear-gradient(to bottom right,#cfe7ff,#ffd9ec);
}
body.with-admin-sidebar{
    margin-left:260px;
}
.main-content{padding:20px;}
.container{max-width:1100px;margin:0 auto;}
.card{
    background:#fff;
    padding:25px;
    border-radius:8px;
    margin-bottom:25px;
}
h2{text-align:center;margin-bottom:20px;}
.form-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}
.form-group{margin-bottom:15px;}
label{font-weight:bold;}
input,select,textarea{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
}
textarea{height:80px;}
.btn{
    padding:10px 20px;
    background:#e24a84;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
.message{
    background:#d4edda;
    padding:10px;
    margin-bottom:15px;
}
ul{list-style:none;padding:0;}
li{
    padding:15px;
    border-bottom:1px solid #ddd;
    font-size:18px;
}
li a{
    text-decoration:none;
    color:#e24a84;
    font-weight:bold;
}
</style>
</head>

<body class="with-admin-sidebar">

<?php include "admin_sidebar.php"; ?>

<div class="main-content">
<div class="container">

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<!-- ASSIGN TASK FORM (UNCHANGED) -->
<div class="card">
    <h2>Assign Task to Supervisor</h2>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Supervisor</label>
                <select name="supervisor_id" required>
                    <option value="">Select</option>
                    <?php while($s = $supervisors->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['fullname']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Student (optional - shows assigned students when supervisor is selected)</label>
                <select name="student_id" id="student_select">
                    <option value="">None</option>
                    <?php 
                    $all_students->data_seek(0);
                    while($st = $all_students->fetch_assoc()): 
                    ?>
                        <option value="<?= $st['id'] ?>" data-supervisor="<?= $st['supervisor_id'] ?? '' ?>">
                            <?= htmlspecialchars($st['fullname']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Task Title</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"></textarea>
        </div>

        <button class="btn" name="assign_task">Assign Task</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supervisorSelect = document.querySelector('select[name="supervisor_id"]');
    const studentSelect = document.getElementById('student_select');
    const allStudentOptions = Array.from(studentSelect.options);
    
    if(supervisorSelect) {
        supervisorSelect.addEventListener('change', function() {
        const selectedSupervisorId = this.value;
        
        // Clear current options except "None"
        studentSelect.innerHTML = '<option value="">None</option>';
        
        if (selectedSupervisorId) {
            // Show only students assigned to selected supervisor
            allStudentOptions.forEach(option => {
                if (option.value && option.dataset.supervisor === selectedSupervisorId) {
                    studentSelect.appendChild(option.cloneNode(true));
                }
            });
        } else {
            // Show all students if no supervisor selected
            allStudentOptions.forEach(option => {
                if (option.value) {
                    studentSelect.appendChild(option.cloneNode(true));
                }
            });
        }
        });
    }
});
</script>

<!-- ALL TASK PART → NOW SUPERVISOR LIST -->
<div class="card">
    <h2>Supervisors</h2>
    <ul>
        <?php
        $supervisorList = $conn->query("SELECT id, fullname FROM users WHERE role='supervisor'");
        while($row = $supervisorList->fetch_assoc()):
        ?>
            <li>
                <a href="admin_view_assign_task.php?id=<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['fullname']) ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

</div>
</div>
</body>
</html>
