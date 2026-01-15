<?php
require_once __DIR__ . '/db.php';

function loginUser($username, $password)
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function checkSessionTimeout()
{
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logout();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit;
    }
    if (!checkSessionTimeout()) {
        header('Location: /index.php?timeout=1');
        exit;
    }
}

function requireRole($roles = [])
{
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        http_response_code(403);
        echo 'Forbidden: insufficient privileges';
        exit;
    }
}

function logout()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
