<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/connectDB.php';

start_app_session();
require_login();

$current_user_id = $_SESSION['user_id'] ?? 0;
$current_username = $_SESSION['username'] ?? 'User';

$role = 'user';
$user_stmt = mysqli_prepare($conn, "SELECT role FROM tb_users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($user_stmt, "i", $current_user_id);
mysqli_stmt_execute($user_stmt);
$user_res = mysqli_stmt_get_result($user_stmt);
if ($u = mysqli_fetch_assoc($user_res)) {
    $role = $u['role'];
}

if ($role !== 'admin') {
    header('Location: index.php');
    exit;
}

$msg_success = '';
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    $game_id    = (int)($_POST['game_id'] ?? 0);
    $post_title = trim($_POST['post_title'] ?? '');
    $post_price = (float)($_POST['post_price'] ?? 0);
    $post_detail= trim($_POST['post_detail'] ?? '');
    $post_img   = '';

    if (isset($_FILES['post_img']) && $_FILES['post_img']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['post_img']['type'];
        $file_size = $_FILES['post_img']['size'];
        $max_size  = 5 * 1024 * 1024; // 5MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $ext = pathinfo($_FILES['post_img']['name'], PATHINFO_EXTENSION);
            $new_filename = 'post_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_dir  = __DIR__ . '/upload/';
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['post_img']['tmp_name'], $upload_path)) {
                $post_img = 'upload/' . $new_filename;
            } else {
                $msg_error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
            }
        } else {
            $msg_error = "รูปภาพต้องเป็นไฟล์ JPG, PNG, GIF หรือ WebP ขนาดไม่เกิน 5MB";
        }
    }

    if ($msg_error === '' && $game_id > 0 && $post_title !== '' && $post_price > 0) {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO tb_posts (seller_id, game_id, post_title, post_detail, post_price, post_img, status) VALUES (?, ?, ?, ?, ?, ?, 'availble')");
        mysqli_stmt_bind_param($insert_stmt, "iissds", $current_user_id, $game_id, $post_title, $post_detail, $post_price, $post_img);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $msg_success = "ลงประกาศขายไอดีเกมเรียบร้อยแล้ว!";
        } else {
            $msg_error = "เกิดข้อผิดพลาดในการลงประกาศ";
        }
    } else if ($msg_error === '') {
        $msg_error = "กรุณากรอกข้อมูลสำคัญให้ครบถ้วน";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_post') {
    $edit_post_id = (int)($_POST['edit_post_id'] ?? 0);
    
    $check_stmt = mysqli_prepare($conn, "SELECT seller_id, post_img FROM tb_posts WHERE post_id = ? LIMIT 1");
    mysqli_stmt_bind_param($check_stmt, "i", $edit_post_id);
    mysqli_stmt_execute($check_stmt);
    $check_res = mysqli_stmt_get_result($check_stmt);
    $post_owner = mysqli_fetch_assoc($check_res);
    
    if ($post_owner) { 
        $edit_game_id    = (int)($_POST['edit_game_id'] ?? 0);
        $edit_post_title = trim($_POST['edit_post_title'] ?? '');
        $edit_post_price = (float)($_POST['edit_post_price'] ?? 0);
        $edit_post_detail= trim($_POST['edit_post_detail'] ?? '');
        $edit_post_img   = $post_owner['post_img']; 
        $edit_status     = trim($_POST['edit_status'] ?? 'availble');

        if (isset($_FILES['edit_post_img']) && $_FILES['edit_post_img']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['edit_post_img']['type'];
            $file_size = $_FILES['edit_post_img']['size'];
            $max_size  = 5 * 1024 * 1024; // 5MB

            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $ext = pathinfo($_FILES['edit_post_img']['name'], PATHINFO_EXTENSION);
                $new_filename = 'post_' . time() . '_' . uniqid() . '.' . $ext;
                $upload_dir  = __DIR__ . '/upload/';
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['edit_post_img']['tmp_name'], $upload_path)) {
                    if (!empty($post_owner['post_img']) && file_exists(__DIR__ . '/' . $post_owner['post_img'])) {
                        unlink(__DIR__ . '/' . $post_owner['post_img']);
                    }
                    $edit_post_img = 'upload/' . $new_filename;
                } else {
                    $msg_error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
                }
            } else {
                $msg_error = "รูปภาพต้องเป็นไฟล์ JPG, PNG, GIF หรือ WebP ขนาดไม่เกิน 5MB";
            }
        }

        if ($msg_error === '' && $edit_game_id > 0 && $edit_post_title !== '' && $edit_post_price > 0) {
            $update_stmt = mysqli_prepare($conn, "UPDATE tb_posts SET game_id = ?, post_title = ?, post_detail = ?, post_price = ?, post_img = ?, status = ? WHERE post_id = ?");
            mysqli_stmt_bind_param($update_stmt, "issdssi", $edit_game_id, $edit_post_title, $edit_post_detail, $edit_post_price, $edit_post_img, $edit_status, $edit_post_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $msg_success = "แก้ไขโพสต์เรียบร้อยแล้ว!";
            } else {
                $msg_error = "เกิดข้อผิดพลาดในการแก้ไขโพสต์";
            }
        } else if ($msg_error === '') {
            $msg_error = "กรุณากรอกข้อมูลสำคัญให้ครบถ้วน";
        }
    } else {
        $msg_error = "ไม่พบโพสต์นี้";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    $del_post_id = (int)($_POST['delete_post_id'] ?? 0);
    
    $check_stmt = mysqli_prepare($conn, "SELECT seller_id FROM tb_posts WHERE post_id = ? LIMIT 1");
    mysqli_stmt_bind_param($check_stmt, "i", $del_post_id);
    mysqli_stmt_execute($check_stmt);
    $check_res = mysqli_stmt_get_result($check_stmt);
    $post_owner = mysqli_fetch_assoc($check_res);
    
    if ($post_owner) { 
        $del_stmt = mysqli_prepare($conn, "DELETE FROM tb_posts WHERE post_id = ?");
        mysqli_stmt_bind_param($del_stmt, "i", $del_post_id);
        
        if (mysqli_stmt_execute($del_stmt)) {
            $msg_success = "ลบโพสต์เรียบร้อยแล้ว (จัดการโดยแอดมิน)!";
        } else {
            $msg_error = "เกิดข้อผิดพลาดในการลบโพสต์";
        }
    } else {
        $msg_error = "ไม่พบโพสต์ที่ต้องการลบ";
    }
}

