<?php
// Get current page for active state highlighting
$current_page = basename($_SERVER['PHP_SELF']);
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
        width: 240px;
        min-width: 240px;
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
        padding: 28px 20px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
    }

    .sidebar-brand .logo-text {
        font-family: 'Space Mono', monospace;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .sidebar-brand .logo-sub {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .sidebar-nav {
        padding: 16px 12px;
        flex: 1;
    }

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
        font-size: 14px;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
    }

    .nav-item:hover {
        background: rgba(255,255,255,0.06);
        color: #e2e8f0;
    }

    .nav-item.active {
        background: #3b82f6;
        color: #ffffff;
    }

    .nav-item .nav-icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        opacity: 0.9;
    }

    .sidebar-footer {
        padding: 16px 20px;
        border-top: 1px solid rgba(255,255,255,0.07);
        font-size: 12px;
        color: #475569;
    }

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
        <div class="logo-text">ProductHub</div>
        <div class="logo-sub">Admin Panel</div>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m8-8H4"/>
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

    <div class="sidebar-footer">
        &copy; <?= date('Y') ?> ProductHub
    </div>
</div>

<div class="main-content">
