<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/connectDB.php';

start_app_session();
require_login();


$current_user_id = $_SESSION['user_id'] ?? 0;
$current_username = $_SESSION['username'] ?? 'User';


$msg_success = '';
$msg_error = '';

$room_id = (int)($_GET['room_id'] ?? 0);
$games_list = [];
$games_query = mysqli_query($conn, "SELECT game_id, game_name FROM tb_games ORDER BY game_name ASC");
if ($games_query) 
    while ($g = mysqli_fetch_assoc($games_query)) {
        $games_list[] = $g;
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg_text = trim($_POST['message_text'] ?? '');
    if ($msg_text !== '' && $room_id > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO tb_chat_messages (room_id, sender_id, message_text) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $room_id, $current_user_id, $msg_text);
        mysqli_stmt_execute($stmt);
        header("Location: chat1.php?room_id=" . $room_id);
        exit;
    }
}


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


$chat_rooms_list = [];
$sql_rooms = "SELECT cr.*, 
                     u1.username AS buyer_name, 
                     u2.username AS seller_name,
                     p.post_title
              FROM tb_chat_rooms cr
              LEFT JOIN tb_users u1 ON cr.user_id = u1.user_id
              LEFT JOIN tb_users u2 ON cr.receiver_id = u2.user_id
              LEFT JOIN tb_posts p ON cr.post_id = p.post_id
              WHERE cr.user_id = ? OR cr.receiver_id = ?
              ORDER BY cr.created_at DESC";
$stmt_rooms = mysqli_prepare($conn, $sql_rooms);
mysqli_stmt_bind_param($stmt_rooms, "ii", $current_user_id, $current_user_id);
mysqli_stmt_execute($stmt_rooms);
$res_rooms = mysqli_stmt_get_result($stmt_rooms);
while ($r = mysqli_fetch_assoc($res_rooms)) {
    $chat_rooms_list[] = $r;
}
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE v4 | Dashboard</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        // Applications with their own theming opt out of AdminLTE's color mode
        // entirely, here as well as in the bundle.
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        // Mirror the precedence in color-mode.ts: the visitor's stored choice
        // wins, then a theme this page declared itself, then the OS preference.
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        // Flag values computed here, so the bundle does not mistake them for a
        // theme the page declared and stop following the OS preference.
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0/css/adminlte.min.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <style>
  /* Social Feed Simple Styles */
  .social-feed-card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 1.5rem;
  }
  .social-feed-card .card-header,
  .social-feed-card .card-body,
  .social-feed-card .card-footer {
    padding: 1rem;
  }

  .post-author-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .post-author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
  }
  .post-author-name a {
    color: inherit;
    text-decoration: none;
    font-weight: 600;
  }
  .post-meta-info {
    font-size: 0.8rem;
    color: #6c757d;
  }

  .post-game-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    background-color: #e9ecef;
    color: #495057;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
  }

  .post-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  .post-detail {
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 0.75rem;
  }

  .post-price-tag {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    background-color: #198754;
    color: #fff;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
  }

  .post-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
  }
  .post-status-badge.available {
    background-color: #d1e7dd;
    color: #0f5132;
  }
  .post-status-badge.sold {
    background-color: #f8d7da;
    color: #842029;
  }

  .post-image-container {
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
    max-height: 300px;
  }
  .post-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .post-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  .feed-header h5 {
    margin: 0;
    font-weight: 600;
  }

  .empty-feed {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
  }
  .empty-feed i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    display: block;
  }

  .post-owner-actions {
    display: flex;
    gap: 4px;
  }
