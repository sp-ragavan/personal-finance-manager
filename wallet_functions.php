<?php
include 'db_connect.php';

function update_wallet_balance($user_id) {
    global $conn;

    // Total income
    $income_res = $conn->query("SELECT SUM(amount) AS total FROM income WHERE user_id = $user_id");
    $total_income = $income_res->fetch_assoc()['total'] ?? 0;

    // Total expense
    $expense_res = $conn->query("SELECT SUM(amount) AS total FROM expense WHERE user_id = $user_id");
    $total_expense = $expense_res->fetch_assoc()['total'] ?? 0;

    // Total EMI from debt
    $emi_res = $conn->query("SELECT SUM(emi) AS total FROM debt WHERE user_id = $user_id");
    $total_emi = $emi_res->fetch_assoc()['total'] ?? 0;

    // 1% Emergency saving rule
    $emergency_saving_add = round(0.01 * $total_income, 2);

    // Remaining balance
    $balance = $total_income - $total_expense - $total_emi - $emergency_saving_add;

    // Update wallet table
    $check = $conn->query("SELECT * FROM wallet WHERE user_id = $user_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE wallet SET balance = $balance, last_updated = NOW() WHERE user_id = $user_id");
    } else {
        $conn->query("INSERT INTO wallet(user_id, balance, last_updated) VALUES ($user_id, $balance, NOW())");
    }

    // Emergency saving logic
    $emergency_check = $conn->query("SELECT * FROM saving WHERE user_id = $user_id AND type = 'emergency'");
    $normal_check = $conn->query("SELECT * FROM saving WHERE user_id = $user_id AND type = 'normal'");

    $emergency_row = $emergency_check->fetch_assoc();
    $normal_row = $normal_check->fetch_assoc();

    $emergency_amount = $emergency_row['amount'] ?? 0;
    $emergency_target = $emergency_row['target'] ?? null;

    if (is_null($emergency_target) || $emergency_target == 0) {
        // No target, save to normal
        if ($normal_row) {
            $new_normal_amount = $normal_row['amount'] + $emergency_saving_add;
            $conn->query("UPDATE saving SET amount = $new_normal_amount WHERE saving_id = " . $normal_row['saving_id']);
        } else {
            $conn->query("INSERT INTO saving (user_id, type, amount) VALUES ($user_id, 'normal', $emergency_saving_add)");
        }
    } else {
        // Check if emergency target reached
        if ($emergency_amount >= $emergency_target) {
            if ($normal_row) {
                $new_normal_amount = $normal_row['amount'] + $emergency_saving_add;
                $conn->query("UPDATE saving SET amount = $new_normal_amount WHERE saving_id = " . $normal_row['saving_id']);
            } else {
                $conn->query("INSERT INTO saving (user_id, type, amount) VALUES ($user_id, 'normal', $emergency_saving_add)");
            }
        } else {
            $new_emergency_amount = $emergency_amount + $emergency_saving_add;
            if ($emergency_row) {
                $conn->query("UPDATE saving SET amount = $new_emergency_amount WHERE saving_id = " . $emergency_row['saving_id']);
            } else {
                $conn->query("INSERT INTO saving (user_id, type, amount) VALUES ($user_id, 'emergency', $emergency_saving_add)");
            }
        }
    }

    // ------------------------------
    // Process EMI: reduce tenor, delete debt if complete
    // ------------------------------
    $debt_result = $conn->query("SELECT * FROM debt WHERE user_id = $user_id");

    while ($debt = $debt_result->fetch_assoc()) {
        $debt_id = $debt['debt_id'];
        $tenor = $debt['tenor'];

        
        if ($tenor > 1) {
            $new_tenor = $tenor - 1;
            $amount = $debt['amount'];
            $rate = $debt['interest_rate'];

            if ($rate > 0) {
                $r = $rate / 12 / 100;
                $emi = ($amount * $r * pow(1 + $r, $new_tenor)) / (pow(1 + $r, $new_tenor) - 1);
            } else {
                $emi = $amount / $new_tenor;
            }

            $stmt = $conn->prepare("UPDATE debt SET tenor = ?, emi = ? WHERE debt_id = ?");
            $stmt->bind_param("idi", $new_tenor, $emi, $debt_id);
            $stmt->execute();
        } else {
            // Last month - delete the debt
            $conn->query("DELETE FROM debt WHERE debt_id = $debt_id");
        }
    }
}
?>
