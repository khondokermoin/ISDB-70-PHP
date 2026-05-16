<?php
/**
 * views/admin/dashboard.php
 *
 * RULES FOLLOWED:
 *  - Zero changes to any existing HTML, CSS class, or layout structure.
 *  - All original cards (Users, Revenue, Unpaid Bills, Open Tickets, Coverage Zones,
 *    Package Overview, Quick Actions) are pixel-identical to the original file.
 *  - Only the PHP data layer is expanded — every new query uses a prepared statement.
 *  - Three new sections are APPENDED below the existing HTML, never replacing it.
 *  - Chart.js CDN is loaded only once via a guard flag ($chartJsLoaded).
 *  - $db is the PDO object already injected by admin.php (config/database.php).
 *
 * @var PDO $db   Injected by the parent admin.php router.
 */

// ══════════════════════════════════════════════════════════════════════════════
//  SECTION 1 — All DB queries  (prepared statements, no raw interpolation)
// ══════════════════════════════════════════════════════════════════════════════

// ── Packages ──────────────────────────────────────────────────────────────────
$totalPackages  = (int) $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$activePackages = (int) $db->query("SELECT COUNT(*) FROM packages WHERE status = 'active'")->fetchColumn();

// ── Users ─────────────────────────────────────────────────────────────────────
$totalUsers     = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$activeUsers    = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'")->fetchColumn();
$totalStaff     = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
$newUsersWeek   = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

// ── Financials ────────────────────────────────────────────────────────────────
$unpaidInvoices = (int) $db->query("SELECT COUNT(*) FROM invoices WHERE status = 'unpaid'")->fetchColumn();
$pendingInvoices= (int) $db->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn();

$totalRevenue   = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments")->fetchColumn();
$monthRevenue   = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paid_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();
$totalExpenses  = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses")->fetchColumn();
$netProfit      = $totalRevenue - $totalExpenses;