</style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a
                class="nav-link"
                data-lte-toggle="sidebar"
                href="#"
                role="button"
                aria-label="Toggle sidebar"
              >
                <i class="bi bi-list"></i>
              </a>
            </li>

            
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::Navbar Search-->

          </form>
          <!--end::Navbar Search-->

          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Search (small screens: the field above is hidden, so link to the search page)-->
            <li class="nav-item d-md-none">
              <a class="nav-link" href="./pages/search-results.html" aria-label="Search">
                <i class="bi bi-search" aria-hidden="true"></i>
              </a>
            </li>
            <!--end::Search-->
            <!--begin::Messages Dropdown Menu-->
            

            

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                
                <span class="d-none d-md-inline"><?= htmlspecialchars($current_username) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                
                <!--end::User Image-->
                <!--begin::Menu Body-->
                
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  
                  <a href="logout.php" class="btn btn-outline-danger float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php include 'include/aside.php' ;?>      <!--end::Sidebar-->
      <!--begin::App Main-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">

            <!-- Chat Box UI -->
            <!-- Chat Box UI -->
            <?php if ($room_id == 0): ?>
                <!-- กรณีที่กดเมนูแชทซ้ายมือ (ไม่ระบุห้อง) ให้แสดงรายการแชททั้งหมด -->
                <div class="card shadow-sm mt-3" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-chat-left-dots-fill me-2"></i>รายการแชททั้งหมดของคุณ</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($chat_rooms_list)): ?>
                            <div class="text-center text-secondary py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                ยังไม่มีประวัติการพูดคุย
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($chat_rooms_list as $room): ?>
                                    <?php 
                                        // เช็คว่าเราคุยกับใคร (ถ้าเราเป็นผู้ซื้อ คู่สนทนาคือผู้ขาย และในทางกลับกัน)
                                        $partner_name = ($room['user_id'] == $current_user_id) ? $room['seller_name'] : $room['buyer_name'];
                                    ?>
                                    <a href="chat1.php?room_id=<?= $room['room_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($partner_name) ?></h6>
                                            <small class="text-secondary">สินค้า: <?= htmlspecialchars($room['post_title'] ?? 'ไม่มีข้อมูลสินค้า') ?></small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-3 py-2">เข้าสู่ห้องแชท</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- กรณีมี room_id ให้แสดงกล่องแชทเดิม -->
                <div class="card shadow-sm mt-3" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>ห้องแชท (Room #<?= $room_id ?>)</h5>
                        <a href="chat1.php" class="btn btn-sm btn-light">กลับหน้ารวมแชท</a>
                    </div>
                    <div class="card-body overflow-auto" style="height: 400px; background-color: #f8f9fa;">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-secondary mt-5">ยังไม่มีข้อความ เริ่มต้นทักทายเลย!</div>
                        <?php endif; ?>
                        
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
                        <form method="POST" action="chat1.php?room_id=<?= $room_id ?>">
                            <div class="input-group">
                                <input type="text" name="message_text" class="form-control" placeholder="พิมพ์ข้อความที่นี่..." required>
                                <button type="submit" name="send_message" class="btn btn-primary"><i class="bi bi-send-fill"></i> ส่ง</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <!-- End Chat Box UI -->

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
      </main>
      <!--end::App Main-->

      <!--begin::Create Post Modal-->
      <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form method="POST" action="index.php" enctype="multipart/form-data">
              <input type="hidden" name="action" value="create_post">
              <div class="modal-header">
                <h5 class="modal-title" id="createPostModalLabel">
                  <i class="bi bi-plus-circle me-2"></i>สร้างโพสต์ขายไอดีเกม
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="create_game_id" class="form-label">เกม <span class="text-danger">*</span></label>
                    <select class="form-select" id="create_game_id" name="game_id" required>
                      <option value="">-- เลือกเกม --</option>
                      <?php foreach ($games_list as $game): ?>
                        <option value="<?= $game['game_id'] ?>"><?= htmlspecialchars($game['game_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="create_post_price" class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="create_post_price" name="post_price" placeholder="เช่น 500.00" required>
                  </div>
                  <div class="col-12">
                    <label for="create_post_title" class="form-label">หัวข้อโพสต์ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="create_post_title" name="post_title" placeholder="เช่น ขายไอดี Genshin Impact AR 60" required>
                  </div>
                  <div class="col-12">
                    <label for="create_post_detail" class="form-label">รายละเอียดเพิ่มเติม</label>
                    <textarea class="form-control" id="create_post_detail" name="post_detail" rows="4" placeholder="อธิบายรายละเอียดไอดีเกมที่จะขาย..."></textarea>
                  </div>
                  <div class="col-12">
                    <label for="create_post_img" class="form-label">รูปภาพ</label>
                    <input type="file" class="form-control" id="create_post_img" name="post_img" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">รองรับไฟล์ JPG, PNG, GIF, WebP ขนาดไม่เกิน 5MB</div>
                    <div id="createImgPreview" class="mt-2" style="display:none;">
                      <img id="createImgPreviewImg" src="" alt="Preview" style="max-height:180px;border-radius:8px;" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i>ยกเลิก
                </button>
                <button type="submit" class="btn btn-create-post">
                  <i class="bi bi-check-lg me-1"></i>ลงประกาศขาย
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!--end::Create Post Modal-->

      <!--begin::Edit Post Modal-->
      <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form method="POST" action="index.php" enctype="multipart/form-data">
              <input type="hidden" name="action" value="edit_post">
              <input type="hidden" name="edit_post_id" id="edit_post_id">
              <div class="modal-header">
                <h5 class="modal-title" id="editPostModalLabel">
                  <i class="bi bi-pencil-square me-2"></i>แก้ไขโพสต์
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="edit_game_id" class="form-label">เกม <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_game_id" name="edit_game_id" required>
                      <option value="">-- เลือกเกม --</option>
                      <?php foreach ($games_list as $game): ?>
                        <option value="<?= $game['game_id'] ?>"><?= htmlspecialchars($game['game_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="edit_post_price" class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="edit_post_price" name="edit_post_price" required>
                  </div>
                  <div class="col-12">
                    <label for="edit_post_title" class="form-label">หัวข้อโพสต์ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_post_title" name="edit_post_title" required>
                  </div>
                  <div class="col-12">
                    <label for="edit_post_detail" class="form-label">รายละเอียดเพิ่มเติม</label>
                    <textarea class="form-control" id="edit_post_detail" name="edit_post_detail" rows="4"></textarea>
                  </div>
                  <div class="col-md-6">
                    <label for="edit_post_img" class="form-label">เปลี่ยนรูปภาพ</label>
                    <input type="file" class="form-control" id="edit_post_img" name="edit_post_img" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">เลือกไฟล์ใหม่เพื่อเปลี่ยนรูป (ถ้าไม่เลือกจะคงรูปเดิม)</div>
                    <div id="editImgPreview" class="mt-2" style="display:none;">
                      <small class="text-secondary d-block mb-1">รูปภาพปัจจุบัน:</small>
                      <img id="editImgPreviewImg" src="" alt="Current image" style="max-height:120px;border-radius:8px;" class="img-fluid">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="edit_status" class="form-label">สถานะ</label>
                    <select class="form-select" id="edit_status" name="edit_status">
                      <option value="availble">พร้อมขาย</option>
                      <option value="sold">ขายแล้ว</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg me-1"></i>ยกเลิก
                </button>
                <button type="submit" class="btn btn-warning">
                  <i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!--end::Edit Post Modal-->

      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2014-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0/js/adminlte.min.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Edit Post Modal Script-->
    <script>
      // Populate the edit modal with post data when the edit button is clicked
      document.addEventListener('DOMContentLoaded', function () {
        const editPostModal = document.getElementById('editPostModal');
        if (editPostModal) {
          editPostModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            document.getElementById('edit_post_id').value = button.getAttribute('data-post-id') || '';
            document.getElementById('edit_game_id').value = button.getAttribute('data-game-id') || '';
            document.getElementById('edit_post_title').value = button.getAttribute('data-post-title') || '';
            document.getElementById('edit_post_detail').value = button.getAttribute('data-post-detail') || '';
            document.getElementById('edit_post_price').value = button.getAttribute('data-post-price') || '';
            document.getElementById('edit_status').value = button.getAttribute('data-post-status') || 'availble';

            
            const currentImg = button.getAttribute('data-post-img') || '';
            const editPreview = document.getElementById('editImgPreview');
            const editPreviewImg = document.getElementById('editImgPreviewImg');
            if (currentImg) {
              editPreviewImg.src = currentImg;
              editPreview.style.display = 'block';
            } else {
              editPreviewImg.src = '';
              editPreview.style.display = 'none';
            }

            
            document.getElementById('edit_post_img').value = '';
          });
        }

  
        const createImgInput = document.getElementById('create_post_img');
        if (createImgInput) {
          createImgInput.addEventListener('change', function () {
            const preview = document.getElementById('createImgPreview');
            const previewImg = document.getElementById('createImgPreviewImg');
            if (this.files && this.files[0]) {
              const reader = new FileReader();
              reader.onload = function (e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
              };
              reader.readAsDataURL(this.files[0]);
            } else {
              previewImg.src = '';
              preview.style.display = 'none';
            }
          });
        }

        
        const editImgInput = document.getElementById('edit_post_img');
        if (editImgInput) {
          editImgInput.addEventListener('change', function () {
            const preview = document.getElementById('editImgPreview');
            const previewImg = document.getElementById('editImgPreviewImg');
            if (this.files && this.files[0]) {
              const reader = new FileReader();
              reader.onload = function (e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
              };
              reader.readAsDataURL(this.files[0]);
            } else {
              previewImg.src = '';
              preview.style.display = 'none';
            }
          });
        }

        
        const createPostModal = document.getElementById('createPostModal');
        if (createPostModal) {
          createPostModal.addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
            document.getElementById('createImgPreview').style.display = 'none';
            document.getElementById('createImgPreviewImg').src = '';
          });
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function (alert) {
          setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
          }, 5000);
        });
      });
    </script>
    <!--end::Edit Post Modal Script-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
