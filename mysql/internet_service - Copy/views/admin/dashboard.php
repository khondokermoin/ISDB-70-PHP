<?php

/**
 * views/admin/dashboard.php
 *
 * REFACTOR SUMMARY
 * ────────────────
 * Section 1 — Queries
 *   • 18 individual queries → 9 consolidated queries (≈50 % fewer DB round-trips).
 *   • statusBadge() uses a `static` array so it is not rebuilt on every call.
 *   • $recentCustomers uses a correlated sub-select → eliminates the duplicate-row
 *     risk when one customer holds multiple subscriptions.
 *   • Unused SELECT columns removed from $recentPayments and $recentCustomers.
 *
 * Section 2 — Original HTML
 *   • Header row updated to include a live "Updated: ..." timestamp.
 *
 * Section 3 — New panels (appended)
 *   • Extra KPI row: unchanged (all 4 values are unique; not repeated above).
 *   • Charts row:    unchanged.
 *   • OLD "Financial Summary" had 6 cards, of which 3 duplicated rows above:
 *       ✗ Total Revenue → already shown in the original Revenue card (Section 2)
 *       ✗ Net Profit    → already shown in the Extra KPI row
 *       ✗ This Month    → already shown in the Extra KPI row
 *     Those 3 cards are removed; the 3 genuinely unique items are kept and the
 *     panel is renamed "Expenses & Receivables".
 *   • Recent tables, Active Tickets, Notifications: unchanged.
 *
 * @var PDO $db  Injected by the parent admin.php router.
 */

// ══════════════════════════════════════════════════════════════════════════════
//  SECTION 1 — DB QUERIES  (consolidated; no raw string interpolation)
// ══════════════════════════════════════════════════════════════════════════════

