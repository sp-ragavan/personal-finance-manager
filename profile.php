<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$result = $conn->query("SELECT * FROM user WHERE user_id = $user_id");
$row = $result->fetch_assoc();

$username = $row['username'];
$email = $row['email'];

// Fetch wallet data
$wallet_result = $conn->prepare("SELECT balance, last_updated FROM wallet WHERE user_id = ?");
$wallet_result->bind_param("i", $user_id);
$wallet_result->execute();
$wallet_data = $wallet_result->get_result();
$wallet = $wallet_data->fetch_assoc();

// Set default values if no wallet entry exists
if (!$wallet) {
    $wallet = [
        'balance' => 0.00,
        'last_updated' => 'N/A'
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <script src="wallet_popup.js"></script>
</head>
<body>
<!-- Navigation -->
<div class="navbar">
    <a href="home.php">Home</a>
    <a href="income.php">Income</a>
    <a href="expense.php">Expense</a>
    <a href="budget.php">Budget</a>
    <a href="debt.php">Debt</a>
    <a class="active" href="saving.php">Saving</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">Profile</a>
    <a href="javascript:void(0);" onclick="toggleWallet()">Wallet</a>

</div>

<!-- Wallet Pop-up -->
<div id="walletPopup" class="wallet-popup">
    <p><strong>Balance:</strong> ₹<?php echo number_format($wallet['balance'], 2); ?></p>
    <p><strong>Last Updated:</strong> <?php echo $wallet['last_updated']; ?></p>
    <button onclick="toggleWallet()">Close</button>
</div>

    <div class="container">
        <h2>Profile Management</h2>

        <form action="profile_update.php" method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="<?= $username ?>" required><br>

            <label>Email:</label>
            <input type="email" name="email" value="<?= $email ?>" required><br>

            <label>Current Password:</label>
            <input type="password" name="current_password" required><br>

            <label>New Password (leave blank if no change):</label>
            <input type="password" name="new_password"><br>

            <button type="submit">Update Profile</button>
        </form>
</div>

<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>

</body>
</html>
