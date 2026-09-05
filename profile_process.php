<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION["user_id"];
$fullname = $_POST["fullname"];
$email    = $_POST["email"];
$phone    = $_POST["phone"];
$room     = $_POST["room"];
$password = $_POST["password"];


$hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;


$query = "SELECT * FROM profile WHERE user_id='$user_id'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0) {
   
    if($hashed){
        $sql = "UPDATE profile SET 
                    fullname='$fullname',
                    email='$email',
                    phone='$phone',
                    room='$room',
                    password='$hashed'
                WHERE user_id='$user_id'";
    } else {
        $sql = "UPDATE profile SET 
                    fullname='$fullname',
                    email='$email',
                    phone='$phone',
                    room='$room'
                WHERE user_id='$user_id'";
    }
} else {
   
    $sql = "INSERT INTO profile (user_id, fullname, email, phone, room, password) VALUES (
        '$user_id',
        '$fullname',
        '$email',
        '$phone',
        '$room',
        '".($hashed ?? '')."'
    )";
}

if (mysqli_query($conn, $sql)) {

    $_SESSION["fullname"] = $fullname;
    $_SESSION["email"]    = $email;
    $_SESSION["phone"]    = $phone;
    $_SESSION["room"]     = $room;

    header("Location: profile.php?success=1");
    exit();
} else {
    echo "Update Failed: " . mysqli_error($conn);
}
?>
