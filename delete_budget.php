<?php
session_start();
include 'db_connect.php';
$id = $_GET['id'];
$conn->query("DELETE FROM budget WHERE budget_id = $id");
header("Location: budget.php");
?>
