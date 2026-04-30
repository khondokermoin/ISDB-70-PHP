<?php
session_start();
require_once 'db.php'; // Include your database connection

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];

// Fetch the user's actual data from the database
$stmt = $conn->prepare("SELECT email, profile_pic, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $profile_pic = $row['profile_pic'];
    $role = $row['role'];
} else {
    // Failsafe: User session exists but user is missing from DB
    session_destroy();
    header('Location: index.php');
    exit();
}
$stmt->close();

// Setup Avatar Path
$avatarPath = "uploads/" . (!empty($profile_pic) ? $profile_pic : 'default.jpg');

// Generate a consistent color from the username for the fallback
function getUserColor(string $name): string {
    $colors = ['#c8522a','#2a6fc8','#2ac87e','#c82a8e','#8e2ac8','#c8a52a','#2ac8c8'];
    $idx = abs(crc32($name)) % count($colors);
    return $colors[$idx];
}

$avatarColor = getUserColor($username);
$avatarInitial = strtoupper(mb_substr($username, 0, 1));
// Format the role for display (e.g., "super_admin" -> "Super Admin")
$displayRole = ucwords(str_replace('_', ' ', $role));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= htmlspecialchars($username) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0c0c0e;
            --paper: #faf9f7;
            --accent: #c8522a;
            --muted: #8a8a8e;
            --border: #e4e2de;
            --card: #ffffff;
            --sidebar: #0c0c0e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--paper);
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 230px;
            min-width: 230px;
            background: var(--sidebar);
            display: flex;
            flex-direction: column;
            padding: 32px 20px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            padding: 0 8px;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon svg { width: 17px; height: 17px; }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.25);
            padding: 0 8px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .nav-item:hover  { background: rgba(255,255,255,0.06); color: #fff; }
        .nav-item.active { background: rgba(200,82,42,0.2); color: #e8906e; }

        .nav-item svg { width: 17px; height: 17px; flex-shrink: 0; }

        .sidebar-spacer { flex: 1; }

        /* ── USER CARD IN SIDEBAR ── */
        .sidebar-user {
            border-top: 1px solid rgba(255,255,255,0.07);
            padding-top: 20px;
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 10px;
        }

        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.1);
            background: #333;
            flex-shrink: 0;
        }

        .user-avatar-fallback-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.1);
        }

        .user-info { min-width: 0; }

        .user-name-sm {
            font-size: 13px;
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .user-role {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }

        /* ── MAIN ── */
        .main {
            flex: 1;
            padding: 40px 48px;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--ink);
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: transparent;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            border-color: #e74c3c;
            color: #e74c3c;
            background: #fff5f5;
        }

        .logout-btn svg { width: 15px; height: 15px; }

        /* ── PROFILE CARD ── */
        .profile-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px;
            display: flex;
            align-items: center;
            gap: 28px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #e8906e);
        }

        .avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--paper);
            box-shadow: 0 0 0 2px var(--border);
            display: block;
        }

        .avatar-fallback {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 700;
            color: white;
            border: 3px solid var(--paper);
            box-shadow: 0 0 0 2px var(--border);
        }

        .avatar-online {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 14px;
            height: 14px;
            background: #22c55e;
            border-radius: 50%;
            border: 2.5px solid white;
        }

        .profile-info { flex: 1; }

        .profile-greeting {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .profile-username {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: #eefaf4;
            border: 1px solid #aadec4;
            border-radius: 20px;
            font-size: 12px;
            color: #1a6644;
            font-weight: 600;
        }

        .profile-badge.admin-badge {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #3730a3;
        }

        .profile-badge.admin-badge::before {
            background: #6366f1;
        }

        .profile-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
        }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
        }

        .stat-sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* ── WELCOME BOX ── */
        .welcome-box {
            background: var(--ink);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 28px;
        }

        .welcome-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .welcome-box p {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            line-height: 1.6;
        }

        .btn-goto {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 11px 20px;
            background: var(--accent);
            color: white;
            border-radius: 9px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-goto:hover { background: #b34525; }

        /* ── SUPER ADMIN PANEL ── */
        .admin-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
        }
        
        .admin-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--ink);
            margin-bottom: 20px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th, .admin-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .admin-table th {
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table-avatar {
            width: 38px; 
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="white">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <span class="logo-text">FileVault</span>
    </div>

    <div class="nav-label">Menu</div>

    <a href="dashboard.php" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
    </a>

    <a href="file_uplode.php" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Upload Files
    </a>

    <div class="sidebar-spacer"></div>

    <div class="sidebar-user">
        <div class="user-row">
            <img src="<?= htmlspecialchars($avatarPath) ?>"
                 class="user-avatar-sm"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                 alt="avatar">
            <div class="user-avatar-fallback-sm" style="background:<?= $avatarColor ?>;display:none;">
                <?= $avatarInitial ?>
            </div>
            <div class="user-info">
                <div class="user-name-sm"><?= htmlspecialchars($username) ?></div>
                <div class="user-role"><?= htmlspecialchars($displayRole) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="top-bar">
        <div class="page-title">Dashboard</div>
        <a href="logout.php" class="logout-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sign out
        </a>
    </div>

    <!-- PROFILE CARD -->
    <div class="profile-card">
        <div class="avatar-wrap">
            <img src="<?= htmlspecialchars($avatarPath) ?>"
                 class="profile-avatar"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                 alt="<?= htmlspecialchars($username) ?>">
            <div class="avatar-fallback" style="background:<?= $avatarColor ?>;display:none;">
                <?= $avatarInitial ?>
            </div>
            <div class="avatar-online"></div>
        </div>
        <div class="profile-info">
            <div class="profile-greeting">Good <?= (date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening')) ?>,</div>
            <div class="profile-username"><?= htmlspecialchars($username) ?></div>
            <div class="profile-badge <?= $role === 'super_admin' ? 'admin-badge' : '' ?>">
                <?= htmlspecialchars($displayRole) ?>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Email</div>
            <div class="stat-value" style="font-size:20px;margin-top:4px; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($email) ?></div>
            <div class="stat-sub">Registered address</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Session</div>
            <div class="stat-value" style="color:#22c55e;">●&nbsp;Live</div>
            <div class="stat-sub">Authenticated via session</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Role</div>
            <div class="stat-value" style="font-size:20px;margin-top:4px;"><?= htmlspecialchars($displayRole) ?></div>
            <div class="stat-sub">System privileges</div>
        </div>
    </div>

    <!-- WELCOME -->
    <div class="welcome-box">
        <h3>Ready to manage products?</h3>
        <p>Head over to the upload page to manage your products and brands. Your session is active and secure.</p>
        <a href="file_uplode.php" class="btn-goto">Go to uploads →</a>
    </div>

    <!-- SUPER ADMIN PANEL: ONLY VISIBLE IF ROLE IS 'super_admin' -->
    <?php if ($role === 'super_admin'): ?>
    <div class="admin-section">
        <h3 class="admin-title">User Management (Super Admin Only)</h3>
        <?php
        // Fetch all users to display to the Super Admin
        $usersQuery = "SELECT username, email, profile_pic, role FROM users ORDER BY id DESC";
        $usersResult = $conn->query($usersQuery);

        if ($usersResult && $usersResult->num_rows > 0):
        ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Username</th>
                    <th>Email Address</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $usersResult->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="uploads/<?= htmlspecialchars($u['profile_pic'] ?: 'default.jpg') ?>" 
                             class="admin-table-avatar" 
                             onerror="this.src='uploads/default.jpg'"
                             alt="User Avatar">
                    </td>
                    <td style="font-weight: 500; color: var(--ink);"><?= htmlspecialchars($u['username']) ?></td>
                    <td style="color: var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="profile-badge <?= $u['role'] === 'super_admin' ? 'admin-badge' : '' ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $u['role']))) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color: var(--muted); font-size: 14px;">No users found in the database.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

</body>
</html>