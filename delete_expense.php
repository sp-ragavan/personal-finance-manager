<?php
session_start();
include 'db_connect.php';

$id = $_GET['id'];
$sql = "SELECT amount, user_id FROM expense WHERE expense_id = $id";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

// Restore amount to wallet
$conn->query("UPDATE wallet SET balance = balance + {$row['amount']}, last_updated = NOW() WHERE user_id = {$row['user_id']}");

// Delete expense
$conn->query("DELETE FROM expense WHERE expense_id = $id");

header("Location: expense.php");
?>
