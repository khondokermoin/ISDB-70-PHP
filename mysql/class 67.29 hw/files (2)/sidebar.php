<?php
// ── Session guard: every page that includes sidebar.php is protected ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$username     = $_SESSION['username'];

// Deterministic avatar color from username
$colors = ['#3b82f6','#c8522a','#2ac87e','#c82a8e','#8e2ac8','#c8a52a','#0ea5e9'];
$avatarColor   = $colors[abs(crc32($username)) % count($colors)];
$avatarInitial = strtoupper(mb_substr($username, 0, 1));
$avatarUrl     = 'https://api.dicebear.com/7.x/thumbs/svg?seed=' . urlencode($username) . '&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf';
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap');

    * { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        display: flex;
        min-height: 100vh;
    }

    /* ── SIDEBAR ── */
    .sidebar {
        width: 248px;
        min-width: 248px;
        background: #0f172a;
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        z-index: 100;
    }

    .sidebar-brand {
        padding: 26px 20px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand-icon {
        width: 34px; height: 34px;
        background: #3b82f6;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .brand-icon svg { width: 18px; height: 18px; }

    .brand-labels .logo-text {
        font-family: 'Space Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
    }

    .brand-labels .logo-sub {
        font-size: 10px;
        color: #475569;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .sidebar-nav { padding: 14px 12px; flex: 1; }

    .nav-section-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #475569;
        padding: 8px 8px 6px;
        margin-top: 8px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
    }

    .nav-item:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
    .nav-item.active { background: #3b82f6; color: #ffffff; }

    .nav-item .nav-icon { width: 17px; height: 17px; flex-shrink: 0; }

    /* ── USER FOOTER ── */
    .sidebar-user {
        padding: 14px 16px;
        border-top: 1px solid rgba(255,255,255,0.07);
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 10px;
        border-radius: 10px;
        background: rgba(255,255,255,0.04);
        margin-bottom: 10px;
    }

    .user-avatar-img {
        width: 38px; height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255,255,255,0.12);
        flex-shrink: 0;
    }

    .user-avatar-fallback {
        width: 38px; height: 38px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        color: white;
        flex-shrink: 0;
        border: 2px solid rgba(255,255,255,0.12);
    }

    .user-meta { min-width: 0; }

    .user-name {
        font-size: 13px;
        font-weight: 600;
        color: #f1f5f9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 130px;
    }

    .user-status {
        font-size: 11px;
        color: #22c55e;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 1px;
    }

    .user-status::before {
        content: '';
        width: 6px; height: 6px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        padding: 9px;
        background: transparent;
        border: 1px solid rgba(255,255,255,0.10);
        border-radius: 8px;
        color: #64748b;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-logout:hover {
        border-color: rgba(239,68,68,0.5);
        color: #ef4444;
        background: rgba(239,68,68,0.06);
    }

    .btn-logout svg { width: 14px; height: 14px; }

    /* ── MAIN CONTENT WRAPPER ── */
    .main-content {
        flex: 1;
        padding: 32px;
        min-width: 0;
        color: #1e293b;
    }
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="white">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        <div class="brand-labels">
            <div class="logo-text">ProductHub</div>
            <div class="logo-sub">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Catalog</div>

        <a href="view_products.php" class="nav-item <?= ($current_page === 'view_products.php') ? 'active' : '' ?>">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
            All Products
        </a>

        <a href="add_product.php" class="nav-item <?= ($current_page === 'add_product.php') ? 'active' : '' ?>">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>

        <div class="nav-section-label">Brands</div>

        <a href="add_brand.php" class="nav-item <?= ($current_page === 'add_brand.php') ? 'active' : '' ?>">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Add Brand
        </a>
    </nav>

    <!-- USER CARD + LOGOUT -->
    <div class="sidebar-user">
        <div class="user-card">
            <img src="<?= htmlspecialchars($avatarUrl) ?>"
                 class="user-avatar-img"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                 alt="avatar">
            <div class="user-avatar-fallback" style="background:<?= $avatarColor ?>;">
                <?= $avatarInitial ?>
            </div>
            <div class="user-meta">
                <div class="user-name"><?= htmlspecialchars($username) ?></div>
                <div class="user-status">Online</div>
            </div>
        </div>

        <a href="logout.php" class="btn-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sign out
        </a>
    </div>
</div>

<div class="main-content">
