<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Handle Add Saving
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_saving"])) {
    $type = $_POST["type"];
    $amount = $_POST["amount"];
    $target = $_POST["target"] ?? null;

    $stmt = $conn->prepare("INSERT INTO saving (user_id, type, amount, target) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdd", $user_id, $type, $amount, $target);
    $stmt->execute();
}

// Fetch Wallet Info
$wallet = $conn->query("SELECT balance, last_updated FROM wallet WHERE user_id = $user_id")->fetch_assoc();

// Fetch Savings
$savings = $conn->query("SELECT * FROM saving WHERE user_id = $user_id");

$emergency = $conn->query("SELECT SUM(amount) AS total, MAX(target) AS target FROM saving WHERE user_id = $user_id AND type = 'emergency'")->fetch_assoc();
$normal = $conn->query("SELECT SUM(amount) AS total FROM saving WHERE user_id = $user_id AND type = 'normal'")->fetch_assoc();

// In income.php or here (only when saving is added)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_saving"])) {
    $type = $_POST["type"];
    $amount = floatval($_POST["amount"]);
    $target = isset($_POST["target"]) && $_POST["target"] !== '' ? floatval($_POST["target"]) : null;

    $stmt = $conn->prepare("INSERT INTO saving (user_id, type, amount, target) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdd", $user_id, $type, $amount, $target);
    $stmt->execute();

    // If saving type is income-triggered (simulate 1% emergency saving logic)
    if ($type === 'income') {
        $emergency_target_row = $conn->query("SELECT SUM(amount) AS saved, MAX(target) AS target FROM saving WHERE user_id = $user_id AND type = 'emergency'")->fetch_assoc();

        if ($emergency_target_row['saved'] < $emergency_target_row['target']) {
            $one_percent = $amount * 0.01;
            $stmt = $conn->prepare("INSERT INTO saving (user_id, type, amount, target) VALUES (?, ?, ?, ?)");
            $emergency_type = 'emergency';
            $stmt->bind_param("isdd", $user_id, $emergency_type, $one_percent, $emergency_target_row['target']);
            $stmt->execute();

            // Deduct from wallet
            $conn->query("UPDATE wallet SET balance = balance - $one_percent WHERE user_id = $user_id");
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Saving Management</title>
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

<!-- Add Saving Form -->
<div class="container">
    <h2>Add Saving</h2>
    <form method="POST">
        <select name="type" required>
            <option value="">--Select Type--</option>
            <option value="emergency">Emergency</option>
            <option value="normal">Normal</option>
        </select>
        <input type="number" name="amount" step="0.01" placeholder="Amount" required>
        <input type="number" name="target" step="0.01" placeholder="Target (for Emergency only)">
        <button type="submit" name="add_saving">Add Saving</button>
    </form>
</div>

<!-- Display Savings -->
<div class="savings-wrapper">
    <div class="container left-section">
        <h2>Your Savings</h2>

        <div class="savings-summary">
            <h3>Emergency Saving</h3>
            <p>Saved: ₹<?php echo number_format($emergency['total'], 2); ?> /
               Target: ₹<?php echo number_format($emergency['target'], 2); ?></p>
            <progress value="<?php echo $emergency['total']; ?>" max="<?php echo $emergency['target']; ?>"></progress>
        </div>

        <div class="savings-summary">
            <h3>Normal Saving</h3>
            <p>Saved: ₹<?php echo number_format($normal['total'], 2); ?></p>
        </div>
    </div>

    <div class="acontainer right-section">
        <table cellspacing="5" cellpadding="5">
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Target</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $savings->fetch_assoc()) { ?>
            <tr>
                <td><?php echo ucfirst($row["type"]); ?></td>
                <td>₹<?php echo number_format($row["amount"], 2); ?></td>
                <td><?php echo $row["target"] ? "₹" . number_format($row["target"], 2) : "-"; ?></td>
                <td><?php echo date("d M Y", strtotime($row["created_at"])); ?></td>
                <td>
                    <a href="edit_saving.php?id=<?php echo $row["saving_id"]; ?>">Edit</a> |
                    <a href="delete_saving.php?id=<?php echo $row["saving_id"]; ?>" onclick="return confirm('Delete saving?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

<div class="button-container">
    <a href="index.php" class="back-button">Back</a>
    <a href="logout.php" class="logout-button">Logout</a>
</div>

</body>
</html>
