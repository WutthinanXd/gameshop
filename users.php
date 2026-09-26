<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/connectDB.php';

$result = mysqli_query($conn, 'SELECT user_id, username, created_at FROM tb_users ORDER BY user_id DESC');
$status = $_GET['status'] ?? '';
$messages = [
    'success' => ['success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว'],
    'duplicate' => ['danger', 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาเลือกชื่ออื่น'],
    'invalid' => ['danger', 'กรุณากรอกข้อมูลให้ครบ และรหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'],
    'mismatch' => ['danger', 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน'],
    'update_success' => ['success', 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว'],
    'delete_success' => ['success', 'ลบผู้ใช้เรียบร้อยแล้ว'],
    'cannot_delete_self' => ['warning', 'ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบอยู่ได้'],
];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-4" style="max-width: 960px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">จัดการผู้ใช้</h1>
                <p class="text-secondary mb-0">เพิ่มบัญชีสำหรับเข้าใช้งานระบบ</p>
            </div>
            <div>
                <a href="index1.php" class="btn btn-outline-secondary">กลับหน้าหลัก</a>
                <a href="logout.php" class="btn btn-outline-danger">ออกจากระบบ</a>
            </div>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert alert-<?= $messages[$status][0] ?>" role="alert">
                <?= htmlspecialchars($messages[$status][1], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <section class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">เพิ่มผู้ใช้ใหม่</h2>
                        <form action="save_user.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <input id="username" name="username" type="text" class="form-control" maxlength="100" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input id="password" name="password" type="password" class="form-control" minlength="6" required>
                                <div class="form-text">อย่างน้อย 6 ตัวอักษร</div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                <input id="confirm_password" name="confirm_password" type="password" class="form-control" minlength="6" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">บันทึกผู้ใช้</button>
                        </form>
                    </div>
                </div>
            </section>
            <section class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">รายชื่อผู้ใช้</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead><tr><th>ลำดับ</th><th>ชื่อผู้ใช้</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
                                <tbody>
                                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= (int) $user['user_id'] ?></td>
                                        <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-nowrap">
                                            <a class="btn btn-sm btn-warning" href="edit_user.php?id=<?= (int) $user['user_id'] ?>">แก้ไข</a>
                                            <?php if ((int) $user['user_id'] !== (int) $_SESSION['user_id']): ?>
                                                <form action="delete_user.php" method="post" class="d-inline" onsubmit="return confirm('ยืนยันการลบผู้ใช้นี้?');">
                                                    <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">ลบ</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>