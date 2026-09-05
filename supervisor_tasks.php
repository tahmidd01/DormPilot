<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];
$message = "";

// Create new task
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_task"])){
    $student_id = !empty($_POST["student_id"]) ? $_POST["student_id"] : NULL;
    $title = $_POST["title"];
    $description = $_POST["description"] ?? "";
    
    // Verify student is assigned to this supervisor if student_id is provided
    if($student_id) {
        $check = $conn->prepare("SELECT id FROM supervisor_assignments WHERE supervisor_id = ? AND student_id = ?");
        $check->bind_param("ii", $supervisor_id, $student_id);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows == 0) {
            $message = "Error: Student is not assigned to you!";
            $check->close();
        } else {
            $check->close();
            $stmt = $conn->prepare("INSERT INTO tasks (supervisor_id, student_id, title, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $supervisor_id, $student_id, $title, $description);
            
            if($stmt->execute()){
                $message = "Task created successfully!";
            } else {
                $message = "Error creating task!";
            }
            $stmt->close();
        }
    } else {
        // General task without specific student
        $stmt = $conn->prepare("INSERT INTO tasks (supervisor_id, student_id, title, description) VALUES (?, NULL, ?, ?)");
        $stmt->bind_param("iss", $supervisor_id, $title, $description);
        
        if($stmt->execute()){
            $message = "Task created successfully!";
        } else {
            $message = "Error creating task!";
        }
        $stmt->close();
    }
}

// Update task status
if(isset($_GET["update_status"])){
    $task_id = $_GET["task_id"];
    $status = $_GET["status"];
    
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND supervisor_id = ?");
    $stmt->bind_param("sii", $status, $task_id, $supervisor_id);
    
    if($stmt->execute()){
        $message = "Task status updated!";
    }
    $stmt->close();
}

