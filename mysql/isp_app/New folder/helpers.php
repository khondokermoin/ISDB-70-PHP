<?php
function fmt_currency(float $amount): string
{
    return '৳' . number_format($amount, 2);
}
function fmt_date(?string $d): string
{
    return $d ? date('d M Y', strtotime($d)) : '—';
}
function status_badge(string $status): string
{
    $map = [
        'active'      => 'badge-success',
        'paid'        => 'badge-success',
        'online'      => 'badge-success',
        'resolved'    => 'badge-success',
        'inactive'    => 'badge-neutral',
        'expired'     => 'badge-neutral',
        'closed'      => 'badge-neutral',
        'offline'     => 'badge-neutral',
        'unpaid'      => 'badge-warning',
        'in_progress' => 'badge-warning',
        'open'        => 'badge-info',
        'overdue'     => 'badge-danger',
        'suspended'   => 'badge-danger',
        'cancelled'   => 'badge-danger',
    ];
    $cls = $map[strtolower($status)] ?? 'badge-neutral';
    return "<span class=\"badge $cls\">$status</span>";
}
function sanitize(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_verify(): bool
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '');
}
function paginate(int $total, int $page, int $perPage = 15): array
{
    $pages = (int) ceil($total / $perPage);
    return ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => $pages, 'offset' => ($page - 1) * $perPage];
}
