<?php
session_start();
// কাস্টমার ছাড়া অন্য কেউ যেন ঢুকতে না পারে
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// টিকিট চেক করা এবং অ্যাসাইন করা স্টাফের ইনফো আনা
$stmt = $db->prepare("
    SELECT t.*, s.full_name as staff_name, s.phone as staff_phone 
    FROM tickets t 
    LEFT JOIN users s ON t.assigned_to = s.user_id 
    WHERE t.ticket_id = ? AND t.user_id = ?
");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:red; font-size:40px;'>404 Error</h1>
            <h2>Ticket Not Found!</h2>
            <p>The ticket you are trying to view does not exist or you don't have permission.</p>
            <a href='user_dashboard.php' style='color:blue;'>Go Back to Dashboard</a>
         </div>");
}

// কাস্টমার মেসেজ পাঠালে তা সেভ করা
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
    $msg = trim($_POST['reply_message']);
    if (!empty($msg) && $ticket['status'] != 'resolved') {
        $stmtRep = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmtRep->execute([$ticket_id, $user_id, $msg]);
        header("Location: view_ticket.php?id=$ticket_id");
        exit;
    }
}

// চ্যাট হিস্ট্রি (আগের মেসেজগুলো) তুলে আনা
$stmtRep = $db->prepare("SELECT r.*, u.full_name, u.role FROM ticket_replies r JOIN users u ON r.user_id = u.user_id WHERE r.ticket_id = ? ORDER BY r.replied_at ASC");
$stmtRep->execute([$ticket_id]);
$replies = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

include '../views/layouts/header.php';
?>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="container mx-auto max-w-4xl px-4">
        <a href="support.php" class="text-blue-600 hover:underline mb-4 inline-block font-semibold">
            <i class="fa fa-arrow-left"></i> Back to Support
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                    <p class="text-sm text-gray-500 mt-1">Ticket #<?php echo $ticket['ticket_id']; ?> | <span class="font-semibold text-blue-600"><i class="fa fa-tag"></i> <?php echo htmlspecialchars($ticket['category']); ?></span></p>
                </div>
                <span class="px-4 py-1 text-sm font-bold rounded-full border <?php echo ($ticket['status'] == 'open') ? 'bg-red-50 text-red-600 border-red-200' : (($ticket['status'] == 'processing') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-green-50 text-green-600 border-green-200'); ?> uppercase">
                    <?php echo $ticket['status']; ?>
                </span>
            </div>

            <?php if ($ticket['assigned_to']): ?>
                <div class="bg-red-50 border border-red-100 rounded-xl p-5 mb-6 flex items-center justify-between shadow-sm">
                    <div class="flex items-center">
                        <div class="bg-red-100 text-red-600 w-12 h-12 rounded-full flex items-center justify-center text-xl mr-4">
                            <i class="fa <?php echo ($ticket['category'] == 'New Installation') ? 'fa-tools' : 'fa-user-shield'; ?>"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-red-500 font-bold uppercase tracking-widest">
                                <?php echo ($ticket['category'] == 'New Installation') ? 'Installation Technician' : 'Support Expert'; ?>
                            </p>
                            <p class="text-lg font-extrabold text-gray-800"><?php echo htmlspecialchars($ticket['staff_name']); ?></p>
                            <p class="text-xs text-gray-500">Handling your request</p>
                        </div>
                    </div>
                    <a href="https://wa.me/88<?php echo htmlspecialchars($ticket['staff_phone']); ?>" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-xl font-bold shadow transition flex items-center">
                        <i class="fab fa-whatsapp text-xl mr-2"></i> WhatsApp
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-5 mb-6 text-center text-gray-500">
                    <i class="fa fa-clock mb-2 text-2xl text-gray-400"></i>
                    <p class="text-sm font-semibold">Waiting for an expert to be assigned...</p>
                </div>
            <?php endif; ?>

            <div class="text-gray-700 bg-gray-50 p-4 rounded text-sm border border-gray-100 leading-relaxed">
                <strong class="block mb-2 text-gray-800">Your Original Message:</strong>
                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 mb-4"><i class="fa fa-comments text-gray-400 mr-2"></i> Replies</h3>
        <div class="space-y-4 mb-8">
            <?php foreach ($replies as $reply): ?>
                <div class="p-4 rounded-xl shadow-sm border <?php echo ($reply['role'] == 'customer') ? 'bg-white border-gray-200 mr-10' : 'bg-red-50 border-red-200 ml-10'; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <strong class="<?php echo ($reply['role'] == 'customer') ? 'text-blue-600' : 'text-red-600'; ?>">
                            <?php echo ($reply['role'] == 'customer') ? '<i class="fa fa-user mr-1"></i> You' : '<i class="fa fa-headset mr-1"></i> Amar IT Support'; ?>
                        </strong>
                        <span class="text-xs text-gray-400"><?php echo date("d M Y, h:i A", strtotime($reply['replied_at'])); ?></span>
                    </div>
                    <p class="text-gray-700 text-sm"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($replies)) echo "<p class='text-gray-500 text-center text-sm py-6 border border-dashed rounded bg-gray-50'>No replies yet. Send a message below.</p>"; ?>
        </div>

        <?php if ($ticket['status'] != 'resolved'): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="view_ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                    <textarea name="reply_message" rows="3" required placeholder="Type your reply here..." class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none mb-4"></textarea>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition"><i class="fa fa-reply mr-2"></i> Send Message</button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-green-100 text-green-700 p-4 rounded text-center border border-green-300 font-bold shadow-sm">
                <i class="fa fa-check-circle mr-2"></i> This issue has been marked as resolved.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>