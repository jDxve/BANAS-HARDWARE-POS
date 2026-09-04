<?php

function current_user_id(): ?string
{
    return $_SESSION['user_id'] ?? null;
}

function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

function is_admin(): bool
{
    return current_user_role() === 'admin';
}

function require_login(string $redirectTo = 'login.php'): void
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirectTo");
        exit();
    }
}

function require_admin(string $redirectTo = 'login.php'): void
{
    require_login($redirectTo);
    if (!is_admin()) {
        header("Location: $redirectTo");
        exit();
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}
