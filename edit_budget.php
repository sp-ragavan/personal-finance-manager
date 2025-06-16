<?php
session_start();
include 'db_connect.php';

$id = $_GET["id"];
$query = "SELECT * FROM budget WHERE budget_id = $id";
$result = $conn->query($query);
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $type = $_POST["type"];

    $stmt = $conn->prepare("UPDATE budget SET category=?, amount=?, type=? WHERE budget_id=?");
    $stmt->bind_param("sdsi", $category, $amount, $type, $id);
    $stmt->execute();

    header("Location: budget.php");
}
?>

<form method="POST">
    <h2>Edit Budget</h2>
    <input type="text" name="category" value="<?php echo $data['category']; ?>" required>
    <input type="number" name="amount" value="<?php echo $data['amount']; ?>" required>
    <select name="type" required>
        <option value="Weekly" <?php if($data['type']=='Weekly') echo 'selected'; ?>>Weekly</option>
        <option value="Monthly" <?php if($data['type']=='Monthly') echo 'selected'; ?>>Monthly</option>
    </select>
    <button type="submit">Update Budget</button>
</form>
