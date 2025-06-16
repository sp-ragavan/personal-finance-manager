<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Add Expense
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_expense"])) {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $purpose = $_POST["purpose"];
    $expense_date = $_POST["expense_date"];

    $stmt = $conn->prepare("INSERT INTO expense (user_id, category, amount, purpose, expense_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $category, $amount, $purpose, $expense_date);
    $stmt->execute();

    // Update wallet
    $conn->query("UPDATE wallet SET balance = balance - $amount, last_updated = NOW() WHERE user_id = $user_id");
}

// After inserting income
include 'wallet_functions.php';
update_wallet_balance($user_id);


// Fetch expense data
$expense_query = "SELECT * FROM expense WHERE user_id = $user_id ORDER BY expense_date DESC";
$expense_result = $conn->query($expense_query);

// Budget comparison data
$budget_query = "SELECT category, SUM(amount) as total_budget FROM budget WHERE user_id = $user_id GROUP BY category";
$budget_result = $conn->query($budget_query);

$budget_data = [];
while ($row = $budget_result->fetch_assoc()) {
    $budget_data[$row['category']] = $row['total_budget'];
}

// Expense totals
$expense_summary_query = "SELECT category, SUM(amount) as total_expense FROM expense WHERE user_id = $user_id GROUP BY category";
$expense_summary_result = $conn->query($expense_summary_query);

$expense_data = [];
while ($row = $expense_summary_result->fetch_assoc()) {
    $expense_data[$row['category']] = $row['total_expense'];
}

// Wallet info
$wallet_sql = "SELECT balance, last_updated FROM wallet WHERE user_id = $user_id";
$wallet_result = $conn->query($wallet_sql);
$wallet = $wallet_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expense Management</title>
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
    <a class="active" href="expense.php">Expense</a>
    <a href="budget.php">Budget</a>
    <a href="debt.php">Debt</a>
    <a href="saving.php">Saving</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">Profile</a>
    <a href="javascript:void(0);" onclick="toggleWallet()">Wallet</a>
</div>

<!-- Wallet Popup -->
<div id="walletPopup" class="wallet-popup">
    <p><strong>Balance:</strong> ₹<?php echo number_format($wallet['balance'], 2); ?></p>
    <p><strong>Last Updated:</strong> <?php echo $wallet['last_updated']; ?></p>
    <button onclick="toggleWallet()">Close</button>
</div>

<!-- Flex Container -->
<div class="expense-wrapper">
    <!-- Add Expense -->
    <div class="container left-section">
        <h2>Add Expense</h2>
        <form method="POST">
            <input type="text" name="category" placeholder="Category" required>
            <input type="number" name="amount" placeholder="Amount" required>
            <input type="text" name="purpose" placeholder="Purpose">
            <input type="date" name="expense_date" required>
            <button type="submit" name="add_expense">Add Expense</button>
        </form>
    </div>

    <!-- Expense Table -->
    <div class="acontainer right-section">
        <h2>Expense History</h2>
        <table align="center" cellspacing=5 cellpadding=5>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Purpose</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $expense_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row["category"]; ?></td>
                <td>₹<?php echo number_format($row["amount"], 2); ?></td>
                <td><?php echo $row["purpose"]; ?></td>
                <td><?php echo $row["expense_date"]; ?></td>
                <td>
                    <a href="edit_expense.php?id=<?php echo $row["expense_id"]; ?>">Edit</a> |
                    <a href="delete_expense.php?id=<?php echo $row["expense_id"]; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>


<!-- Budget Comparison -->
<div class="acontainer">
    <h2>Expense vs Budget</h2>
    <canvas id="expenseChart"></canvas>
    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($budget_data)); ?>,
                datasets: [{
                    label: 'Budget',
                    data: <?php echo json_encode(array_values($budget_data)); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)'
                },
                {
                    label: 'Expense',
                    data: <?php
                        $expenses = [];
                        foreach ($budget_data as $cat => $amt) {
                            $expenses[] = $expense_data[$cat] ?? 0;
                        }
                        echo json_encode($expenses);
                    ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                }]
            }
        });
    </script>
</div>

<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>

</body>
</html>
