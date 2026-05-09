<?php
require_once __DIR__ . '/config.php';

function startSession()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(string $redirect = '/isp_app/index.php')
{
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireRole(string $role)
{
    requireLogin();
    startSession();
    if ($_SESSION['role_name'] !== $role && $_SESSION['role_name'] !== 'admin') {
        header("Location: " . APP_URL . "/index.php?error=unauthorized");
        exit;
    }
}

function currentUser(): array
{
    startSession();
    return [
        'user_id'   => $_SESSION['user_id']   ?? null,
        'full_name' => $_SESSION['full_name']  ?? '',
        'email'     => $_SESSION['email']      ?? '',
        'role_name' => $_SESSION['role_name']  ?? '',
        'role_id'   => $_SESSION['role_id']    ?? null,
    ];
}

function login(string $email, string $password): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id = u.role_id WHERE u.email = ? AND u.status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['role_id']   = $user['role_id'];
    return ['success' => true, 'role' => $user['role_name']];
}

function logout()
{
    startSession();
    session_destroy();
    header("Location: " . APP_URL . "/index.php");
    exit;
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}
