<?php
session_start();
include "db.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$student_name = $_POST['student_name'];
$student_id = $_POST['student_id'];
$phone = $_POST['phone'];
$current_room = $_POST['current_room'];
$new_room = $_POST['new_room'];
$reason = $_POST['reason'];


$stmt = $conn->prepare("INSERT INTO room_change_requests (student_name, student_id, phone, current_room, new_room, reason) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $student_name, $student_id, $phone, $current_room, $new_room, $reason);


if($stmt->execute()){
    header("Location: roomchange.php?success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
