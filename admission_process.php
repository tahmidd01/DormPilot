<?php
session_start();


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$servername = "localhost";
$username = "root";     
$password = "";         
$dbname = "hostel_system";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$nid = $_POST['nid'];
$course = $_POST['course'];
$father_name = $_POST['father'];
$father_phone = $_POST['father_phone'];
$mother_name = $_POST['mother'];
$mother_phone = $_POST['mother_phone'];
$room = $_POST['room'];


$stmt = $conn->prepare("INSERT INTO admissions 
    (name, phone, email, nid, course, father_name, father_phone, mother_name, mother_phone, preferred_room)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss", $name, $phone, $email, $nid, $course, $father_name, $father_phone, $mother_name, $mother_phone, $room);

if ($stmt->execute()) {
    
    header("Location: admission.php?success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
