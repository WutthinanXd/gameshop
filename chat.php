<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/connectDB.php';

start_app_session();
require_login();

$current_user_id = $_SESSION['user_id'] ?? 0;
$room_id = (int)($_GET['room_id'] ?? 0);

// บันทึกข้อความใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg_text = trim($_POST['message_text'] ?? '');
    if ($msg_text !== '' && $room_id > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO tb_chat_messages (room_id, sender_id, message_text) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $room_id, $current_user_id, $msg_text);
        mysqli_stmt_execute($stmt);
        header("Location: chat.php?room_id=" . $room_id);
        exit;
    }
}

// ดึงรายการข้อความใน ห้องแชทปัจจุบัน
$messages = [];
if ($room_id > 0) {
    $sql_msg = "SELECT m.*, u.username 
                FROM tb_chat_messages m 
                JOIN tb_users u ON m.sender_id = u.user_id 
                WHERE m.room_id = ? 
                ORDER BY m.created_at ASC";
    $stmt_msg = mysqli_prepare($conn, $sql_msg);
    mysqli_stmt_bind_param($stmt_msg, "i", $room_id);
    mysqli_stmt_execute($stmt_msg);
    $res_msg = mysqli_stmt_get_result($stmt_msg);
    while ($m = mysqli_fetch_assoc($res_msg)) {
        $messages[] = $m;
    }
}
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ระบบแชท / ติดต่อแอดมิน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 800px;">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>ห้องแชท (Room #<?= $room_id ?>)</h5>
            <a href="index.php" class="btn btn-sm btn-light">กลับหน้าหลัก</a>
        </div>
        <div class="card-body overflow-auto" style="height: 400px; background-color: #f8f9fa;">
            <?php foreach ($messages as $msg): ?>
                <?php $is_me = ($msg['sender_id'] == $current_user_id); ?>
                <div class="d-flex mb-3 <?= $is_me ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="p-3 rounded-3 <?= $is_me ? 'bg-primary text-white' : 'bg-white border' ?>" style="max-width: 75%;">
                        <div class="small fw-bold mb-1"><?= htmlspecialchars($msg['username']) ?></div>
                        <div><?= nl2br(htmlspecialchars($msg['message_text'])) ?></div>
                        <div class="text-end small opacity-75 mt-1" style="font-size: 0.75rem;">
                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer">
            <form method="POST" action="chat.php?room_id=<?= $room_id ?>">
                <div class="input-group">
                    <input type="text" name="message_text" class="form-control" placeholder="พิมพ์ข้อความที่นี่..." required>
                    <button type="submit" name="send_message" class="btn btn-primary"><i class="bi bi-send-fill"></i> ส่ง</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>