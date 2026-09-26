<?php
/** Shared session and access-control helpers. */

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
}

function require_login(): void
{
    start_app_session();

    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}