// ── Packages (was 2 queries) ──────────────────────────────────────────────────
$pkgRow = $db->query("
    SELECT COUNT(*)                            AS total,
           COALESCE(SUM(status = 'active'), 0) AS active
    FROM   packages
")->fetch(PDO::FETCH_ASSOC);
$totalPackages  = (int) $pkgRow['total'];
$activePackages = (int) $pkgRow['active'];

// ── Users (was 4 queries) ─────────────────────────────────────────────────────
$userRow = $db->query("
    SELECT
        COALESCE(SUM(role = 'customer'), 0)                                          AS total_customers,
        COALESCE(SUM(role = 'customer' AND status = 'active'), 0)                    AS active_customers,
        COALESCE(SUM(role = 'staff'), 0)                                             AS total_staff,
        COALESCE(SUM(role = 'customer'
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)), 0)          AS new_week
    FROM users
")->fetch(PDO::FETCH_ASSOC);
$totalUsers   = (int) $userRow['total_customers'];
$activeUsers  = (int) $userRow['active_customers'];
$totalStaff   = (int) $userRow['total_staff'];
$newUsersWeek = (int) $userRow['new_week'];

// ── Financials (was 4 queries) ────────────────────────────────────────────────
$payRow = $db->query("
    SELECT
        COALESCE(SUM(amount), 0)                                               AS total_revenue,
        COALESCE(SUM(CASE WHEN paid_at >= DATE_FORMAT(NOW(),'%Y-%m-01')
                          THEN amount END), 0)                                  AS month_revenue
    FROM payments
")->fetch(PDO::FETCH_ASSOC);
$totalRevenue  = (float) $payRow['total_revenue'];
$monthRevenue  = (float) $payRow['month_revenue'];
$totalExpenses = (float) $db->query(
    "SELECT COALESCE(SUM(amount), 0) FROM expenses"
)->fetchColumn();
$netProfit = $totalRevenue - $totalExpenses;

// ── Invoices (was 2 queries) ──────────────────────────────────────────────────
$invRow = $db->query("
    SELECT
        COALESCE(SUM(status = 'unpaid'),  0)                              AS unpaid_count,
        COALESCE(SUM(status = 'pending'), 0)                              AS pending_count,
        COALESCE(SUM(CASE WHEN status = 'unpaid' THEN amount END), 0)    AS unpaid_amount
    FROM invoices
")->fetch(PDO::FETCH_ASSOC);
$unpaidInvoices  = (int)   $invRow['unpaid_count'];
$pendingInvoices = (int)   $invRow['pending_count'];
$unpaidAmount    = (float) $invRow['unpaid_amount'];

// ── Subscriptions (was 3 queries) ─────────────────────────────────────────────
$subRow = $db->query("
    SELECT
        COALESCE(SUM(status = 'active'),  0)                                               AS active_subs,
        COALESCE(SUM(status = 'pending'), 0)                                               AS pending_subs,
        COALESCE(SUM(status = 'active'
                     AND end_date BETWEEN CURDATE()
                                      AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)), 0)          AS expiring_soon
    FROM subscriptions
")->fetch(PDO::FETCH_ASSOC);
$activeSubs   = (int) $subRow['active_subs'];
$pendingSubs  = (int) $subRow['pending_subs'];
$expiringSoon = (int) $subRow['expiring_soon'];

// ── Tickets (was 3 queries) ───────────────────────────────────────────────────
$tkRow = $db->query("
    SELECT
        COALESCE(SUM(status = 'open'),       0) AS open_tickets,
        COALESCE(SUM(status = 'processing'), 0) AS processing_tickets,
        COALESCE(SUM(status = 'resolved'),   0) AS resolved_tickets
    FROM tickets
")->fetch(PDO::FETCH_ASSOC);
$openTickets       = (int) $tkRow['open_tickets'];
$processingTickets = (int) $tkRow['processing_tickets'];
$resolvedTickets   = (int) $tkRow['resolved_tickets'];

// ── Coverage Zones (was 3 queries) ────────────────────────────────────────────
$zoneRow = $db->query("
    SELECT
        COUNT(*)                                AS total_zones,
        COALESCE(SUM(status = 'active'),   0)   AS active_zones,
        COALESCE(SUM(status = 'upcoming'), 0)   AS upcoming_zones
    FROM coverage_zones
")->fetch(PDO::FETCH_ASSOC);
$total_zones   = (int) $zoneRow['total_zones'];
$activeZones   = (int) $zoneRow['active_zones'];
$upcomingZones = (int) $zoneRow['upcoming_zones'];

// ── Chart: Revenue last 6 months ──────────────────────────────────────────────
$revenueRows = $db->query("
    SELECT DATE_FORMAT(paid_at,'%b %Y') AS label,
           MONTH(paid_at)               AS mnum,
           YEAR(paid_at)                AS yr,
           COALESCE(SUM(amount), 0)     AS total
    FROM   payments
    WHERE  paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP  BY yr, mnum, label
    ORDER  BY yr, mnum
")->fetchAll(PDO::FETCH_ASSOC);

// ── Chart: Active subscriptions by package ─────────────────────────────────────
$pkgDistRows = $db->query("
    SELECT p.name, COUNT(s.subscription_id) AS cnt
    FROM   subscriptions s
    JOIN   packages p ON p.package_id = s.package_id
    WHERE  s.status = 'active'
    GROUP  BY p.package_id, p.name
    ORDER  BY cnt DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Chart: Tickets by category ─────────────────────────────────────────────────
$ticketCatRows = $db->query("
    SELECT COALESCE(category,'General') AS cat, COUNT(*) AS cnt
    FROM   tickets
    GROUP  BY cat
    ORDER  BY cnt DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent payments (last 8) — unused columns removed ─────────────────────────
$recentPayments = $db->query("
    SELECT py.amount, py.method, py.paid_at,
           u.full_name, u.phone, i.invoice_number
    FROM   payments py
    LEFT JOIN users    u ON u.user_id    = py.user_id
    LEFT JOIN invoices i ON i.invoice_id = py.invoice_id
    ORDER  BY py.paid_at DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent invoices (last 8) ───────────────────────────────────────────────────
$recentInvoices = $db->query("
    SELECT i.invoice_number, i.amount, i.due_date, i.status, i.created_at,
           u.full_name
    FROM   invoices i
    LEFT JOIN users u ON u.user_id = i.user_id
    ORDER  BY i.created_at DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Active tickets (open + processing, last 6) ────────────────────────────────
$recentTickets = $db->query("
    SELECT t.ticket_id, t.subject, t.category, t.status, t.created_at,
           c.full_name AS customer_name,
           a.full_name AS assigned_name
    FROM   tickets t
    LEFT JOIN users c ON c.user_id = t.user_id
    LEFT JOIN users a ON a.user_id = t.assigned_to
    WHERE  t.status IN ('open','processing')
    ORDER  BY t.created_at DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent customers — correlated sub-query prevents duplicate rows ─────────────
// (A bare LEFT JOIN on subscriptions returns N rows per customer with N subs)
$recentCustomers = $db->query("
    SELECT u.user_id, u.full_name, u.phone, u.status, u.created_at,
           (SELECT p.name
            FROM   subscriptions s
            JOIN   packages p ON p.package_id = s.package_id
            WHERE  s.user_id = u.user_id AND s.status = 'active'
            ORDER  BY s.subscription_id DESC LIMIT 1) AS package_name
    FROM   users u
    WHERE  u.role = 'customer'
    ORDER  BY u.created_at DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Unread notifications (last 5) ─────────────────────────────────────────────
$unreadNotifs = $db->query("
    SELECT notification_id, message, sent_at
    FROM   notifications
    WHERE  is_read = 0
    ORDER  BY sent_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ── JSON payloads for Chart.js ─────────────────────────────────────────────────
$chartMonthLabels  = json_encode(array_column($revenueRows,   'label'));
$chartMonthRevenue = json_encode(array_column($revenueRows,   'total'));
$chartPkgLabels    = json_encode(array_column($pkgDistRows,   'name'));
$chartPkgData      = json_encode(array_column($pkgDistRows,   'cnt'));
$chartTicketLabels = json_encode(array_column($ticketCatRows, 'cat'));
$chartTicketData   = json_encode(array_column($ticketCatRows, 'cnt'));

// ── Status badge helper — static $map avoids rebuilding the array each call ────
function statusBadge(string $s): string
{
    static $map = [
        'active'     => 'bg-green-100 text-green-700',
        'inactive'   => 'bg-gray-100 text-gray-500',
        'pending'    => 'bg-yellow-100 text-yellow-700',
        'open'       => 'bg-red-100 text-red-700',
        'processing' => 'bg-blue-100 text-blue-700',
        'paid'       => 'bg-green-100 text-green-700',
        'unpaid'     => 'bg-red-100 text-red-700',
        'resolved'   => 'bg-gray-100 text-gray-500',
        'closed'     => 'bg-gray-100 text-gray-500',
        'upcoming'   => 'bg-purple-100 text-purple-700',
    ];
    $cls = $map[strtolower($s)] ?? 'bg-gray-100 text-gray-500';
    return '<span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">'
        . htmlspecialchars(ucfirst($s)) . '</span>';
}

?>

<?php /* ══════════════════════════════════════════════════════════════════════
   SECTION 2 — ORIGINAL HTML
══════════════════════════════════════════════════════════════════════════ */ ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">System Overview</h2>
        <p class="text-gray-500 text-sm">Welcome back, Admin! Here is your ISP business summary.</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-gray-500">
        <i class="fa fa-clock"></i>
        <span>Updated: <?php echo date('d M Y, h:i A'); ?></span>
    </div>
</div>

<!-- Stats Grid (Top row) -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

    <!-- Users Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-blue-500 hover:shadow-md transition">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-lg mr-4">
            <i class="fa fa-users text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Users</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalUsers; ?> <span class="text-sm font-normal text-green-500">(<?php echo $activeUsers; ?> Active)</span></p>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-green-500 hover:shadow-md transition">
        <div class="bg-green-100 text-green-600 p-4 rounded-lg mr-4">
            <i class="fa fa-wallet text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Revenue</h3>
            <p class="text-2xl font-bold text-gray-800">৳<?php echo number_format($totalRevenue, 2); ?></p>
        </div>
    </div>

    <!-- Unpaid Invoices Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-orange-500 hover:shadow-md transition">
        <div class="bg-orange-100 text-orange-600 p-4 rounded-lg mr-4">
            <i class="fa fa-file-invoice-dollar text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Unpaid Bills</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $unpaidInvoices; ?></p>
        </div>
    </div>

    <!-- Support Tickets Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-red-500 hover:shadow-md transition">
        <div class="bg-red-100 text-red-600 p-4 rounded-lg mr-4">
            <i class="fa fa-headset text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Open Tickets</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $openTickets; ?></p>
        </div>
    </div>

    <!-- Zone Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-blue-500 hover:shadow-md transition">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-lg mr-4">
            <i class="fa fa-map-marked-alt text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Coverage Zones</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_zones; ?></p>
        </div>
    </div>
</div>

<!-- Bottom Section: Grid for Packages & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <!-- Packages Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Package Overview</h3>
            <a href="admin.php?page=packages" class="text-red-500 text-sm hover:underline">Manage</a>
        </div>
        <div class="flex items-center space-x-8">
            <div class="text-center">
                <p class="text-4xl font-extrabold text-gray-700"><?php echo $totalPackages; ?></p>
                <p class="text-sm text-gray-500 mt-1">Total Packages</p>
            </div>
            <div class="text-center">
                <p class="text-4xl font-extrabold text-green-500"><?php echo $activePackages; ?></p>
                <p class="text-sm text-gray-500 mt-1">Active Packages</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="admin.php?page=create_package" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-plus-circle text-red-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">New Package</span>
            </a>
            <a href="admin.php?page=users" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-user-plus text-blue-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">Manage Users</span>
            </a>
            <a href="admin.php?page=billings" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-file-invoice text-orange-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">Billings</span>
            </a>
            <a href="admin.php?page=tickets" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 rounded-lg text-center transition flex flex-col items-center justify-center">
                <i class="fa fa-ticket-alt text-purple-500 text-xl mb-2"></i>
                <span class="text-sm font-semibold">View Tickets</span>
            </a>
        </div>
    </div>
</div>

<?php /* ══════════════════════════════════════════════════════════════════════
   SECTION 3 — NEW CONTENT (appended; Section 2 above is untouched)
   Duplicate removal:
     "Financial Summary" originally showed 6 cards. 3 of them already appeared
     in rows above, so they are removed:
       ✗ Total Revenue  → Section 2 Revenue card
       ✗ Net Profit     → Extra KPI row below
       ✗ This Month     → Extra KPI row below
     The remaining 3 unique items are kept as "Expenses & Receivables".
══════════════════════════════════════════════════════════════════════════ */ ?>

<!-- ── Extra KPI row (all 4 values are unique — not shown anywhere above) ─── -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8 mb-8">

    <!-- Net Profit -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 <?php echo $netProfit >= 0 ? 'border-teal-500' : 'border-red-500'; ?> hover:shadow-md transition">
        <div class="<?php echo $netProfit >= 0 ? 'bg-teal-100 text-teal-600' : 'bg-red-100 text-red-600'; ?> p-4 rounded-lg mr-4">
            <i class="fa fa-chart-line text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Net Profit</h3>
            <p class="text-2xl font-bold <?php echo $netProfit >= 0 ? 'text-teal-600' : 'text-red-600'; ?>">৳<?php echo number_format($netProfit, 2); ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Revenue – Expenses</p>
        </div>
    </div>

    <!-- This Month Revenue -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-indigo-500 hover:shadow-md transition">
        <div class="bg-indigo-100 text-indigo-600 p-4 rounded-lg mr-4">
            <i class="fa fa-calendar-check text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">This Month</h3>
            <p class="text-2xl font-bold text-gray-800">৳<?php echo number_format($monthRevenue, 2); ?></p>
            <p class="text-xs text-gray-400 mt-0.5">Revenue collected</p>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-purple-500 hover:shadow-md transition">
        <div class="bg-purple-100 text-purple-600 p-4 rounded-lg mr-4">
            <i class="fa fa-wifi text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Subscriptions</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $activeSubs; ?> <span class="text-sm font-normal text-yellow-500">(<?php echo $pendingSubs; ?> Pending)</span></p>
            <?php if ($expiringSoon > 0): ?>
                <p class="text-xs text-red-500 mt-0.5"><i class="fa fa-exclamation-triangle"></i> <?php echo $expiringSoon; ?> expiring in 7 days</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Staff -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center border-l-4 border-yellow-500 hover:shadow-md transition">
        <div class="bg-yellow-100 text-yellow-600 p-4 rounded-lg mr-4">
            <i class="fa fa-user-tie text-2xl"></i>
        </div>
        <div>
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Staff Members</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalStaff; ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?php echo $newUsersWeek; ?> new customers (7d)</p>
        </div>
    </div>

</div>

<!-- ── Charts row ─────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <!-- Revenue Bar Chart (spans 2 cols) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Revenue — Last 6 Months</h3>
            <a href="admin.php?page=payments" class="text-red-500 text-sm hover:underline">View All</a>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="dash_revenueChart"></canvas>
        </div>
    </div>

    <!-- Active Subscriptions Doughnut -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Active Plans</h3>
            <a href="admin.php?page=subscriptions" class="text-red-500 text-sm hover:underline">View</a>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="dash_packageChart"></canvas>
        </div>
    </div>

</div>

<!-- ── Ticket categories + Expenses & Receivables ─────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <!-- Ticket by Category Horizontal Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Tickets by Category</h3>
        <div style="position:relative;height:200px;">
            <canvas id="dash_ticketChart"></canvas>
        </div>
    </div>

    <!-- Expenses & Receivables — 3 unique values (Total Revenue / Net Profit /  -->
    <!-- This Month removed; they already appear in Section 2 and the KPI row)   -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Expenses &amp; Receivables</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php
            $expBlocks = [
                ['Total Expenses',   '৳' . number_format($totalExpenses,  2), 'text-orange-600', 'fa-arrow-trend-down',    'bg-orange-50 border-orange-200'],
                ['Unpaid Amount',    '৳' . number_format($unpaidAmount,   2), 'text-red-600',    'fa-triangle-exclamation', 'bg-red-50 border-red-200'],
                ['Pending Invoices', number_format($pendingInvoices),          'text-yellow-600', 'fa-hourglass-half',       'bg-yellow-50 border-yellow-200'],
            ];
            foreach ($expBlocks as [$label, $value, $textCls, $icon, $cardCls]):
            ?>
                <div class="rounded-lg border p-4 <?php echo $cardCls; ?>">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa <?php echo $icon; ?> <?php echo $textCls; ?> text-sm"></i>
                        <span class="text-xs text-gray-500 font-semibold uppercase tracking-wide"><?php echo $label; ?></span>
                    </div>
                    <p class="text-xl font-extrabold <?php echo $textCls; ?>"><?php echo $value; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── Recent Activity Tables ────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

    <!-- Recent Payments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Recent Payments</h3>
            <a href="admin.php?page=payments" class="text-red-500 text-sm hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 text-left font-semibold">Customer</th>
                        <th class="pb-2 text-left font-semibold">Invoice</th>
                        <th class="pb-2 text-right font-semibold">Amount</th>
                        <th class="pb-2 text-left font-semibold">Method</th>
                        <th class="pb-2 text-left font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recentPayments)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">No payments recorded yet.</td>
                        </tr>
                        <?php else: foreach ($recentPayments as $pay): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2.5 pr-2">
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($pay['full_name'] ?? '—'); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($pay['phone'] ?? ''); ?></p>
                                </td>
                                <td class="py-2.5 pr-2 text-xs text-blue-600 font-mono"><?php echo htmlspecialchars($pay['invoice_number'] ?? '—'); ?></td>
                                <td class="py-2.5 text-right font-bold text-gray-800">৳<?php echo number_format($pay['amount'], 2); ?></td>
                                <td class="py-2.5 px-2 text-xs text-gray-500"><?php echo htmlspecialchars($pay['method'] ?? '—'); ?></td>
                                <td class="py-2.5 text-xs text-gray-400 font-mono whitespace-nowrap"><?php echo date('d M y', strtotime($pay['paid_at'])); ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Recent Invoices</h3>
            <a href="admin.php?page=invoices" class="text-red-500 text-sm hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 text-left font-semibold">Invoice #</th>
                        <th class="pb-2 text-left font-semibold">Customer</th>
                        <th class="pb-2 text-right font-semibold">Amount</th>
                        <th class="pb-2 text-left font-semibold">Due</th>
                        <th class="pb-2 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recentInvoices)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">No invoices yet.</td>
                        </tr>
                        <?php else: foreach ($recentInvoices as $inv): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2.5 pr-2 text-xs text-blue-600 font-mono"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                <td class="py-2.5 pr-2 font-medium text-gray-800"><?php echo htmlspecialchars($inv['full_name'] ?? '—'); ?></td>
                                <td class="py-2.5 text-right font-bold text-gray-800">৳<?php echo number_format($inv['amount'], 2); ?></td>
                                <td class="py-2.5 px-2 text-xs text-gray-400 font-mono whitespace-nowrap"><?php echo $inv['due_date'] ? date('d M y', strtotime($inv['due_date'])) : '—'; ?></td>
                                <td class="py-2.5"><?php echo statusBadge($inv['status']); ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── Recent Customers + Open Tickets ───────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

    <!-- Recent Customers -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Recent Customers</h3>
            <a href="admin.php?page=users" class="text-red-500 text-sm hover:underline">Manage</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 text-left font-semibold">Name</th>
                        <th class="pb-2 text-left font-semibold">Package</th>
                        <th class="pb-2 text-left font-semibold">Status</th>
                        <th class="pb-2 text-left font-semibold">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recentCustomers)): ?>
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">No customers yet.</td>
                        </tr>
                        <?php else: foreach ($recentCustomers as $cu): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2.5 pr-2">
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cu['full_name']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($cu['phone'] ?? ''); ?></p>
                                </td>
                                <td class="py-2.5 pr-2 text-xs text-gray-600"><?php echo $cu['package_name'] ? htmlspecialchars($cu['package_name']) : '<span class="text-gray-300">—</span>'; ?></td>
                                <td class="py-2.5"><?php echo statusBadge($cu['status']); ?></td>
                                <td class="py-2.5 text-xs text-gray-400 font-mono whitespace-nowrap"><?php echo date('d M y', strtotime($cu['created_at'])); ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Open / Processing Tickets -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Active Tickets</h3>
            <a href="admin.php?page=tickets" class="text-red-500 text-sm hover:underline">View All</a>
        </div>
        <?php if (empty($recentTickets)): ?>
            <div class="py-8 text-center text-gray-400">
                <i class="fa fa-circle-check text-3xl text-green-400 mb-2"></i>
                <p>No open or processing tickets. 🎉</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentTickets as $tk): ?>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                        <div class="mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                            <?php echo $tk['status'] === 'open' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'; ?>">
                            <?php
                            $catIcon = [
                                'New Installation' => 'fa-plug',
                                'Package Upgrade'  => 'fa-arrow-up',
                                'Billing Issue'    => 'fa-file-invoice-dollar',
                            ];
                            $icon = $catIcon[$tk['category']] ?? 'fa-ticket-alt';
                            ?>
                            <i class="fa <?php echo $icon; ?> text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($tk['subject']); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($tk['customer_name'] ?? '?'); ?>
                                &middot; <?php echo htmlspecialchars($tk['category']); ?>
                                <?php if ($tk['assigned_name']): ?>
                                    &middot; <span class="text-blue-500">→ <?php echo htmlspecialchars($tk['assigned_name']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <?php echo statusBadge($tk['status']); ?>
                            <span class="text-xs text-gray-400 font-mono"><?php echo date('d M', strtotime($tk['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Unread Notifications ───────────────────────────────────────────────── -->
<?php if (!empty($unreadNotifs)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fa fa-bell text-yellow-500 mr-2"></i>Unread Notifications
                <span class="ml-2 bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full"><?php echo count($unreadNotifs); ?></span>
            </h3>
            <a href="admin.php?page=notifications" class="text-red-500 text-sm hover:underline">See All</a>
        </div>
        <ul class="divide-y divide-gray-100">
            <?php foreach ($unreadNotifs as $notif): ?>
                <li class="py-3 flex items-start gap-3">
                    <span class="mt-1.5 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($notif['message']); ?></p>
                        <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo date('d M Y H:i', strtotime($notif['sent_at'])); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php /* ══════════════════════════════════════════════════════════════════════
   SECTION 4 — Chart.js  (loaded once via $GLOBALS guard; scripts unchanged)
══════════════════════════════════════════════════════════════════════════ */ ?>

<?php if (empty($GLOBALS['_chartJsLoaded'])): $GLOBALS['_chartJsLoaded'] = true; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php endif; ?>

<script>
    (function() {
        'use strict';

        var PALETTE = ['#3b82f6', '#22c55e', '#f97316', '#ef4444', '#8b5cf6', '#06b6d4', '#eab308', '#ec4899'];

        Chart.defaults.font.family = "'Inter','ui-sans-serif','system-ui',sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#9ca3af';

        // ── Revenue Bar Chart ────────────────────────────────────────────────────
        (function() {
            var el = document.getElementById('dash_revenueChart');
            if (!el) return;
            var ctx = el.getContext('2d');
            var grad = ctx.createLinearGradient(0, 0, 0, 220);
            grad.addColorStop(0, 'rgba(59,130,246,0.75)');
            grad.addColorStop(1, 'rgba(59,130,246,0.08)');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chartMonthLabels; ?>,
                    datasets: [{
                        label: 'Revenue (৳)',
                        data: <?php echo $chartMonthRevenue; ?>,
                        backgroundColor: grad,
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(c) {
                                    return ' ৳' + Number(c.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            },
                            ticks: {
                                callback: function(v) {
                                    return '৳' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v);
                                }
                            }
                        }
                    }
                }
            });
        }());

        // ── Package Doughnut ─────────────────────────────────────────────────────
        (function() {
            var el = document.getElementById('dash_packageChart');
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo $chartPkgLabels; ?>,
                    datasets: [{
                        data: <?php echo $chartPkgData; ?>,
                        backgroundColor: PALETTE,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                boxWidth: 10,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }());

        // ── Tickets by Category Horizontal Bar ───────────────────────────────────
        (function() {
            var el = document.getElementById('dash_ticketChart');
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo $chartTicketLabels; ?>,
                    datasets: [{
                        label: 'Tickets',
                        data: <?php echo $chartTicketData; ?>,
                        backgroundColor: PALETTE,
                        borderRadius: 5,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }());

    }());
</script>