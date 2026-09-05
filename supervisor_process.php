<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['message']) && !empty($_POST['message'])){
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$sender_id','$receiver_id','$message')";
    mysqli_query($conn, $sql);
}

header("Location: supervisor.php");
exit();
?>
