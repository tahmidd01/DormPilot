<?php
session_start();
include "db.php";

if(!isset($_SESSION["user_id"]) || $_SESSION['role'] != 'supervisor'){
    header("Location: login.php");
    exit();
}

if(isset($_POST['title'], $_POST['message'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    mysqli_query($conn, "INSERT INTO announcements (title, message) VALUES ('$title','$message')");
    header("Location: announcements.php");
    exit();
}
?>

<form action="" method="post">
    <input type="text" name="title" placeholder="Title" required><br><br>
    <textarea name="message" placeholder="Message" required></textarea><br><br>
    <button type="submit">Post Announcement</button>
</form>
