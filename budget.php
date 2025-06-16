<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Add Budget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_budget"])) {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $type = $_POST["type"];

    $stmt = $conn->prepare("INSERT INTO budget (user_id, category, amount, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $category, $amount, $type);
    $stmt->execute();
}

// Fetch Budgets
$budget_query = "SELECT * FROM budget WHERE user_id = $user_id ORDER BY created_at DESC";
$budget_result = $conn->query($budget_query);

// Wallet info
$wallet_sql = "SELECT balance, last_updated FROM wallet WHERE user_id = $user_id";
$wallet_result = $conn->query($wallet_sql);
$wallet = $wallet_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="wallet_style.css">
    <script src="wallet_popup.js"></script>
</head>
<body>

<!-- Navigation -->
<div class="navbar">
    <a href="home.php">Home</a>
    <a href="income.php">Income</a>
    <a href="expense.php">Expense</a>
    <a class="active" href="budget.php">Budget</a>
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

<!-- Add Budget -->
<div class="container">
    <h2>Add Budget</h2>
    <form method="POST">
        <input type="text" name="category" placeholder="Category" required>
        <input type="number" name="amount" placeholder="Amount" required>
        <select name="type" required>
            <option value="Weekly">Weekly</option>
            <option value="Monthly">Monthly</option>
        </select>
        <button type="submit" name="add_budget">Add Budget</button>
    </form>
</div>

<!-- Budget Table -->
<div class="acontainer">
    <h2>Your Budgets</h2>
    <table align="center" cellspacing=5 cellpadding=5>
        <tr>
            <th>Category</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $budget_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row["category"]; ?></td>
            <td>₹<?php echo number_format($row["amount"], 2); ?></td>
            <td><?php echo $row["type"]; ?></td>
            <td><?php echo date("d M Y", strtotime($row["created_at"])); ?></td>
            <td>
                <a href="edit_budget.php?id=<?php echo $row["budget_id"]; ?>">Edit</a> |
                <a href="delete_budget.php?id=<?php echo $row["budget_id"]; ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>

</body>
</html>
