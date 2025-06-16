<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];

// Fetch current password from DB
$res = $conn->query("SELECT password FROM user WHERE user_id = $user_id");
$row = $res->fetch_assoc();

if (!password_verify($current_password, $row['password'])) {
    echo "<script>alert('Incorrect current password'); window.history.back();</script>";
    exit;
}

// Update details
$update_query = "UPDATE user SET username='$username', email='$email'";

// If user provided a new password
if (!empty($new_password)) {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query .= ", password='$hashed'";
}

$update_query .= " WHERE user_id=$user_id";

if ($conn->query($update_query)) {
    echo "<script>alert('Profile updated successfully'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('Error updating profile'); window.history.back();</script>";
}
?>
