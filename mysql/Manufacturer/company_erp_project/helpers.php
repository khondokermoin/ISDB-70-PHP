<?php
session_start();

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash(): array
{
    $message = $_SESSION['flash_message'] ?? '';
    $type = $_SESSION['flash_type'] ?? '';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return [$message, $type];
}

function redirect_to(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function generate_invoice_no(): string
{
    return 'INV-' . date('Ymd-His') . '-' . random_int(100, 999);
}

function format_money($amount): string
{
    return number_format((float)$amount, 2);
}
