<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$saving_id = $_GET["id"] ?? null;

if (!$saving_id) {
    header("Location: saving.php");
    exit();
}

// Fetch existing saving record
$stmt = $conn->prepare("SELECT * FROM saving WHERE saving_id = ? AND user_id = ?");
$stmt->bind_param("ii", $saving_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$saving = $result->fetch_assoc();

if (!$saving) {
    echo "Saving record not found.";
    exit();
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST["type"];
    $amount = floatval($_POST["amount"]);
    $target = isset($_POST["target"]) && $_POST["target"] !== '' ? floatval($_POST["target"]) : null;

    $stmt = $conn->prepare("UPDATE saving SET type = ?, amount = ?, target = ? WHERE saving_id = ? AND user_id = ?");
    $stmt->bind_param("sddii", $type, $amount, $target, $saving_id, $user_id);
    $stmt->execute();

    header("Location: saving.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Saving</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Saving</h2>
        <form method="POST">
            <select name="type" required>
                <option value="">--Select Type--</option>
                <option value="emergency" <?= $saving["type"] == "emergency" ? "selected" : "" ?>>Emergency</option>
                <option value="normal" <?= $saving["type"] == "normal" ? "selected" : "" ?>>Normal</option>
            </select>
            <input type="number" name="amount" step="0.01" value="<?= $saving["amount"] ?>" required>
            <input type="number" name="target" step="0.01" value="<?= $saving["target"] ?>">
            <button type="submit">Update Saving</button>
        </form>
        <a href="saving.php">⬅ Back</a>
    </div>
</body>
</html>