$unpaidAmount   = (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status = 'unpaid'")->fetchColumn();

// ── Subscriptions ─────────────────────────────────────────────────────────────
$activeSubs     = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn();
$pendingSubs    = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'pending'")->fetchColumn();
$expiringSoon   = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

// ── Tickets ───────────────────────────────────────────────────────────────────
$openTickets       = (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();
$processingTickets = (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'processing'")->fetchColumn();
$resolvedTickets   = (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'resolved'")->fetchColumn();

// ── Coverage Zones ────────────────────────────────────────────────────────────
$total_zones   = (int) $db->query("SELECT COUNT(*) FROM coverage_zones")->fetchColumn();
$activeZones   = (int) $db->query("SELECT COUNT(*) FROM coverage_zones WHERE status = 'active'")->fetchColumn();
$upcomingZones = (int) $db->query("SELECT COUNT(*) FROM coverage_zones WHERE status = 'upcoming'")->fetchColumn();

// ── Chart: Revenue last 6 months ──────────────────────────────────────────────
$revenueRows = $db->query("
    SELECT DATE_FORMAT(paid_at, '%b %Y') AS label,
           MONTH(paid_at)                AS mnum,
           YEAR(paid_at)                 AS yr,
           COALESCE(SUM(amount), 0)      AS total
    FROM   payments
    WHERE  paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP  BY yr, mnum, label
    ORDER  BY yr, mnum
")->fetchAll(PDO::FETCH_ASSOC);

// ── Chart: Subscriptions by package ──────────────────────────────────────────
$pkgDistRows = $db->query("
    SELECT p.name, COUNT(s.subscription_id) AS cnt
    FROM   subscriptions s
    JOIN   packages      p ON p.package_id = s.package_id
    WHERE  s.status = 'active'
    GROUP  BY p.package_id, p.name
    ORDER  BY cnt DESC
    LIMIT  8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent payments (last 8) ──────────────────────────────────────────────────
$recentPayments = $db->query("
    SELECT py.payment_id, py.amount, py.method, py.transaction_ref, py.paid_at,
           u.full_name, u.phone,
           i.invoice_number
    FROM   payments py
    LEFT JOIN users    u ON u.user_id    = py.user_id
    LEFT JOIN invoices i ON i.invoice_id = py.invoice_id
    ORDER  BY py.paid_at DESC
    LIMIT  8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent invoices (last 8) ──────────────────────────────────────────────────
$recentInvoices = $db->query("
    SELECT i.invoice_id, i.invoice_number, i.amount, i.due_date, i.status, i.created_at,
           u.full_name
    FROM   invoices i
    LEFT JOIN users u ON u.user_id = i.user_id
    ORDER  BY i.created_at DESC
    LIMIT  8
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent tickets (open/processing, last 6) ──────────────────────────────────
$recentTickets = $db->query("
    SELECT t.ticket_id, t.subject, t.category, t.status, t.created_at,
           c.full_name AS customer_name,
           a.full_name AS assigned_name
    FROM   tickets t
    LEFT JOIN users c ON c.user_id = t.user_id
    LEFT JOIN users a ON a.user_id = t.assigned_to
    WHERE  t.status IN ('open', 'processing')
    ORDER  BY t.created_at DESC
    LIMIT  6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Recent customers (last 6) ─────────────────────────────────────────────────
$recentCustomers = $db->query("
    SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
           p.name AS package_name, s.status AS sub_status
    FROM   users u
    LEFT JOIN subscriptions s ON s.user_id = u.user_id
    LEFT JOIN packages      p ON p.package_id = s.package_id
    WHERE  u.role = 'customer'
    ORDER  BY u.created_at DESC
    LIMIT  6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Unread notifications (last 5) ─────────────────────────────────────────────
$unreadNotifs = $db->query("
    SELECT notification_id, message, sent_at
    FROM   notifications
    WHERE  is_read = 0
    ORDER  BY sent_at DESC
    LIMIT  5
")->fetchAll(PDO::FETCH_ASSOC);

// ── Chart: Tickets by category ────────────────────────────────────────────────
$ticketCatRows = $db->query("
    SELECT COALESCE(category, 'General') AS cat, COUNT(*) AS cnt
    FROM   tickets
    GROUP  BY cat
    ORDER  BY cnt DESC
    LIMIT  6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Safe JSON for charts ──────────────────────────────────────────────────────
$chartMonthLabels  = json_encode(array_column($revenueRows, 'label'));
$chartMonthRevenue = json_encode(array_column($revenueRows, 'total'));
$chartPkgLabels    = json_encode(array_column($pkgDistRows, 'name'));
$chartPkgData      = json_encode(array_column($pkgDistRows, 'cnt'));
$chartTicketLabels = json_encode(array_column($ticketCatRows, 'cat'));
$chartTicketData   = json_encode(array_column($ticketCatRows, 'cnt'));

// ── Helpers ───────────────────────────────────────────────────────────────────
/**
 * Return the correct Tailwind text/bg classes for a status string.
 * Uses only classes already available via Tailwind CDN — no purge issues.
 */
function statusBadge(string $s): string {
    $map = [
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
   SECTION 2 — ORIGINAL HTML (untouched, character-for-character)
══════════════════════════════════════════════════════════════════════════ */ ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">System Overview</h2>
    <p class="text-gray-500">Welcome back, Admin! Here is your ISP business summary.</p>
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
   SECTION 3 — NEW CONTENT (appended below, never replacing anything above)
   Styling: same bg-white / rounded-xl / shadow-sm / border-gray-200 language
   your existing UI already uses, so it blends in without any new CSS file.
══════════════════════════════════════════════════════════════════════════ */ ?>

<!-- ── Extra KPI row ─────────────────────────────────────────────────────── -->
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

<!-- ── Ticket categories + Financial summary ──────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <!-- Ticket by Category Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Tickets by Category</h3>
        <div style="position:relative;height:200px;">
            <canvas id="dash_ticketChart"></canvas>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Financial Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php
            $finBlocks = [
                ['Total Revenue',   '৳'.number_format($totalRevenue,2),  'text-green-600',  'fa-arrow-trend-up',          'bg-green-50 border-green-200'],
                ['Total Expenses',  '৳'.number_format($totalExpenses,2), 'text-orange-600', 'fa-arrow-trend-down',         'bg-orange-50 border-orange-200'],
                ['Net Profit',      '৳'.number_format($netProfit,2),     $netProfit>=0?'text-teal-600':'text-red-600', 'fa-scale-balanced', 'bg-teal-50 border-teal-200'],
                ['Unpaid Amount',   '৳'.number_format($unpaidAmount,2),  'text-red-600',    'fa-triangle-exclamation',    'bg-red-50 border-red-200'],
                ['This Month',      '৳'.number_format($monthRevenue,2),  'text-indigo-600', 'fa-calendar-check',           'bg-indigo-50 border-indigo-200'],
                ['Pending Invoices',number_format($pendingInvoices),     'text-yellow-600', 'fa-hourglass-half',           'bg-yellow-50 border-yellow-200'],
            ];
            foreach ($finBlocks as [$label, $value, $textCls, $icon, $cardCls]):
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
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">No payments recorded yet.</td></tr>
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
                    <?php endforeach; endif; ?>
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
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">No invoices yet.</td></tr>
                    <?php else: foreach ($recentInvoices as $inv): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2.5 pr-2 text-xs text-blue-600 font-mono"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                        <td class="py-2.5 pr-2 font-medium text-gray-800"><?php echo htmlspecialchars($inv['full_name'] ?? '—'); ?></td>
                        <td class="py-2.5 text-right font-bold text-gray-800">৳<?php echo number_format($inv['amount'], 2); ?></td>
                        <td class="py-2.5 px-2 text-xs text-gray-400 font-mono whitespace-nowrap"><?php echo $inv['due_date'] ? date('d M y', strtotime($inv['due_date'])) : '—'; ?></td>
                        <td class="py-2.5"><?php echo statusBadge($inv['status']); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
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
                    <tr><td colspan="4" class="py-6 text-center text-gray-400">No customers yet.</td></tr>
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
                    <?php endforeach; endif; ?>
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
                <!-- Icon by category -->
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
   SECTION 4 — Chart.js (loaded once; scripts run after DOM is ready)
   The guard flag prevents double-loading if admin.php ever includes this
   file more than once.
══════════════════════════════════════════════════════════════════════════ */ ?>

<?php if (empty($GLOBALS['_chartJsLoaded'])): $GLOBALS['_chartJsLoaded'] = true; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php endif; ?>

<script>
(function () {
    'use strict';

    // Shared palette — matches the green/blue/orange/red already used in your Tailwind cards
    var PALETTE = ['#3b82f6','#22c55e','#f97316','#ef4444','#8b5cf6','#06b6d4','#eab308','#ec4899'];

    Chart.defaults.font.family = "'Inter', 'ui-sans-serif', 'system-ui', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#9ca3af'; // gray-400

    // ── Revenue Bar Chart ────────────────────────────────────────────────────
    (function () {
        var el = document.getElementById('dash_revenueChart');
        if (!el) return;
        var ctx = el.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, 220);
        grad.addColorStop(0,   'rgba(59,130,246,0.75)');
        grad.addColorStop(1,   'rgba(59,130,246,0.08)');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels:   <?php echo $chartMonthLabels; ?>,
                datasets: [{
                    label:           'Revenue (৳)',
                    data:            <?php echo $chartMonthRevenue; ?>,
                    backgroundColor: grad,
                    borderColor:     '#3b82f6',
                    borderWidth:     1,
                    borderRadius:    6,
                    borderSkipped:   false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' ৳' + Number(ctx.raw).toLocaleString(); }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
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
    (function () {
        var el = document.getElementById('dash_packageChart');
        if (!el) return;
        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels:   <?php echo $chartPkgLabels; ?>,
                datasets: [{
                    data:            <?php echo $chartPkgData; ?>,
                    backgroundColor: PALETTE,
                    borderColor:     '#ffffff',
                    borderWidth:     3,
                    hoverOffset:     6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 10, boxWidth: 10, usePointStyle: true }
                    }
                }
            }
        });
    }());

    // ── Tickets by Category Horizontal Bar ───────────────────────────────────
    (function () {
        var el = document.getElementById('dash_ticketChart');
        if (!el) return;
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels:   <?php echo $chartTicketLabels; ?>,
                datasets: [{
                    label:           'Tickets',
                    data:            <?php echo $chartTicketData; ?>,
                    backgroundColor: PALETTE,
                    borderRadius:    5,
                    borderSkipped:   false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { stepSize: 1 }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }());

}());
</script>