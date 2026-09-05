<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

// Generate Room Allocation PDF
if(isset($_GET["pdf_rooms"])){
    require_once('tcpdf/tcpdf.php');
    
    $pdf = new TCPDF();
    $pdf->SetCreator('DormPilot');
    $pdf->SetTitle('Room Allocation Report');
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Room Allocation Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 10);
    $rooms = $conn->query("SELECT r.*, COUNT(ra.user_id) as occupied FROM rooms r LEFT JOIN room_assignments ra ON r.id = ra.room_id GROUP BY r.id");
    
    $html = '<table border="1" cellpadding="5">
        <tr>
            <th>Room Number</th>
            <th>Block</th>
            <th>Capacity</th>
            <th>Occupied</th>
            <th>Available</th>
            <th>Status</th>
        </tr>';
    
    while($row = $rooms->fetch_assoc()){
        $available = $row["capacity"] - $row["occupied"];
        $status = ($row["occupied"] >= $row["capacity"]) ? "Full" : "Available";
        $html .= '<tr>
            <td>'.$row["room_number"].'</td>
            <td>'.($row["block"] ?? "-").'</td>
            <td>'.$row["capacity"].'</td>
            <td>'.$row["occupied"].'</td>
            <td>'.$available.'</td>
            <td>'.$status.'</td>
        </tr>';
    }
    
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output('room_allocation_'.date('Y-m-d').'.pdf', 'D');
    exit();
}

// Generate Monthly Complaint Log PDF
if(isset($_GET["pdf_complaints"])){
    require_once('tcpdf/tcpdf.php');
    
    $month = $_GET["month"] ?? date('Y-m');
    $pdf = new TCPDF();
    $pdf->SetCreator('DormPilot');
    $pdf->SetTitle('Monthly Complaint Log - '.$month);
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Monthly Complaint Log - '.$month, 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 10);
    $complaints = $conn->prepare("SELECT c.*, u.fullname FROM complaints c JOIN users u ON c.user_id = u.id WHERE DATE_FORMAT(c.created_at, '%Y-%m') = ? ORDER BY c.created_at");
    $complaints->bind_param("s", $month);
    $complaints->execute();
    $result = $complaints->get_result();
    
    $html = '<table border="1" cellpadding="5">
        <tr>
            <th>Date</th>
            <th>Student</th>
            <th>Type</th>
            <th>Description</th>
            <th>Status</th>
        </tr>';
    
    while($row = $result->fetch_assoc()){
        $html .= '<tr>
            <td>'.date("Y-m-d", strtotime($row["created_at"])).'</td>
            <td>'.$row["fullname"].'</td>
            <td>'.$row["type"].'</td>
            <td>'.substr($row["description"], 0, 50).'...</td>
            <td>'.ucfirst($row["status"]).'</td>
        </tr>';
    }
    
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output('complaint_log_'.$month.'.pdf', 'D');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PDF Reports - Admin</title>
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
    max-width:800px;
    margin:0 auto;
    padding:0 20px;
}
.report-section {
    background:white;
    padding:30px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.report-section h2 {
    margin-bottom:15px;
    color:#333;
}
.report-section p {
    color:#666;
    margin-bottom:20px;
}
.btn {
    padding:12px 25px;
    background:#e24a84;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:14px;
    text-decoration:none;
    display:inline-block;
    margin-right:10px;
}
.btn:hover {
    background:#ad276a;
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
.form-group input {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
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
    <div class="report-section">
        <h2>Room Allocation Report</h2>
        <p>Generate a PDF report of all room allocations and availability.</p>
        <a href="?pdf_rooms=1" class="btn">Generate Room Allocation PDF</a>
    </div>

    <div class="report-section">
        <h2>Monthly Complaint Log</h2>
        <p>Generate a PDF report of complaints for a specific month.</p>
        <form method="GET" style="display:inline;">
            <div class="form-group">
                <label>Select Month</label>
                <input type="month" name="month" value="<?php echo date('Y-m'); ?>" required>
            </div>
            <button type="submit" name="pdf_complaints" value="1" class="btn">Generate Complaint Log PDF</button>
        </form>
    </div>
</div>
</div>

</body>
</html>

