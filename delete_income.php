<?php
session_start();
include 'db_connect.php';

$id = $_GET['id'];
$sql = "SELECT amount, user_id FROM income WHERE income_id = $id";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

// Deduct from wallet
$conn->query("UPDATE wallet SET balance = balance - {$row['amount']}, last_updated = NOW() WHERE user_id = {$row['user_id']}");

// Delete income
$conn->query("DELETE FROM income WHERE income_id = $id");

header("Location: income.php");
?>
