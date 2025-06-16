<?php
session_start(); // MUST be the very first line

include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    // User not logged in, redirect or show message
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    // No saving ID provided
    header("Location: savings.php");
    exit;
}

$saving_id = intval($_GET['id']);

if ($saving_id <= 0) {
    // Invalid saving ID
    header("Location: savings.php");
    exit;
}

// Get saving amount
$sql = "SELECT amount FROM saving WHERE saving_id = $saving_id AND user_id = $user_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    // Saving not found
    header("Location: savings.php");
    exit;
}

$saving = $result->fetch_assoc();
$saving_amount = $saving['amount'];

// Delete saving
$conn->query("DELETE FROM saving WHERE saving_id = $saving_id AND user_id = $user_id");

// Update wallet balance
$wallet_res = $conn->query("SELECT balance FROM wallet WHERE user_id = $user_id");

if ($wallet_res && $wallet_res->num_rows > 0) {
    $wallet = $wallet_res->fetch_assoc();
    $new_balance = $wallet['balance'] + $saving_amount;
    $conn->query("UPDATE wallet SET balance = $new_balance WHERE user_id = $user_id");
} else {
    // Create wallet record if not exists
    $conn->query("INSERT INTO wallet (user_id, balance) VALUES ($user_id, $saving_amount)");
}

// Redirect back
header("Location: saving.php");
exit;
?>
