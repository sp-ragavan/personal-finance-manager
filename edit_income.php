<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }

$id = $_GET["id"];
$sql = "SELECT * FROM income WHERE income_id = $id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $source = $_POST["source"];
    $income_date = $_POST["income_date"];

    // Update wallet: get old amount
    $old_amount = $data['amount'];
    $diff = $amount - $old_amount;
    $conn->query("UPDATE wallet SET balance = balance + $diff, last_updated = NOW() WHERE user_id = ".$_SESSION["user_id"]);

    // Update income
    $sql = "UPDATE income SET category=?, amount=?, source=?, income_date=? WHERE income_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $category, $amount, $source, $income_date, $id);
    $stmt->execute();

    header("Location: income.php");
}
// After inserting income
include 'wallet_functions.php';
update_wallet_balance($user_id);

?>

<form method="POST">
    <h2>Edit Income</h2>
    <input type="text" name="category" value="<?php echo $data['category']; ?>" required>
    <input type="number" name="amount" value="<?php echo $data['amount']; ?>" required>
    <input type="text" name="source" value="<?php echo $data['source']; ?>">
    <input type="date" name="income_date" value="<?php echo $data['income_date']; ?>" required>
    <button type="submit">Update</button>
</form>