$games_list = [];
$games_query = mysqli_query($conn, "SELECT game_id, game_name FROM tb_games ORDER BY game_name ASC");
if ($games_query) {
    while ($g = mysqli_fetch_assoc($games_query)) {
        $games_list[] = $g;
    }
}

$sql_posts = "SELECT p.*, g.game_name, u.username AS seller_name 
              FROM tb_posts p
              JOIN tb_games g ON p.game_id = g.game_id
              JOIN tb_users u ON p.seller_id = u.user_id
              ORDER BY p.post_id DESC";
$posts_result = mysqli_query($conn, $sql_posts);
?>

<!doctype html>
<html lang="th">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin Dashboard | GameShop</title>

    <!--begin::Theme Init-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
        }
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
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    
    <!--begin::Fonts & Plugins-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0/css/adminlte.min.css" />
    <!--end::Fonts & Plugins-->

    <style>
      /* Social Feed Simple Styles (Match index.php) */
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
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <span class="d-none d-md-inline"><?= htmlspecialchars($current_username) ?> (Admin)</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-footer">
                  <a href="logout.php" class="btn btn-outline-danger float-end">Sign out</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!--end::Header-->
      
      <!--begin::Sidebar-->
      <?php include 'include/aside_admin.php'; ?>
      <!--end::Sidebar-->      
      
      <!--begin::App Main-->
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">

            <!-- Alert Messages -->
            <?php if ($msg_success): ?>
              <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>
            <?php if ($msg_error): ?>
              <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <!--begin::Feed Header-->
            <div class="feed-header mt-4">
              <h5><i class="bi bi-rss me-2"></i>ระบบจัดการโพสต์ทั้งหมด (Admin)</h5>
              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                สร้างโพสต์ในฐานะแอดมิน
              </button>
            </div>
            <!--end::Feed Header-->

            <!--begin::Posts Feed-->
            <div class="row">
              <div class="col-lg-8 col-xl-7">

                <?php if ($posts_result && mysqli_num_rows($posts_result) > 0): ?>
                  <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                    <div class="card social-feed-card">
                      <!--begin::Card Header-->
                      <div class="card-header d-flex justify-content-between align-items-start">
                        <div class="post-author-info">
                          <div class="post-author-avatar">
                            <?= strtoupper(mb_substr($post['seller_name'], 0, 1)) ?>
                          </div>
                          <div>
                            <div class="post-author-name">
                              <a href="#"><?= htmlspecialchars($post['seller_name']) ?></a>
                            </div>
                            <div class="post-meta-info">
                              Post ID: #<?= $post['post_id'] ?> | Seller ID: <?= $post['seller_id'] ?>
                            </div>
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <span class="post-status-badge <?= $post['status'] === 'availble' ? 'available' : 'sold' ?>">
                            <i class="bi <?= $post['status'] === 'availble' ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                            <?= $post['status'] === 'availble' ? 'พร้อมขาย' : 'ขายแล้ว' ?>
                          </span>
                          <!-- Admin มีสิทธิ์แก้ไข/ลบทุกโพสต์ -->
                          <div class="post-owner-actions">
                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-post"
                              data-bs-toggle="modal" 
                              data-bs-target="#editPostModal"
                              data-post-id="<?= $post['post_id'] ?>"
                              data-game-id="<?= $post['game_id'] ?>"
                              data-post-title="<?= htmlspecialchars($post['post_title'], ENT_QUOTES) ?>"
                              data-post-detail="<?= htmlspecialchars($post['post_detail'], ENT_QUOTES) ?>"
                              data-post-price="<?= $post['post_price'] ?>"
                              data-post-img="<?= htmlspecialchars($post['post_img'], ENT_QUOTES) ?>"
                              data-post-status="<?= $post['status'] ?>"
                              title="จัดการแก้ไข"
                            >
                              <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('ในฐานะแอดมิน: คุณแน่ใจหรือไม่ที่จะลบโพสต์นี้แบบถาวร?');">
                              <input type="hidden" name="action" value="delete_post">
                              <input type="hidden" name="delete_post_id" value="<?= $post['post_id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบโพสต์">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                      <!--end::Card Header-->

                      <!--begin::Card Body-->
                      <div class="card-body">
                        <?php if (!empty($post['post_img'])): ?>
                          <div class="post-image-container">
                            <img src="<?= htmlspecialchars($post['post_img']) ?>" alt="<?= htmlspecialchars($post['post_title']) ?>" class="img-fluid" />
                          </div>
                        <?php endif; ?>

                        <div class="post-game-badge">
                          <i class="bi bi-controller"></i> <?= htmlspecialchars($post['game_name']) ?>
                        </div>

                        <div class="post-title"><?= htmlspecialchars($post['post_title']) ?></div>

                        <?php if (!empty($post['post_detail'])): ?>
                          <p class="post-detail"><?= nl2br(htmlspecialchars($post['post_detail'])) ?></p>
                        <?php endif; ?>

                        <div class="post-price-tag">
                          <i class="bi bi-tag-fill"></i> ฿<?= number_format($post['post_price'], 2) ?>
                        </div>
                        
                      </div>
                      <!--end::Card Body-->
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <div class="card social-feed-card">
                    <div class="card-body empty-feed">
                      <i class="bi bi-inbox"></i>
                      <h5>ยังไม่มีโพสต์ในระบบ</h5>
                    </div>
                  </div>
                <?php endif; ?>

              </div>
            </div>
            <!--end::Posts Feed-->

          </div>
        </div>
      </main>
      <!--end::App Main-->

      <!--begin::Create Post Modal-->
      <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
              <input type="hidden" name="action" value="create_post">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>แอดมินสร้างโพสต์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">เกม <span class="text-danger">*</span></label>
                    <select class="form-select" name="game_id" required>
                      <option value="">-- เลือกเกม --</option>
                      <?php foreach ($games_list as $game): ?>
                        <option value="<?= $game['game_id'] ?>"><?= htmlspecialchars($game['game_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="post_price" placeholder="เช่น 500.00" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">หัวข้อโพสต์ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="post_title" placeholder="เช่น ขายไอดี Genshin Impact AR 60" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">รายละเอียดเพิ่มเติม</label>
                    <textarea class="form-control" name="post_detail" rows="4" placeholder="อธิบายรายละเอียดไอดีเกมที่จะขาย..."></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">รูปภาพ</label>
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
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-lg me-1"></i>ลงประกาศ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!--end::Create Post Modal-->

      <!--begin::Edit Post Modal-->
      <div class="modal fade" id="editPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
              <input type="hidden" name="action" value="edit_post">
              <input type="hidden" name="edit_post_id" id="edit_post_id">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>แอดมินแก้ไขโพสต์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">เกม <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_game_id" name="edit_game_id" required>
                      <option value="">-- เลือกเกม --</option>
                      <?php foreach ($games_list as $game): ?>
                        <option value="<?= $game['game_id'] ?>"><?= htmlspecialchars($game['game_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="edit_post_price" name="edit_post_price" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">หัวข้อโพสต์ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_post_title" name="edit_post_title" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label">รายละเอียดเพิ่มเติม</label>
                    <textarea class="form-control" id="edit_post_detail" name="edit_post_detail" rows="4"></textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">เปลี่ยนรูปภาพ</label>
                    <input type="file" class="form-control" id="edit_post_img" name="edit_post_img" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">เลือกไฟล์ใหม่เพื่อเปลี่ยนรูป (ถ้าไม่เลือกจะคงรูปเดิม)</div>
                    <div id="editImgPreview" class="mt-2" style="display:none;">
                      <small class="text-secondary d-block mb-1">รูปภาพปัจจุบัน:</small>
                      <img id="editImgPreviewImg" src="" alt="Current image" style="max-height:120px;border-radius:8px;" class="img-fluid">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">สถานะ</label>
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
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>
          Copyright &copy; 2014-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0/js/adminlte.min.js"></script>
    
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }

        // Edit Modal Data Binding
        const editPostModal = document.getElementById('editPostModal');
        if (editPostModal) {
          editPostModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
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
              editPreview.style.display = 'none';
            }
            document.getElementById('edit_post_img').value = '';
          });
        }

        // Image Preview Handler
        function handleImagePreview(inputId, previewBoxId, previewImgId) {
          const input = document.getElementById(inputId);
          if (input) {
            input.addEventListener('change', function () {
              const preview = document.getElementById(previewBoxId);
              const img = document.getElementById(previewImgId);
              if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                  img.src = e.target.result;
                  preview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
              } else {
                preview.style.display = 'none';
              }
            });
          }
        }
        handleImagePreview('create_post_img', 'createImgPreview', 'createImgPreviewImg');
        handleImagePreview('edit_post_img', 'editImgPreview', 'editImgPreviewImg');

        // Dismiss alerts automatically
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function (alert) {
          setTimeout(function () { bootstrap.Alert.getOrCreateInstance(alert).close(); }, 5000);
        });
        
        // Reset form on modal close
        const createPostModal = document.getElementById('createPostModal');
        if (createPostModal) {
          createPostModal.addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
            document.getElementById('createImgPreview').style.display = 'none';
            document.getElementById('createImgPreviewImg').src = '';
          });
        }
      });
    </script>
    <!--end::Script-->
  </body>
</html>