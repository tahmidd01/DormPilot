<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])) header("Location: admin_assign_tasks.php");

$supervisor_id = intval($_GET['id']);

// Supervisor info
$supervisor = $conn->query("SELECT * FROM users WHERE id=$supervisor_id")->fetch_assoc();

// Tasks + students with resolution info
$tasks = $conn->query("
SELECT t.*, u.fullname AS student_name
FROM tasks t
LEFT JOIN users u ON t.student_id = u.id
WHERE t.supervisor_id = $supervisor_id
ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Supervisor Tasks</title>
<style>
body{font-family:Arial;background:linear-gradient(to bottom right,#cfe7ff,#ffd9ec);}
.container{max-width:1000px;margin:auto;padding:20px;}
.card{background:#fff;padding:25px;border-radius:10px;}
.profile{text-align:center;margin-bottom:25px;}
.profile img{width:150px;height:150px;border-radius:50%;object-fit:cover;}
table{width:100%;border-collapse:collapse;}
td,th{padding:12px;border-bottom:1px solid #ddd;}
th{background:#f5f5f5;}
.badge{padding:5px 10px;border-radius:5px;font-size:12px;}
.pending{background:#fff3cd;}
.resolved{background:#d1e7dd;}
.btn{display:inline-block;margin-top:20px;background:#e24a84;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;}
.description-cell{max-width:400px;word-wrap:break-word;white-space:normal;}
.resolution-box{margin-top:10px;padding:12px;background:#e8f5e9;border-radius:6px;border-left:4px solid #4caf50;}
.resolution-box p{margin:5px 0;}
.resolution-title{font-weight:bold;color:#2e7d32;margin-bottom:8px;}
.cost-display{font-weight:bold;color:#4a90e2;margin-top:8px;}
.cost-zero{color:#999;font-style:italic;}
</style>
</head>
<body>

<div class="container">
<div class="card">

<div class="profile">
<img src="uploads/supervisors/<?= $supervisor['photo'] ?? 'default.png' ?>">
<h2><?= htmlspecialchars($supervisor['fullname']) ?></h2>
<p><?= htmlspecialchars($supervisor['email']) ?></p>
</div>

<h3>Assigned Tasks & Students</h3>
<table>
<tr>
<th>Task Title</th>
<th>Description</th>
<th>Student</th>
<th>Status</th>
<th>Date</th>
</tr>

<?php while($t = $tasks->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($t['title']) ?></td>
<td class="description-cell"><?= nl2br(htmlspecialchars($t['description'] ?? '')) ?></td>
<td><?= htmlspecialchars($t['student_name'] ?? 'Not Assigned') ?></td>
<td>
<span class="badge <?= $t['status']=='resolved'?'resolved':'pending' ?>">
<?= ucfirst($t['status']) ?>
</span>
<?php if($t['status'] == 'resolved' && (!empty($t['resolution_description']) || !empty($t['resolution_cost']))): ?>
<div class="resolution-box">
    <div class="resolution-title">Resolution Details:</div>
    <?php if(!empty($t['resolution_description'])): ?>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($t['resolution_description'])) ?></p>
    <?php endif; ?>
    <?php if(isset($t['resolution_cost'])): ?>
    <p class="cost-display <?= $t['resolution_cost'] == 0 ? 'cost-zero' : '' ?>">
        Cost: $<?= number_format($t['resolution_cost'], 2) ?>
    </p>
    <?php endif; ?>
</div>
<?php endif; ?>
</td>
<td><?= date("Y-m-d",strtotime($t['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
</table>

<a href="admin_assign_tasks.php" class="btn">Back</a>

</div>
</div>
</body>
</html>
