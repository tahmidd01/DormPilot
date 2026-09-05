<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "supervisor"){
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];
$message = "";

// Update complaint status
if(isset($_GET["update_status"])){
    $complaint_id = $_GET["complaint_id"];
    $status = $_GET["status"];
    
    $stmt = $conn->prepare("UPDATE complaints SET status = ?, supervisor_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $supervisor_id, $complaint_id);
    
    if($stmt->execute()){
        $message = "Complaint status updated!";
    }
    $stmt->close();
}

// Resolve complaint with description and cost
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["resolve_complaint"])){
    $complaint_id = intval($_POST["complaint_id"]);
    $resolution_description = $_POST["resolution_description"] ?? "";
    $cost = floatval($_POST["cost"] ?? 0);
    
    $conn->begin_transaction();
    
    try {
        // Update complaint with resolution
        $stmt = $conn->prepare("UPDATE complaints SET status = 'resolved', supervisor_id = ?, resolution_description = ?, resolution_cost = ?, resolved_at = NOW() WHERE id = ?");
        $stmt->bind_param("isdi", $supervisor_id, $resolution_description, $cost, $complaint_id);
        $stmt->execute();
        $stmt->close();
        
        // Add to repair_costs if cost > 0
        if($cost > 0) {
            $stmt_cost = $conn->prepare("INSERT INTO repair_costs (complaint_id, supervisor_id, description, cost, date) VALUES (?, ?, ?, ?, CURDATE())");
            $cost_desc = "Complaint Resolution: " . ($resolution_description ? substr($resolution_description, 0, 200) : "No description");
            $stmt_cost->bind_param("iisd", $complaint_id, $supervisor_id, $cost_desc, $cost);
            $stmt_cost->execute();
            $stmt_cost->close();
        }
        
        $conn->commit();
        $message = "Complaint resolved successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

$complaints = $conn->query("SELECT c.*, u.fullname, u.email FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Handle Complaints - Supervisor</title>
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
.complaint-photo {
    max-width:100px;
    max-height:100px;
    border-radius:5px;
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
.form-group {
    margin-bottom:15px;
}
.form-group label {
    display:block;
    margin-bottom:5px;
    font-weight:bold;
    color:#555;
}
.form-group textarea,
.form-group input {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
    font-family:Arial;
}
.form-group textarea {
    min-height:100px;
    resize:vertical;
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
    
    <div class="list-section">
        <h2>All Complaints</h2>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $complaints->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                    <td><?php echo htmlspecialchars($row["type"]); ?></td>
                    <td class="description-cell"><?php echo nl2br(htmlspecialchars($row["description"])); ?></td>
                    <td>
                        <?php if($row["photo"]): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row["photo"]); ?>" alt="Photo" class="complaint-photo">
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?php echo $row["status"]; ?>"><?php echo ucfirst($row["status"]); ?></span></td>
                    <td><?php echo date("Y-m-d", strtotime($row["created_at"])); ?></td>
                    <td>
                        <?php if($row["status"] == "pending"): ?>
                        <a href="?update_status=1&complaint_id=<?php echo $row["id"]; ?>&status=in-progress" class="btn btn-primary">In Progress</a>
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

<!-- Resolve Complaint Modal -->
<div id="resolveModal" class="modal">
    <div class="modal-content">
        <h3>Resolve Complaint</h3>
        <form method="POST" id="resolveForm">
            <input type="hidden" name="complaint_id" id="modal_complaint_id">
            <div class="form-group">
                <label>Resolution Description *</label>
                <textarea name="resolution_description" id="modal_resolution_description" required placeholder="Describe how the complaint was resolved..."></textarea>
            </div>
            <div class="form-group">
                <label>Cost ($) - Enter 0 if no cost</label>
                <input type="number" step="0.01" min="0" name="cost" id="modal_cost" value="0" required>
            </div>
            <div class="modal-buttons">
                <button type="submit" name="resolve_complaint" class="btn-modal btn-modal-submit">Resolve</button>
                <button type="button" onclick="closeResolveModal()" class="btn-modal btn-modal-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(complaintId) {
    document.getElementById('modal_complaint_id').value = complaintId;
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