// Resolve task with description and cost
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["resolve_task"])){
    $task_id = intval($_POST["task_id"]);
    $resolution_description = $_POST["resolution_description"] ?? "";
    $cost = floatval($_POST["cost"] ?? 0);
    
    $conn->begin_transaction();
    
    try {
        // Update task with resolution
        $stmt = $conn->prepare("UPDATE tasks SET status = 'resolved', resolution_description = ?, resolution_cost = ?, resolved_at = NOW() WHERE id = ? AND supervisor_id = ?");
        $stmt->bind_param("sdii", $resolution_description, $cost, $task_id, $supervisor_id);
        $stmt->execute();
        $stmt->close();
        
        // Add to repair_costs if cost > 0
        if($cost > 0) {
            $stmt_cost = $conn->prepare("INSERT INTO repair_costs (task_id, supervisor_id, description, cost, date) VALUES (?, ?, ?, ?, CURDATE())");
            $cost_desc = "Task Resolution: " . ($resolution_description ? substr($resolution_description, 0, 200) : "No description");
            $stmt_cost->bind_param("iisd", $task_id, $supervisor_id, $cost_desc, $cost);
            $stmt_cost->execute();
            $stmt_cost->close();
        }
        
        $conn->commit();
        $message = "Task resolved successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch assigned students for this supervisor
$assigned_students = $conn->prepare("
    SELECT u.id, u.fullname 
    FROM users u
    INNER JOIN supervisor_assignments sa ON u.id = sa.student_id
    WHERE sa.supervisor_id = ? AND u.role = 'student'
    ORDER BY u.fullname
");
$assigned_students->bind_param("i", $supervisor_id);
$assigned_students->execute();
$assigned_students = $assigned_students->get_result();

// Fetch tasks only for students assigned to this supervisor
$tasks = $conn->prepare("
    SELECT t.*, st.fullname as student_name 
    FROM tasks t 
    LEFT JOIN users st ON t.student_id = st.id 
    WHERE t.supervisor_id = ? 
    AND (t.student_id IS NULL OR t.student_id IN (
        SELECT student_id FROM supervisor_assignments WHERE supervisor_id = ?
    ))
    ORDER BY t.created_at DESC
");
$tasks->bind_param("ii", $supervisor_id, $supervisor_id);
$tasks->execute();
$tasks = $tasks->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Tasks - Supervisor</title>
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
.message {
    padding:10px;
    margin-bottom:15px;
    border-radius:5px;
    background:#d4edda;
    color:#155724;
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
.status-badge {
    padding:5px 10px;
    border-radius:3px;
    font-size:12px;
    font-weight:bold;
}
.status-pending {
    background:#fff3cd;
    color:#856404;
}
.status-in-progress {
    background:#cfe2ff;
    color:#084298;
}
.status-resolved {
    background:#d1e7dd;
    color:#0f5132;
}
.btn {
    padding:6px 12px;
    border:none;
    border-radius:3px;
    cursor:pointer;
    font-size:12px;
    text-decoration:none;
    display:inline-block;
    margin:2px;
}
.btn-primary {
    background:#007bff;
    color:white;
}
.btn-success {
    background:#e24a84;
    color:white;
}
.card {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:25px;
}
.card h2 {
    margin-bottom:20px;
    color:#333;
}
.form-group {
    margin-bottom:15px;
}
.form-group label {
    font-weight:bold;
    display:block;
    margin-bottom:5px;
    color:#555;
}
.form-group input,
.form-group select,
.form-group textarea {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
}
.form-group textarea {
    height:80px;
    resize:vertical;
}
.btn-create {
    padding:10px 20px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
}
.btn-create:hover {
    background:#ad276a;
}
.description-cell {
    max-width:300px;
    word-wrap:break-word;
    white-space:normal;
}
.modal {
    display:none;
    position:fixed;
    z-index:1000;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background-color:rgba(0,0,0,0.5);
}
.modal-content {
    background-color:#fff;
    margin:5% auto;
    padding:30px;
    border-radius:10px;
    width:90%;
    max-width:600px;
    box-shadow:0 4px 20px rgba(0,0,0,0.3);
}
.modal-content h3 {
    margin-bottom:20px;
    color:#333;
}
.modal-buttons {
    display:flex;
    gap:10px;
    margin-top:20px;
}
.btn-modal {
    padding:10px 20px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
}
.btn-modal-submit {
    background:#e24a84;
    color:white;
}
.btn-modal-cancel {
    background:#6c757d;
    color:white;
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
    <?php if($message != "") echo '<div class="message">'.$message.'</div>'; ?>
    
    <!-- Create Task Form -->
    <div class="card">
        <h2>Create New Task</h2>
        <form method="POST">
            <div class="form-group">
                <label>Student (Optional - Only shows your assigned students)</label>
                <select name="student_id">
                    <option value="">General Task (No specific student)</option>
                    <?php 
                    $assigned_students->data_seek(0);
                    while($student = $assigned_students->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $student["id"]; ?>">
                        <?php echo htmlspecialchars($student["fullname"]); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Task Title *</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            
            <button type="submit" name="create_task" class="btn-create">Create Task</button>
        </form>
    </div>
    
    <div class="list-section">
        <h2>My Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Description</th>
                    <th>Student</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $tasks->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td class="description-cell"><?php echo nl2br(htmlspecialchars($row["description"] ?? "")); ?></td>
                    <td><?php echo htmlspecialchars($row["student_name"] ?? "N/A"); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"]; ?>"><?php echo ucfirst($row["status"]); ?></span></td>
                    <td><?php echo date("Y-m-d", strtotime($row["created_at"])); ?></td>
                    <td>
                        <?php if($row["status"] == "pending"): ?>
                        <a href="?update_status=1&task_id=<?php echo $row["id"]; ?>&status=in-progress" class="btn btn-primary">In Progress</a>
                        <?php endif; ?>
                        <?php if($row["status"] != "resolved"): ?>
                        <button onclick="openResolveModal(<?php echo $row["id"]; ?>)" class="btn btn-success">Resolve</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Resolve Task Modal -->
<div id="resolveModal" class="modal">
    <div class="modal-content">
        <h3>Resolve Task</h3>
        <form method="POST" id="resolveForm">
            <input type="hidden" name="task_id" id="modal_task_id">
            <div class="form-group">
                <label>Resolution Description *</label>
                <textarea name="resolution_description" id="modal_resolution_description" required placeholder="Describe how the task was completed..."></textarea>
            </div>
            <div class="form-group">
                <label>Cost ($) - Enter 0 if no cost</label>
                <input type="number" step="0.01" min="0" name="cost" id="modal_cost" value="0" required>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="resolve_task" class="btn-modal btn-modal-submit">Resolve</button>
                <button type="button" onclick="closeResolveModal()" class="btn-modal btn-modal-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(taskId) {
    document.getElementById('modal_task_id').value = taskId;
    document.getElementById('modal_resolution_description').value = '';
    document.getElementById('modal_cost').value = '0';
    document.getElementById('resolveModal').style.display = 'block';
}

function closeResolveModal() {
    document.getElementById('resolveModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('resolveModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>

