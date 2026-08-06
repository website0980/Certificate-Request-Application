<?php
require_once __DIR__ . '/common.php';

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        redirect('/admin/login.php');
    }
}

function requireAdminApi(): void
{
    if (!isAdminLoggedIn()) {
        jsonResponse(['error' => 'Authentication required.'], 401);
    }
}

function attemptAdminLogin(string $username, string $password): bool
{
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function logoutAdmin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly'],
            $params['samesite'] ?? ''
        );
    }
    session_destroy();
}
