<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch totals
$totals = [];

$tables = ['income', 'expense', 'budget', 'debt', 'saving'];
foreach ($tables as $table) {
    $res = $conn->query("SELECT SUM(amount) AS total FROM $table WHERE user_id = $user_id");
    $row = $res->fetch_assoc();
    $totals[$table] = $row['total'] ?? 0;
}

// Most expensive category
$exp = $conn->query("SELECT category, SUM(amount) AS total FROM expense WHERE user_id = $user_id GROUP BY category ORDER BY total DESC LIMIT 1")->fetch_assoc();

// Wallet Info
$wallet = $conn->query("SELECT balance, last_updated FROM wallet WHERE user_id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="wallet_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <a href="saving.php">Saving</a>
    <a class="active" href="dashboard.php">Dashboard</a>
    <a href="profile.php">Profile</a>
    <a href="javascript:void(0);" onclick="toggleWallet()">Wallet</a>
</div>

<!-- Wallet Pop-up -->
<div id="walletPopup" class="wallet-popup">
    <p><strong>Balance:</strong> ₹<?php echo number_format($wallet['balance'], 2); ?></p>
    <p><strong>Last Updated:</strong> <?php echo $wallet['last_updated']; ?></p>
    <button onclick="toggleWallet()">Close</button>
</div>

<!-- Dashboard -->
<div class="acontainer">
    <h2>Dashboard Summary</h2>

    <canvas id="financeChart" width="400" height="200"></canvas>

    <script>
        const ctx = document.getElementById('financeChart').getContext('2d');
        const financeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Income', 'Expense', 'Budget', 'Debt', 'Saving'],
                datasets: [{
                    label: '₹ Amount',
                    data: [
                        <?php echo $totals['income']; ?>,
                        <?php echo $totals['expense']; ?>,
                        <?php echo $totals['budget']; ?>,
                        <?php echo $totals['debt']; ?>,
                        <?php echo $totals['saving']; ?>
                    ],
                    backgroundColor: ['#4caf50', '#f44336', '#ff9800', '#2196f3', '#9c27b0']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Financial Overview'
                    }
                }
            }
        });
    </script>

    <h3>Most Expensive Category: <?php echo $exp ? $exp['category'] . " (₹" . number_format($exp['total'], 2) . ")" : "None"; ?></h3>
</div>

<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>
</body>
</html>
