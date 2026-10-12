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
$role = $_POST['role'] ?? 'user';

// Validate role against the allowed enum values.
$allowedRoles = ['user', 'merchant', 'admin'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'user';
}

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
        $stmt = mysqli_prepare($conn, 'UPDATE tb_users SET username = ?, role = ? WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $username, $role, $userId);
    } else {
        // The new password is converted to a hash before saving.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, 'UPDATE tb_users SET username = ?, password_hash = ?, role = ? WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $username, $passwordHash, $role, $userId);
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