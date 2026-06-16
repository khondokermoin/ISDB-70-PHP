<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="index.html" aria-label="adminHMD dashboard">
            <span class="brand-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">adminHMD</span>
                <span class="brand-subtitle">Admin Template</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <a class="nav-link " href="{{ url('dashboard') }}" aria-current="page">
            <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a class="nav-link " href="{{ url('students') }}">
            <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <span class="nav-text">Students</span>
        </a>
        ''
    </nav>

    <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg"
            alt="Admin Hasan">
        <strong>Admin Hasan</strong>
        <small>Active Workspace</small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">System running smoothly</span>
    </div>
</aside>
