<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/connectDB.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($username === '' || strlen($username) > 100 || strlen($password) < 6) {
    header('Location: users.php?status=invalid');
    exit;
}

if ($password !== $confirmPassword) {
    header('Location: users.php?status=mismatch');
    exit;
}

// Hash before saving; the real password is never stored in the database.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, 'INSERT INTO tb_users (username, password_hash) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'ss', $username, $passwordHash);

try {
    mysqli_stmt_execute($stmt);
    header('Location: users.php?status=success');
    exit;
} catch (mysqli_sql_exception $exception) {
    if ($exception->getCode() === 1062) {
        header('Location: users.php?status=duplicate');
        exit;
    }
}

http_response_code(500);
echo 'ไม่สามารถบันทึกข้อมูลผู้ใช้ได้';