<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/connectDB.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (!$userId || $username === '' || strlen($username) > 100 || ($password !== '' && strlen($password) < 6)) {
    header('Location: users.php?status=invalid');
    exit;
}

if ($password !== $confirmPassword) {
    header('Location: users.php?status=mismatch');
    exit;
}

try {
    if ($password === '') {
        $stmt = mysqli_prepare($conn, 'UPDATE tb_users SET username = ? WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'si', $username, $userId);
    } else {
        // The new password is converted to a hash before saving.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, 'UPDATE tb_users SET username = ?, password_hash = ? WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $username, $passwordHash, $userId);
    }
    mysqli_stmt_execute($stmt);
    header('Location: users.php?status=update_success');
    exit;
} catch (mysqli_sql_exception $exception) {
    if ($exception->getCode() === 1062) {
        header('Location: users.php?status=duplicate');
        exit;
    }
}

http_response_code(500);
echo 'ไม่สามารถแก้ไขข้อมูลผู้ใช้ได้';