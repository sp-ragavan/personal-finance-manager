<?php
session_start();
include 'db_connect.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM debt WHERE debt_id = $id");
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["debt_name"];
    $amount = $_POST["amount"];
    $tenor = $_POST["tenor"];
    $rate = $_POST["interest_rate"];

    $emi = ($rate > 0)
        ? ($amount * ($rate / 12 / 100) * pow(1 + ($rate / 12 / 100), $tenor)) / (pow(1 + ($rate / 12 / 100), $tenor) - 1)
        : $amount / $tenor;

    $stmt = $conn->prepare("UPDATE debt SET debt_name=?, amount=?, tenor=?, interest_rate=?, emi=? WHERE debt_id=?");
    $stmt->bind_param("sidddi", $name, $amount, $tenor, $rate, $emi, $id);
    $stmt->execute();

    header("Location: debt.php");
}
?>

<form method="POST">
    <h2>Edit Debt</h2>
    <input type="text" name="debt_name" value="<?php echo $data["debt_name"]; ?>" required>
    <input type="number" name="amount" value="<?php echo $data["amount"]; ?>" required>
    <input type="number" name="tenor" value="<?php echo $data["tenor"]; ?>" required>
    <input type="number" name="interest_rate" value="<?php echo $data["interest_rate"]; ?>" required>
    <button type="submit">Update Debt</button>
</form>
