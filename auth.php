<?php
session_start();

const ADMIN_USERNAME = 'Adminuser';
const ADMIN_PASSWORD_HASH = '$2y$12$MOOdwetLmOEveDjCAbDRI.uxEBGzpLFle89gkLdQqSc9uNR5Tyy1S';

function isAdminLoggedIn() {
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdminApi() {
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function attemptAdminLogin(string $username, string $password): bool {
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }

    return false;
}

function logoutAdmin() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
