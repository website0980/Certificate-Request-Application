<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

function verifyCsrfToken(string $token): bool
{
    return !empty($_SESSION[CSRF_SESSION_KEY]) && hash_equals($_SESSION[CSRF_SESSION_KEY], $token);
}

function requireCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (!$token && !empty($_SERVER['CONTENT_TYPE']) && str_contains(strtolower($_SERVER['CONTENT_TYPE']), 'application/json')) {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (is_array($data)) {
            $token = trim($data['csrf_token'] ?? '');
        }
    }

    if (!verifyCsrfToken($token)) {
        jsonResponse(['error' => 'Invalid CSRF token.'], 403);
    }
}

function redirect(string $destination): void
{
    header('Location: ' . $destination);
    exit;
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonPayload(): array
{
    static $payload = null;
    if ($payload !== null) {
        return $payload;
    }

    $body = file_get_contents('php://input');
    $decoded = json_decode($body, true);
    return $payload = is_array($decoded) ? $decoded : [];
}

function getRequestData(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains(strtolower($contentType), 'application/json')) {
        return getJsonPayload();
    }

    return $_REQUEST;
}

function getRequestValue(string $key, string $default = ''): string
{
    $data = getRequestData();
    if (!is_array($data)) {
        return $default;
    }

    $value = $data[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function getPostValue(string $key, string $default = ''): string
{
    return trim($_POST[$key] ?? $default);
}
