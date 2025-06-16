<?php
session_start();
include 'db_connect.php';
$id = $_GET['id'];
$conn->query("DELETE FROM debt WHERE debt_id = $id");
header("Location: debt.php");
?>
