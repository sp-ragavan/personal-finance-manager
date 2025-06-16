<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Add income
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_income"])) {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $source = $_POST["source"];
    $income_date = $_POST["income_date"];

    $sql = "INSERT INTO income (user_id, category, amount, source, income_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $user_id, $category, $amount, $source, $income_date);
    $stmt->execute();

    // Update wallet
    $conn->query("UPDATE wallet SET balance = balance + $amount, last_updated = NOW() WHERE user_id = $user_id");
}
// After inserting income
include 'wallet_functions.php';
update_wallet_balance($user_id);


// Fetch incomes
$income_sql = "SELECT * FROM income WHERE user_id = $user_id ORDER BY income_date DESC";
$income_result = $conn->query($income_sql);

// Wallet info
$wallet_sql = "SELECT balance, last_updated FROM wallet WHERE user_id = $user_id";
$wallet_result = $conn->query($wallet_sql);
$wallet = $wallet_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Income Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="wallet_style.css">
    <script src="wallet_popup.js"></script>
</head>
<body>

<!-- Navigation -->
<div class="navbar">
    <a href="home.php">Home</a>
    <a class="active" href="income.php">Income</a>
    <a href="expense.php">Expense</a>
    <a href="budget.php">Budget</a>
    <a href="debt.php">Debt</a>
    <a href="saving.php">Saving</a>
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

<!-- Income Form -->
<div class="container">
    <h2>Add Income</h2>
    <form method="POST">
        <input type="text" name="category" placeholder="Category (e.g., Salary)" required>
        <input type="number" name="amount" placeholder="Amount" required>
        <input type="text" name="source" placeholder="Source (optional)">
        <input type="date" name="income_date" required>
        <button type="submit" name="add_income">Add Income</button>
    </form>
</div>

<!-- Income Summary Table -->
<div class="acontainer">
    <h2>Income Summary</h2>
    <table align="center" cellspacing=5 cellpadding=5>
        <tr>
            <th>Category</th>
            <th>Amount</th>
            <th>Source</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $income_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['category']; ?></td>
                <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['source']; ?></td>
                <td><?php echo $row['income_date']; ?></td>
                <td>
                    <a href="edit_income.php?id=<?php echo $row['income_id']; ?>">Edit</a> | 
                    <a href="delete_income.php?id=<?php echo $row['income_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<!-- Back and Logout -->
<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>

</body>
</html>
