<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$staff_id = $_SESSION['user_id'];
$ticket_id = (int)$_GET['id'];

// টিকিট চেক
$stmt = $db->prepare("SELECT t.*, u.full_name as customer_name, u.phone as customer_phone, u.address FROM tickets t JOIN users u ON t.user_id = u.user_id WHERE t.ticket_id = ? AND t.assigned_to = ?");
$stmt->execute([$ticket_id, $staff_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) die("<h2 style='text-align:center; color:red; margin-top:50px;'>Access Denied! This ticket is not assigned to you.</h2>");

// 🔥 স্ট্যাটাস আপডেট এবং নোটিফিকেশন লজিক
if (isset($_GET['new_status'])) {
    $status = $_GET['new_status'];
    $db->prepare("UPDATE tickets SET status = ? WHERE ticket_id = ?")->execute([$status, $ticket_id]);

    // যদি কাজ Resolved হয়, অ্যাডমিনকে নোটিফিকেশন পাঠাবে
    if ($status == 'resolved') {
        $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
        if ($adminQuery) {
            $notif_msg = "✅ Job Completed: Ticket #{$ticket_id} has been marked as resolved by Technician. Please review or ACTIVATE the line.";
            $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
        }
    }

    header("Location: staff_view_ticket.php?id=$ticket_id");
    exit;
}

// রিপ্লাই সেভ লজিক (যা ছিল তাই)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)")->execute([$ticket_id, $staff_id, $_POST['reply']]);
    $db->query("UPDATE tickets SET status='processing' WHERE ticket_id=$ticket_id AND status='open'");
    header("Location: staff_view_ticket.php?id=$ticket_id");
    exit;
}

$replies = $db->prepare("SELECT r.*, u.full_name, u.role FROM ticket_replies r JOIN users u ON r.user_id = u.user_id WHERE r.ticket_id = ? ORDER BY r.replied_at ASC");
$replies->execute([$ticket_id]);
$replies = $replies->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Work Details - Amar IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 py-8">
    <div class="container mx-auto max-w-4xl px-4">

        <div class="flex justify-between items-center mb-6">
            <a href="staff_dashboard.php" class="text-gray-600 hover:text-gray-900 font-bold"><i class="fa fa-arrow-left"></i> Back to Tasks</a>

            <?php if ($ticket['status'] != 'resolved'): ?>
                <div class="space-x-2 bg-white p-2 rounded shadow-sm border">
                    <span class="text-sm font-bold text-gray-500 mr-2">Action:</span>
                    <a href="staff_view_ticket.php?id=<?php echo $ticket_id; ?>&new_status=processing" class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-4 py-2 rounded text-xs font-bold transition">Mark Processing</a>
                    <a href="staff_view_ticket.php?id=<?php echo $ticket_id; ?>&new_status=resolved" class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded text-xs font-bold transition shadow">Complete Job (Resolved)</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border-t-4 border-red-500 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-[10px] font-bold uppercase mb-2 inline-block"><i class="fa fa-tag"></i> <?php echo htmlspecialchars($ticket['category']); ?></span>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border <?php echo ($ticket['status'] == 'open') ? 'bg-red-50 text-red-600' : (($ticket['status'] == 'processing') ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'); ?>">
                    <?php echo $ticket['status']; ?>
                </span>
            </div>

            <p class="text-gray-700 bg-gray-50 p-4 rounded text-sm border mb-4">
                <strong>Customer Issue:</strong><br>
                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
            </p>

            <div class="flex justify-between items-center border-t pt-4">
                <div class="text-sm">
                    <p class="text-gray-800 font-bold"><i class="fa fa-user text-gray-400 w-5"></i> <?php echo htmlspecialchars($ticket['customer_name']); ?></p>
                    <p class="text-gray-600 mt-1"><i class="fa fa-map-marker-alt text-gray-400 w-5"></i> <?php echo htmlspecialchars($ticket['address']); ?></p>
                </div>

                <a href="https://wa.me/88<?php echo htmlspecialchars($ticket['customer_phone']); ?>" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg font-bold shadow transition flex items-center text-sm">
                    <i class="fab fa-whatsapp text-lg mr-2"></i> WhatsApp Customer
                </a>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 mb-4"><i class="fa fa-comments text-gray-400 mr-2"></i> Conversation History</h3>
        <div class="space-y-4 mb-6">
            <?php foreach ($replies as $r): ?>
                <div class="p-4 rounded-xl shadow-sm border <?php echo ($r['role'] == 'staff') ? 'bg-red-50 ml-10 border-red-200' : 'bg-white mr-10 border-gray-200'; ?>">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-xs font-bold <?php echo ($r['role'] == 'staff') ? 'text-red-600' : 'text-blue-600'; ?>">
                            <?php echo ($r['role'] == 'staff') ? '<i class="fa fa-user-tie"></i> You (Technician)' : '<i class="fa fa-user"></i> ' . htmlspecialchars($r['full_name']); ?>
                        </p>
                        <span class="text-xs text-gray-400"><?php echo date("d M Y, h:i A", strtotime($r['replied_at'])); ?></span>
                    </div>
                    <p class="text-sm text-gray-800 mt-2"><?php echo nl2br(htmlspecialchars($r['message'])); ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($replies)) echo "<p class='text-center text-gray-400 text-sm py-4'>No messages yet.</p>"; ?>
        </div>

        <?php if ($ticket['status'] != 'resolved'): ?>
            <form method="POST" class="bg-white p-6 rounded-xl shadow-sm border">
                <label class="block font-bold text-gray-700 mb-2">Update Customer</label>
                <textarea name="reply" rows="3" required placeholder="Type your message to the customer..." class="w-full border border-gray-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-red-500 mb-4"></textarea>
                <button type="submit" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold shadow transition"><i class="fa fa-paper-plane mr-2"></i> Send Message</button>
            </form>
        <?php else: ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg text-center font-bold border border-green-200"><i class="fa fa-check-circle mr-2"></i> This job is marked as Resolved.</div>
        <?php endif; ?>
    </div>
</body>

</html>