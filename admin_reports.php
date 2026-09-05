<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// Room availability report
$room_report = $conn->query("SELECT r.*, COUNT(ra.user_id) as occupied FROM rooms r LEFT JOIN room_assignments ra ON r.id = ra.room_id GROUP BY r.id");

// Occupancy report
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()["count"];
$occupied_count = $conn->query("SELECT COUNT(DISTINCT room_id) as count FROM room_assignments")->fetch_assoc()["count"];
$vacant_count = $total_rooms - $occupied_count;
$occupancy_rate = $total_rooms > 0 ? round(($occupied_count / $total_rooms) * 100, 2) : 0;

// Complaints report
$complaints_by_type = $conn->query("SELECT type, COUNT(*) as count FROM complaints GROUP BY type");
$complaints_by_status = $conn->query("SELECT status, COUNT(*) as count FROM complaints GROUP BY status");
$recent_complaints = $conn->query("
    SELECT c.*, u.fullname, s.fullname as supervisor_name 
    FROM complaints c 
    JOIN users u ON c.user_id = u.id 
    LEFT JOIN users s ON c.supervisor_id = s.id
    ORDER BY c.created_at DESC LIMIT 10
");

// Tasks report
$tasks_by_status = $conn->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
$tasks_by_supervisor = $conn->query("
    SELECT u.fullname, COUNT(t.id) as task_count 
    FROM users u 
    LEFT JOIN tasks t ON u.id = t.supervisor_id 
    WHERE u.role = 'supervisor' 
    GROUP BY u.id, u.fullname 
    ORDER BY task_count DESC
");
$recent_tasks = $conn->query("
    SELECT t.*, u.fullname as student_name, s.fullname as supervisor_name 
    FROM tasks t 
    LEFT JOIN users u ON t.student_id = u.id 
    LEFT JOIN users s ON t.supervisor_id = s.id
    ORDER BY t.created_at DESC LIMIT 10
");

// Supervisor-Student assignments report
$supervisor_assignments = $conn->query("
    SELECT s.fullname as supervisor_name, COUNT(sa.student_id) as student_count 
    FROM users s 
    LEFT JOIN supervisor_assignments sa ON s.id = sa.supervisor_id 
    WHERE s.role = 'supervisor' 
    GROUP BY s.id, s.fullname 
    ORDER BY student_count DESC
");
$total_assigned_students = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM supervisor_assignments")->fetch_assoc()["count"];
$total_unassigned_students = $conn->query("
    SELECT COUNT(*) as count 
    FROM users u 
    WHERE u.role = 'student' 
    AND u.id NOT IN (SELECT student_id FROM supervisor_assignments)
")->fetch_assoc()["count"];

// Repair costs summary
$total_repair_costs = $conn->query("SELECT SUM(cost) as total FROM repair_costs")->fetch_assoc()["total"] ?? 0;
$repair_costs_by_source = $conn->query("
    SELECT 
        CASE 
            WHEN complaint_id IS NOT NULL THEN 'Complaint'
            WHEN task_id IS NOT NULL THEN 'Task'
            ELSE 'Manual Entry'
        END as source_type,
        SUM(cost) as total_cost,
        COUNT(*) as count
    FROM repair_costs 
    GROUP BY source_type
");
$recent_repair_costs = $conn->query("
    SELECT rc.*, 
        c.type as complaint_type,
        t.title as task_title,
        s.fullname as supervisor_name
    FROM repair_costs rc
    LEFT JOIN complaints c ON rc.complaint_id = c.id
    LEFT JOIN tasks t ON rc.task_id = t.id
    LEFT JOIN users s ON rc.supervisor_id = s.id
    ORDER BY rc.created_at DESC LIMIT 10
");

// Room change requests report
$room_change_by_status = $conn->query("SELECT status, COUNT(*) as count FROM room_change_requests GROUP BY status");
$recent_room_changes = $conn->query("
    SELECT r.*, u.fullname, u.email 
    FROM room_change_requests r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.request_date DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - Admin</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
body {
    background: linear-gradient(to bottom right, #cfe7ff, #ffd9ec);
}
body.with-admin-sidebar { margin-left:260px; }
.main-content { flex:1; padding:20px; }
.top-bar {
    display:flex; justify-content:flex-end; align-items:center; margin-bottom:20px;
}
.top-bar .logout {
    padding:10px 18px; font-size:18px; border-radius:5px; 
    background:#e24a84; color:#fff; border:none; cursor:pointer;
    display:flex; align-items:center;
}
.top-bar .logout img { width:20px; height:20px; margin-right:8px; }
.container { max-width:1200px; margin:0 auto; padding:0 20px; }
.report-section {
    background:white; padding:25px; border-radius:8px; 
    box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px;
}
.report-section h2 { margin-bottom:20px; color:#333; }
.stats-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); 
    gap:15px; margin-bottom:20px;
}
.stat-box { padding:15px; background:#f8f9fa; border-radius:5px; text-align:center; }
.stat-box .number { font-size:28px; font-weight:bold; color:#4a90e2; }
.stat-box .label { color:#666; font-size:14px; margin-top:5px; }
table { width:100%; border-collapse:collapse; }
table th, table td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
table th { background:#f8f9fa; font-weight:bold; }
.status-badge { padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold; }
.status-pending {background:#fff3cd; color:#856404;}
.status-in-progress {background:#cfe2ff; color:#084298;}
.status-resolved {background:#d1e7dd; color:#0f5132;}
.cost-amount { font-weight:bold; color:#28a745; }
.description-cell { max-width:200px; word-wrap:break-word; white-space:normal; }
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

    <!-- Room Availability Report -->
    <div class="report-section">
        <h2>Room Availability Report</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number"><?php echo $total_rooms; ?></div><div class="label">Total Rooms</div></div>
            <div class="stat-box"><div class="number"><?php echo $occupied_count; ?></div><div class="label">Occupied</div></div>
            <div class="stat-box"><div class="number"><?php echo $vacant_count; ?></div><div class="label">Vacant</div></div>
            <div class="stat-box"><div class="number"><?php echo $occupancy_rate; ?>%</div><div class="label">Occupancy Rate</div></div>
        </div>
        <table>
            <thead><tr>
                <th>Room Number</th><th>Block</th><th>Capacity</th><th>Occupied</th><th>Available</th><th>Status</th>
            </tr></thead>
            <tbody>
                <?php while($row = $room_report->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["room_number"] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row["block"] ?? "—"); ?></td>
                    <td><?php echo $row["capacity"] ?? 0; ?></td>
                    <td><?php echo $row["occupied"] ?? 0; ?></td>
                    <td><?php echo ($row["capacity"] ?? 0) - ($row["occupied"] ?? 0); ?></td>
                    <td><?php echo ($row["occupied"] ?? 0) >= ($row["capacity"] ?? 0) ? "Full" : "Available"; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Complaints Report -->
    <div class="report-section">
        <h2>Complaints Report</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()["count"]; ?></div><div class="label">Total Complaints</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='resolved'")->fetch_assoc()["count"]; ?></div><div class="label">Resolved</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='pending'")->fetch_assoc()["count"]; ?></div><div class="label">Pending</div></div>
            <div class="stat-box"><div class="number cost-amount">$<?php echo number_format($conn->query("SELECT SUM(resolution_cost) as total FROM complaints WHERE resolution_cost > 0")->fetch_assoc()["total"] ?? 0, 2); ?></div><div class="label">Total Resolution Costs</div></div>
        </div>

        <h3>By Type</h3>
        <table><thead><tr><th>Type</th><th>Count</th></tr></thead><tbody>
        <?php while($row = $complaints_by_type->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row["type"] ?? '—'); ?></td><td><?php echo $row["count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>By Status</h3>
        <table><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>
        <?php while($row = $complaints_by_status->fetch_assoc()): ?>
            <tr><td><?php echo ucfirst($row["status"] ?? '—'); ?></td><td><?php echo $row["count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>Recent Complaints</h3>
        <table>
            <thead><tr><th>Student</th><th>Type</th><th>Description</th><th>Supervisor</th><th>Status</th><th>Resolution Cost</th><th>Date</th></tr></thead>
            <tbody>
            <?php while($row = $recent_complaints->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["fullname"] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row["type"] ?? '—'); ?></td>
                    <td class="description-cell"><?php echo htmlspecialchars(substr($row["description"] ?? '', 0, 50)) . (strlen($row["description"] ?? '') > 50 ? '...' : ''); ?></td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? '—'); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"] ?? 'pending'; ?>"><?php echo ucfirst($row["status"] ?? 'pending'); ?></span></td>
                    <td><?php echo ($row["resolution_cost"] ?? 0) > 0 ? '$' . number_format($row["resolution_cost"], 2) : '—'; ?></td>
                    <td><?php echo date("Y-m-d", strtotime($row["created_at"] ?? 'now')); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Tasks Report -->
    <div class="report-section">
        <h2>Tasks Report</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM tasks")->fetch_assoc()["count"]; ?></div><div class="label">Total Tasks</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status='resolved'")->fetch_assoc()["count"]; ?></div><div class="label">Resolved</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status='pending'")->fetch_assoc()["count"]; ?></div><div class="label">Pending</div></div>
            <div class="stat-box"><div class="number cost-amount">$<?php echo number_format($conn->query("SELECT SUM(resolution_cost) as total FROM tasks WHERE resolution_cost > 0")->fetch_assoc()["total"] ?? 0, 2); ?></div><div class="label">Total Task Costs</div></div>
        </div>

        <h3>By Status</h3>
        <table><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>
        <?php while($row = $tasks_by_status->fetch_assoc()): ?>
            <tr><td><span class="status-badge status-<?php echo $row["status"] ?? 'pending'; ?>"><?php echo ucfirst($row["status"] ?? 'pending'); ?></span></td><td><?php echo $row["count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>By Supervisor</h3>
        <table><thead><tr><th>Supervisor</th><th>Task Count</th></tr></thead><tbody>
        <?php while($row = $tasks_by_supervisor->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row["fullname"] ?? '—'); ?></td><td><?php echo $row["task_count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>Recent Tasks</h3>
        <table>
            <thead><tr><th>Title</th><th>Student</th><th>Supervisor</th><th>Status</th><th>Resolution Cost</th><th>Date</th></tr></thead>
            <tbody>
            <?php while($row = $recent_tasks->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["title"] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row["student_name"] ?? "N/A"); ?></td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "—"); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"] ?? 'pending'; ?>"><?php echo ucfirst($row["status"] ?? 'pending'); ?></span></td>
                    <td><?php echo ($row["resolution_cost"] ?? 0) > 0 ? '$' . number_format($row["resolution_cost"], 2) : '—'; ?></td>
                    <td><?php echo date("Y-m-d", strtotime($row["created_at"] ?? 'now')); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Supervisor-Student Assignments -->
    <div class="report-section">
        <h2>Supervisor-Student Assignments</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM users WHERE role='supervisor'")->fetch_assoc()["count"]; ?></div><div class="label">Total Supervisors</div></div>
            <div class="stat-box"><div class="number"><?php echo $total_assigned_students; ?></div><div class="label">Assigned Students</div></div>
            <div class="stat-box"><div class="number"><?php echo $total_unassigned_students; ?></div><div class="label">Unassigned Students</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM supervisor_assignments")->fetch_assoc()["count"]; ?></div><div class="label">Total Assignments</div></div>
        </div>

        <h3>Students per Supervisor</h3>
        <table><thead><tr><th>Supervisor</th><th>Assigned Students</th></tr></thead><tbody>
        <?php while($row = $supervisor_assignments->fetch_assoc()): ?>
            <tr><td><?php echo htmlspecialchars($row["supervisor_name"] ?? '—'); ?></td><td><?php echo $row["student_count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>
    </div>

    <!-- Repair Costs Summary -->
    <div class="report-section">
        <h2>Repair Costs Summary</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number cost-amount">$<?php echo number_format($total_repair_costs, 2); ?></div><div class="label">Total Repair Costs</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM repair_costs")->fetch_assoc()["count"]; ?></div><div class="label">Total Entries</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM repair_costs WHERE complaint_id IS NOT NULL")->fetch_assoc()["count"]; ?></div><div class="label">From Complaints</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM repair_costs WHERE task_id IS NOT NULL")->fetch_assoc()["count"]; ?></div><div class="label">From Tasks</div></div>
        </div>

        <h3>By Source Type</h3>
        <table><thead><tr><th>Source Type</th><th>Count</th><th>Total Cost</th></tr></thead><tbody>
        <?php while($row = $repair_costs_by_source->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["source_type"] ?? '—'); ?></td>
                <td><?php echo $row["count"] ?? 0; ?></td>
                <td class="cost-amount">$<?php echo number_format($row["total_cost"] ?? 0, 2); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>Recent Repair Costs</h3>
        <table>
            <thead><tr><th>Date</th><th>Description</th><th>Source</th><th>Supervisor</th><th>Cost</th></tr></thead>
            <tbody>
            <?php while($row = $recent_repair_costs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("Y-m-d", strtotime($row["date"] ?? 'now')); ?></td>
                    <td class="description-cell"><?php echo htmlspecialchars($row["description"] ?? '—'); ?></td>
                    <td><?php 
                        if (!empty($row["complaint_type"])) {
                            echo "Complaint: " . htmlspecialchars($row["complaint_type"]);
                        } elseif (!empty($row["task_title"])) {
                            echo "Task: " . htmlspecialchars($row["task_title"]);
                        } else {
                            echo "Manual Entry";
                        }
                    ?></td>
                    <td><?php echo htmlspecialchars($row["supervisor_name"] ?? "—"); ?></td>
                    <td class="cost-amount">$<?php echo number_format($row["cost"] ?? 0, 2); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Room Change Requests Report -->
    <div class="report-section">
        <h2>Room Change Requests Report</h2>
        <div class="stats-grid">
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM room_change_requests")->fetch_assoc()["count"]; ?></div><div class="label">Total Requests</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM room_change_requests WHERE status='pending'")->fetch_assoc()["count"]; ?></div><div class="label">Pending</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM room_change_requests WHERE status='approved'")->fetch_assoc()["count"]; ?></div><div class="label">Approved</div></div>
            <div class="stat-box"><div class="number"><?php echo $conn->query("SELECT COUNT(*) as count FROM room_change_requests WHERE status='rejected'")->fetch_assoc()["count"]; ?></div><div class="label">Rejected</div></div>
        </div>

        <h3>By Status</h3>
        <table><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>
        <?php while($row = $room_change_by_status->fetch_assoc()): ?>
            <tr><td><span class="status-badge status-<?php echo $row["status"] ?? 'pending'; ?>"><?php echo ucfirst($row["status"] ?? 'pending'); ?></span></td><td><?php echo $row["count"] ?? 0; ?></td></tr>
        <?php endwhile; ?>
        </tbody></table>

        <h3>Recent Room Change Requests</h3>
        <table>
            <thead><tr><th>Student</th><th>Current Room</th><th>New Room</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php while($row = $recent_room_changes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["fullname"] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row["current_room"] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row["new_room"] ?? '—'); ?></td>
                    <td class="description-cell"><?php echo nl2br(htmlspecialchars($row["reason"] ?? '')); ?></td>
                    <td><span class="status-badge status-<?php echo $row["status"] ?? 'pending'; ?>"><?php echo ucfirst($row["status"] ?? 'pending'); ?></span></td>
                    <td><?php echo date("Y-m-d", strtotime($row["request_date"] ?? 'now')); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>
</div>

</body>
</html>