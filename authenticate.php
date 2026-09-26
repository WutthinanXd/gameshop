<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/connectDB.php';

start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    header('Location: login.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT user_id, username, password_hash FROM tb_users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['login_error'] = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['username'] = $user['username'];
header('Location: index.php');
exit;