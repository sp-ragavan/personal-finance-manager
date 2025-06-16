<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Add Debt
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_debt"])) {
    $name = $_POST["debt_name"];
    $amount = $_POST["amount"];
    $tenor = $_POST["tenor"];
    $rate = $_POST["interest_rate"];

    if ($rate > 0) {
        $r = $rate / 12 / 100;
        $emi = ($amount * $r * pow(1 + $r, $tenor)) / (pow(1 + $r, $tenor) - 1);
    } else {
        $emi = $amount / $tenor;
    }

    $stmt = $conn->prepare("INSERT INTO debt (user_id, debt_name, amount, tenor, interest_rate, emi) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issidd", $user_id, $name, $amount, $tenor, $rate, $emi);
    $stmt->execute();
}

// Fetch Debts
$debts = $conn->query("SELECT * FROM debt WHERE user_id = $user_id ORDER BY amount ASC");

// Wallet Info
$wallet = $conn->query("SELECT balance, last_updated FROM wallet WHERE user_id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debt Management</title>
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
    <a href="budget.php">Budget</a>
    <a class="active" href="debt.php">Debt</a>
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

<!-- Add Debt -->
<div class="container">
    <h2>Add Debt</h2>
    <form method="POST">
        <input type="text" name="debt_name" placeholder="Debt Name" required>
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <input type="number" name="tenor" placeholder="Tenor (in months)" required>
        <input type="number" step="0.01" name="interest_rate" placeholder="Interest Rate (%)" required>
        <button type="submit" name="add_debt">Add Debt</button>
    </form>
</div>

<!-- Debt Table -->
<div class="acontainer">
    <h2>Your Debts</h2>
    <table align="center" cellspacing=5 cellpadding=5>
        <tr>
            <th>Name</th>
            <th>Amount</th>
            <th>Tenor</th>
            <th>Interest</th>
            <th>EMI</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $debts->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row["debt_name"]; ?></td>
            <td>₹<?php echo number_format($row["amount"], 2); ?></td>
            <td><?php echo $row["tenor"]; ?> months</td>
            <td><?php echo $row["interest_rate"]; ?>%</td>
            <td>₹<?php echo number_format($row["emi"], 2); ?></td>
            <td><?php echo date("d M Y", strtotime($row["created_at"])); ?></td>
            <td>
                <a href="edit_debt.php?id=<?php echo $row["debt_id"]; ?>">Edit</a> |
                <a href="delete_debt.php?id=<?php echo $row["debt_id"]; ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
