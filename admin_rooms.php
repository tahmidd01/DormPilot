<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$message = "";

// Create uploads directory if it doesn't exist
if(!file_exists("uploads/rooms")){
    mkdir("uploads/rooms", 0777, true);
}

// Add room
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_room"])){
    $room_number = $_POST["room_number"];
    $block = $_POST["block"];
    $capacity = $_POST["capacity"];
    $image = "";
    
    if(isset($_FILES["room_image"]) && $_FILES["room_image"]["error"] == 0){
        $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
        $ext = strtolower(pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)){
            $image = "room_" . time() . "_" . basename($_FILES["room_image"]["name"]);
            move_uploaded_file($_FILES["room_image"]["tmp_name"], "uploads/rooms/" . $image);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, block, capacity, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $room_number, $block, $capacity, $image);
    
    if($stmt->execute()){
        $message = "Room added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Delete room
if(isset($_GET["delete"])){
    $id = $_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "Room deleted successfully!";
    }
    $stmt->close();
}

$rooms = $conn->query("SELECT r.*, COUNT(ra.user_id) as current_occupancy FROM rooms r LEFT JOIN room_assignments ra ON r.id = ra.room_id GROUP BY r.id ORDER BY r.room_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Rooms - Admin</title>
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
    max-width:1000px;
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
.form-row {
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
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
.form-group input, .form-group select {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
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
.btn-danger {
    background:#dc3545;
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
.status-available {
    background:#d4edda;
    color:#155724;
}
.status-full {
    background:#f8d7da;
    color:#721c24;
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
    
    <div class="form-section">
        <h2>Add New Room</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" required>
                </div>
                <div class="form-group">
                    <label>Block</label>
                    <input type="text" name="block" placeholder="e.g., Block A">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Room Image (Optional)</label>
                <input type="file" name="room_image" accept="image/*">
            </div>
            <button type="submit" name="add_room" class="btn">Add Room</button>
        </form>
    </div>

    <div class="list-section">
        <h2>All Rooms</h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Room Number</th>
                    <th>Block</th>
                    <th>Capacity</th>
                    <th>Occupancy</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $rooms->fetch_assoc()): 
                    $status = ($row["current_occupancy"] >= $row["capacity"]) ? "Full" : "Available";
                ?>
                <tr>
                    <td>
                        <?php if($row["image"]): ?>
                        <img src="uploads/rooms/<?php echo htmlspecialchars($row["image"]); ?>" alt="Room" style="width:60px; height:60px; object-fit:cover; border-radius:5px;">
                        <?php else: ?>
                        <span style="color:#999;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row["room_number"]); ?></td>
                    <td><?php echo htmlspecialchars($row["block"] ?? "-"); ?></td>
                    <td><?php echo $row["capacity"]; ?></td>
                    <td><?php echo $row["current_occupancy"]; ?>/<?php echo $row["capacity"]; ?></td>
                    <td><span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                    <td>
                        <a href="?delete=<?php echo $row["id"]; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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

