<?php
session_start();
require_once "../includes/config.php";

$login_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Check if admin login
    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($admin = $res->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin'] = true;
            header("Location: dashboard.php");
            exit;
        }
    }

    // 2. Check if it's a member login (check even if inactive)
    $stmt = $conn->prepare("SELECT id, member_no, password, active FROM members WHERE member_no = ? AND is_main = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($member = $res->fetch_assoc()) {
        if (password_verify($password, $member['password'])) {
            if ($member['active'] == 1) {
                $_SESSION['member_id'] = $member['id'];
                $_SESSION['member_no'] = $member['member_no'];
                header("Location: dashboard.php");
                exit;
            } else {
                $login_error = ($member['active'] == 2) ? "Please contact trust Administrator: You are In-Active Member Suspended." : "Please contact trust Administrator: You are In-Active Member Deleted.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }
}
?>

<!-- Simple Login Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - SKJSS</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-4"><?= $org_short_name ?> - Admin / Member Login</h2>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <form method="POST" action="login.php">
                <input type="text" name="username" required placeholder="Member No or Admin Username" class="form-control mb-2">
                <input type="password" name="password" required placeholder="Password" class="form-control mb-2">
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-danger mt-2"><?= $login_error ?></p>
            </form>
            <a href="../index.php" class="btn btn-success">Home</a>
        </div>
    </div>
</div>
</body>
</html>
