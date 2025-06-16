<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<h1>PERSONAL FINANCE MANAGEMENT SYSTEM</h1>
<h1>Welcome, <?php echo $_SESSION["username"]; ?>!</h1>

<div class="top-center">
    <a href="profile.php" class="nav">Profile</a>
</div>

<div class="main-sections">
    <div class="left">
        <a href="income.php" class="nav">Income</a><br>
        <a href="saving.php" class="nav">Saving</a><br>
        <a href="budget.php" class="nav">Budget</a><br>
    </div>
    <div class="right">
        <a href="expense.php" class="nav">Expense</a><br>
        <a href="debt.php" class="nav">Debt</a><br>
        <a href="dashboard.php" class="nav">Dashboard</a><br>
    </div>

</div>

<div class="bottom-center">
    <a href="logout.php" class="logout-button">Logout</a>
</div>
</body>
</html>