<?php
include 'db_connect.php';

function transfer_to_saving($user_id) {
    global $conn;

    // Check if it's month end
    $today = date("d");
    if ($today != date("t")) return;

    // Get wallet balance
    $res = $conn->query("SELECT balance FROM wallet WHERE user_id = $user_id");
    $row = $res->fetch_assoc();
    $balance = $row['balance'];

    if ($balance <= 0) return;

    // Transfer to normal saving
    $conn->query("INSERT INTO saving(user_id, amount, target_amount, type) VALUES ($user_id, $balance, 0, 'Normal')");

    // Reset wallet
    $conn->query("UPDATE wallet SET balance = 0 WHERE user_id = $user_id");
}
?>
