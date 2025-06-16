<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }

$id = $_GET["id"];
$sql = "SELECT * FROM expense WHERE expense_id = $id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $purpose = $_POST["purpose"];
    $expense_date = $_POST["expense_date"];

    $old_amount = $data['amount'];
    $diff = $amount - $old_amount;
    $conn->query("UPDATE wallet SET balance = balance - $diff, last_updated = NOW() WHERE user_id = ".$_SESSION["user_id"]);

    $sql = "UPDATE expense SET category=?, amount=?, purpose=?, expense_date=? WHERE expense_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $category, $amount, $purpose, $expense_date, $id);
    $stmt->execute();

    header("Location: expense.php");
}
// After inserting income
include 'wallet_functions.php';
update_wallet_balance($user_id);

?>

<form method="POST">
    <h2>Edit Expense</h2>
    <input type="text" name="category" value="<?php echo $data['category']; ?>" required>
    <input type="number" name="amount" value="<?php echo $data['amount']; ?>" required>
    <input type="text" name="purpose" value="<?php echo $data['purpose']; ?>">
    <input type="date" name="expense_date" value="<?php echo $data['expense_date']; ?>" required>
    <button type="submit">Update</button>
</form>
