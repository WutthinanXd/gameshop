<?php
require_once __DIR__ . '/config/auth.php';
require_login();
require_once __DIR__ . '/config/connectDB.php';

$result = mysqli_query($conn, 'SELECT user_id, username, role, created_at FROM tb_users ORDER BY user_id DESC');
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

$roleLabels = [
    'user'     => 'ผู้ใช้',
    'merchant' => 'ผู้ขาย',
    'admin'    => 'แอดมิน',
];
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE v4 | จัดการผู้ใช้</title>

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
    <meta name="title" content="AdminLTE v4 | จัดการผู้ใช้" />
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
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.11.0/css/OverlayScrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0/css/adminlte.min.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />

    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
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

            <li class="nav-item d-none d-md-block">
            </li>
          </ul>
          <!--end::Start Navbar Links-->

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

            <!--begin::Notifications Dropdown Menu-->
            <!--end::Notifications Dropdown Menu-->

            <!--begin::Language Menu-->
            <!-- Markup only: swapping the locale is the application's job. The docs
           Recipes page shows how to wire this to a real locale switch. -->
            <!--end::Fullscreen Toggle-->

            <!--begin::Color Mode Toggle (#6010)-->
           
            <!--end::Color Mode Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <span class="d-none d-md-inline">admin</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->     
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
     <?php include 'include/aside_admin.php'; ?>
      <!--end::Sidebar-->
      <!--begin::App Main-->
     <main class="container py-4" style="max-width: 960px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">จัดการผู้ใช้</h1>
                <p class="text-secondary mb-0">เพิ่มบัญชีสำหรับเข้าใช้งานระบบ</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary">กลับหน้าหลัก</a>
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
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                <input id="confirm_password" name="confirm_password" type="password" class="form-control" minlength="6" required>
                            </div>
                            <div class="mb-4">
                                <label for="role" class="form-label">บทบาท</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="user">ผู้ใช้ (User)</option>
                                    <option value="merchant">ผู้ขาย (Merchant)</option>
                                    <option value="admin">แอดมิน (Admin)</option>
                                </select>
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
                                <thead><tr><th>ลำดับ</th><th>ชื่อผู้ใช้</th><th>บทบาท</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
                                <tbody>
                                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= (int) $user['user_id'] ?></td>
                                        <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php
                                                $roleBadge = match($user['role']) {
                                                    'admin'    => 'bg-danger',
                                                    'merchant' => 'bg-warning text-dark',
                                                    default    => 'bg-info text-dark',
                                                };
                                            ?>
                                            <span class="badge <?= $roleBadge ?>"><?= htmlspecialchars($roleLabels[$user['role']] ?? $user['role'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($user['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-nowrap">
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-user"
                                                data-user-id="<?= (int) $user['user_id'] ?>"
                                                data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>"
                                            >แก้ไข</button>
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

      <!--begin::Edit User Modal-->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form action="update_user.php" method="post">
              <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">
                  <label for="edit_username" class="form-label">ชื่อผู้ใช้</label>
                  <input id="edit_username" name="username" type="text" class="form-control" maxlength="100" required>
                </div>
                <div class="mb-3">
                  <label for="edit_role" class="form-label">บทบาท</label>
                  <select id="edit_role" name="role" class="form-select" required>
                    <option value="user">ผู้ใช้ (User)</option>
                    <option value="merchant">ผู้ขาย (Merchant)</option>
                    <option value="admin">แอดมิน (Admin)</option>
                  </select>
                </div>
                <hr>
                <p class="text-secondary mb-2"><small>เว้นว่างหากไม่ต้องการเปลี่ยนรหัสผ่าน</small></p>
                <div class="mb-3">
                  <label for="edit_password" class="form-label">รหัสผ่านใหม่</label>
                  <input id="edit_password" name="password" type="password" class="form-control" minlength="6">
                  <div class="form-text">อย่างน้อย 6 ตัวอักษร</div>
                </div>
                <div class="mb-3">
                  <label for="edit_confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                  <input id="edit_confirm_password" name="confirm_password" type="password" class="form-control" minlength="6">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!--end::Edit User Modal-->

      <!--end::App Main-->
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
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
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

    <!--begin::Edit User Modal Script-->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const editModal = document.getElementById('editUserModal');
        const editButtons = document.querySelectorAll('.btn-edit-user');

        editButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            document.getElementById('edit_user_id').value = this.dataset.userId;
            document.getElementById('edit_username').value = this.dataset.username;
            document.getElementById('edit_role').value = this.dataset.role;
            // Clear password fields every time the modal opens
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_confirm_password').value = '';

            const modal = new bootstrap.Modal(editModal);
            modal.show();
          });
        });
      });
    </script>
    <!--end::Edit User Modal Script-->

    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>