<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Add repair cost
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_cost"])){
    $complaint_id = $_POST["complaint_id"] ?: NULL;
    $description = $_POST["description"];
    $cost = $_POST["cost"];
    $date = $_POST["date"];
    
    $stmt = $conn->prepare("INSERT INTO repair_costs (complaint_id, description, cost, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $complaint_id, $description, $cost, $date);
    
    if($stmt->execute()){
        $message = "Repair cost added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

$total_cost = $conn->query("SELECT SUM(cost) as total FROM repair_costs")->fetch_assoc()["total"] ?? 0;

// Get all repair costs from both complaints and tasks with detailed information
$repair_costs = $conn->query("
    SELECT 
        rc.id,
        rc.date,
        rc.description,
        rc.cost,
        rc.complaint_id,
        rc.task_id,
        rc.supervisor_id,
        c.type as complaint_type,
        c.resolution_description as complaint_resolution,
        c.resolution_cost as complaint_resolution_cost,
        t.title as task_title,
        t.resolution_description as task_resolution,
        t.resolution_cost as task_resolution_cost,
        s.fullname as supervisor_name,
        CASE 
            WHEN rc.complaint_id IS NOT NULL THEN 'Complaint'
            WHEN rc.task_id IS NOT NULL THEN 'Task'
            ELSE 'Manual Entry'
        END as source_type
    FROM repair_costs rc
    LEFT JOIN complaints c ON rc.complaint_id = c.id
    LEFT JOIN tasks t ON rc.task_id = t.id
    LEFT JOIN users s ON rc.supervisor_id = s.id
    ORDER BY rc.date DESC, rc.created_at DESC
");

// Also get costs directly from resolved complaints and tasks (even if cost is 0)
$resolved_complaints = $conn->query("
    SELECT 
        c.id,
        c.resolved_at as date,
        CONCAT('Complaint Resolution: ', c.type) as description,
        COALESCE(c.resolution_cost, 0) as cost,
        c.id as complaint_id,
        NULL as task_id,
        c.supervisor_id,
        c.type as complaint_type,
        c.resolution_description as complaint_resolution,
        c.resolution_cost as complaint_resolution_cost,
        NULL as task_title,
        NULL as task_resolution,
        NULL as task_resolution_cost,
        s.fullname as supervisor_name,
        'Complaint' as source_type
    FROM complaints c
    LEFT JOIN users s ON c.supervisor_id = s.id
    WHERE c.status = 'resolved' AND c.resolved_at IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM repair_costs rc WHERE rc.complaint_id = c.id
    )
");

$resolved_tasks = $conn->query("
    SELECT 
        t.id,
        t.resolved_at as date,
        CONCAT('Task Resolution: ', t.title) as description,
        COALESCE(t.resolution_cost, 0) as cost,
        NULL as complaint_id,
        t.id as task_id,
        t.supervisor_id,
        NULL as complaint_type,
        NULL as complaint_resolution,
        NULL as complaint_resolution_cost,
        t.title as task_title,
        t.resolution_description as task_resolution,
        t.resolution_cost as task_resolution_cost,
        s.fullname as supervisor_name,
        'Task' as source_type
    FROM tasks t
    LEFT JOIN users s ON t.supervisor_id = s.id
    WHERE t.status = 'resolved' AND t.resolved_at IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM repair_costs rc WHERE rc.task_id = t.id
    )
");

$complaints = $conn->query("SELECT id, type, description FROM complaints");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Repair Costs - Admin</title>
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
body.with-admin-sidebar {
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
.form-section, .list-section {
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.form-section h2, .list-section h2 {
    margin-bottom:20px;
    color:#333;
}
.total-box {
    background:#e8f4f8;
    padding:20px;
    border-radius:5px;
    margin-bottom:20px;
    text-align:center;
}
.total-box .amount {
    font-size:32px;
    font-weight:bold;
    color:#4a90e2;
}
.form-row {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
    margin-bottom:15px;
}
.form-group {
    margin-bottom:15px;
}
.form-group label {
    display:block;
    margin-bottom:5px;
    color:#555;
    font-weight:bold;
}
.form-group input, .form-group select, .form-group textarea {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
    font-family:Arial;
}
.btn {
    padding:10px 20px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
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
.description-cell {
    max-width:400px;
    word-wrap:break-word;
    white-space:normal;
}
.resolution-cell {
    max-width:300px;
    word-wrap:break-word;
    white-space:normal;
    font-style:italic;
    color:#555;
}
.source-badge {
    padding:4px 8px;
    border-radius:4px;
    font-size:11px;
    font-weight:bold;
}
.source-complaint {
    background:#fff3cd;
    color:#856404;
}
.source-task {
    background:#cfe2ff;
    color:#084298;
}
.source-manual {
    background:#d1e7dd;
    color:#0f5132;
}
.cost-zero {
    color:#999;
    font-style:italic;
}
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
    
    <div class="list-section">
        <div class="total-box">
            <div style="color:#666; margin-bottom:5px;">Total Repair Costs</div>
            <div class="amount">$<?php echo number_format($total_cost, 2); ?></div>
        </div>
    </div>

    <div class="form-section">
        <h2>Add Repair Cost</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Related Complaint (Optional)</label>
                    <select name="complaint_id">
                        <option value="">None</option>
                        <?php 
                        $complaints->data_seek(0);
                        while($row = $complaints->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $row["id"]; ?>">
                            #<?php echo $row["id"]; ?> - <?php echo htmlspecialchars($row["type"]); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" required>
            </div>
            <div class="form-group">
                <label>Cost ($)</label>
                <input type="number" step="0.01" name="cost" required>
            </div>
            <button type="submit" name="add_cost" class="btn">Add Cost</button>
        </form>
    </div>

    <div class="list-section">
        <h2>Repair Cost History - All Sources</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Description</th>
                    <th>Supervisor</th>
                    <th>Resolution Details</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Display repair_costs entries
                $repair_costs->data_seek(0);
                while($row = $repair_costs->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo date("Y-m-d", strtotime($row["date"])); ?></td>
                    <td>
                        <span class="source-badge source-<?php echo strtolower(str_replace(' ', '-', $row["source_type"])); ?>">
                            <?php echo htmlspecialchars($row["source_type"]); ?>
                        </span>
                    </td>
                    <td class="description-cell">
                        <?php if($row["complaint_id"]): ?>
                            Complaint #<?php echo $row["complaint_id"]; ?>: <?php echo htmlspecialchars($row["complaint_type"] ?? ""); ?>
                        <?php elseif($row["task_id"]): ?>
                            Task: <?php echo htmlspecialchars($row["task_title"] ?? ""); ?>
                        <?php else: ?>
                            <?php echo htmlspecialchars($row["description"]); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "-"); ?></td>
                    <td class="resolution-cell">
                        <?php 
                        if($row["complaint_resolution"]): 
                            echo nl2br(htmlspecialchars($row["complaint_resolution"])); 
                        elseif($row["task_resolution"]): 
                            echo nl2br(htmlspecialchars($row["task_resolution"])); 
                        else: 
                            echo "-"; 
                        endif; 
                        ?>
                    </td>
                    <td class="<?php echo $row["cost"] == 0 ? 'cost-zero' : ''; ?>">
                        $<?php echo number_format($row["cost"], 2); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php 
                // Display resolved complaints that don't have repair_costs entries
                $resolved_complaints->data_seek(0);
                while($row = $resolved_complaints->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo date("Y-m-d", strtotime($row["date"])); ?></td>
                    <td>
                        <span class="source-badge source-complaint">Complaint</span>
                    </td>
                    <td class="description-cell">
                        Complaint #<?php echo $row["complaint_id"]; ?>: <?php echo htmlspecialchars($row["complaint_type"]); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "-"); ?></td>
                    <td class="resolution-cell">
                        <?php echo $row["complaint_resolution"] ? nl2br(htmlspecialchars($row["complaint_resolution"])) : "-"; ?>
                    </td>
                    <td class="<?php echo $row["cost"] == 0 ? 'cost-zero' : ''; ?>">
                        $<?php echo number_format($row["cost"], 2); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php 
                // Display resolved tasks that don't have repair_costs entries
                $resolved_tasks->data_seek(0);
                while($row = $resolved_tasks->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo date("Y-m-d", strtotime($row["date"])); ?></td>
                    <td>
                        <span class="source-badge source-task">Task</span>
                    </td>
                    <td class="description-cell">
                        Task: <?php echo htmlspecialchars($row["task_title"]); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "-"); ?></td>
                    <td class="resolution-cell">
                        <?php echo $row["task_resolution"] ? nl2br(htmlspecialchars($row["task_resolution"])) : "-"; ?>
                    </td>
                    <td class="<?php echo $row["cost"] == 0 ? 'cost-zero' : ''; ?>">
                        $<?php echo number_format($row["cost"], 2); ?>
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

