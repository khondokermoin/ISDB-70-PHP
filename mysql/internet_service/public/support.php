<?php
session_start();
// ইউজার লগইন করা না থাকলে হোমপেজে পাঠিয়ে দেবে
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$success_message = "";
$error_message = "";

// ফর্ম সাবমিট হলে টিকিট সেভ করার লজিক
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $category = trim($_POST['category']);
    $message = trim($_POST['message']);

    if (!empty($subject) && !empty($message)) {
        try {
            $query = "INSERT INTO tickets (user_id, subject, category, message, status) VALUES (:uid, :sub, :cat, :msg, 'open')";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':uid' => $user_id,
                ':sub' => $subject,
                ':cat' => $category,
                ':msg' => $message
            ]);


            // 🔥 কাস্টমার নতুন টিকিট খুললে অ্যাডমিনকে নোটিফিকেশন পাঠানো
            $adminQuery = $db->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
            if ($adminQuery) {
                // নতুন টিকিটের আইডি ডাটাবেস থেকে ধরা হলো এবং কাস্টমারের নাম যুক্ত করা হলো
                $new_ticket_id = $db->lastInsertId();
                $customer_name = $_SESSION['user_name'] ?? 'Customer';

                $notif_msg = "🎫 Support Alert: Ticket #{$new_ticket_id} opened by {$customer_name} regarding '{$category}'. Please assign a staff member.";
                $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$adminQuery['user_id'], $notif_msg]);
            }

            $success_message = "Your support ticket has been submitted successfully! We will contact you soon.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error_message = "Failed to submit the ticket. Please try again later.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// ইউজারের আগের টিকিটগুলো ডাটাবেস থেকে আনা হচ্ছে
$stmtTickets = $db->prepare("SELECT * FROM tickets WHERE user_id = :uid ORDER BY created_at DESC");
$stmtTickets->execute([':uid' => $user_id]);
$tickets = $stmtTickets->fetchAll(PDO::FETCH_ASSOC);

include '../views/layouts/header.php';
?>

<section class="bg-gray-800 text-white py-12">
    <div class="container mx-auto max-w-6xl px-4 text-center">
        <h1 class="text-3xl font-extrabold mb-2">Support Center</h1>
        <p class="text-gray-400">How can we help you today, <?php echo htmlspecialchars($_SESSION['user_name']); ?>?</p>
    </div>
</section>

<section class="py-10 bg-gray-50 min-h-screen">
    <div class="container mx-auto max-w-6xl px-4">

        <div class="mb-6">
            <a href="user_dashboard.php" class="text-blue-600 hover:underline font-semibold"><i class="fa fa-arrow-left mr-1"></i> Back to Dashboard</a>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <i class="fa fa-check-circle mr-2"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                <i class="fa fa-exclamation-circle mr-2"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-plus-circle text-amberRed mr-2"></i> Open New Ticket</h3>

                    <form action="support.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Issue Category <span class="text-red-500">*</span></label>
                            <select name="category" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                                <option value="No Internet">No Internet Connection</option>
                                <option value="Slow Speed">Slow Internet Speed</option>
                                <option value="Billing Issue">Billing / Payment Issue</option>
                                <option value="Package Upgrade">Package Upgrade / Change</option>
                                <option value="Other">Other Issues</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Subject <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" required placeholder="e.g., Internet disconnecting frequently" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Message Detail <span class="text-red-500">*</span></label>
                            <textarea name="message" rows="5" required placeholder="Describe your issue in detail..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amberRed outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-amberRed hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow transition">
                            Submit Ticket
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa fa-history text-gray-500 mr-2"></i> Your Ticket History</h3>

                    <?php if (count($tickets) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($tickets as $ticket):
                                // Status color logic
                                $statusClass = 'bg-gray-100 text-gray-800';
                                $iconClass = 'fa-clock';
                                if ($ticket['status'] == 'open') {
                                    $statusClass = 'bg-red-100 text-red-700 border-red-200';
                                    $iconClass = 'fa-exclamation-circle';
                                } elseif ($ticket['status'] == 'processing') {
                                    $statusClass = 'bg-blue-100 text-blue-700 border-blue-200';
                                    $iconClass = 'fa-spinner fa-spin';
                                } elseif ($ticket['status'] == 'resolved') {
                                    $statusClass = 'bg-green-100 text-green-700 border-green-200';
                                    $iconClass = 'fa-check-circle';
                                }
                            ?>
                                <div class="p-5 border border-gray-100 rounded-lg hover:shadow-md transition bg-gray-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $statusClass; ?>">
                                                <i class="fa <?php echo $iconClass; ?> mr-1"></i> <?php echo strtoupper($ticket['status']); ?>
                                            </span>
                                            <a href="view_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded text-xs font-bold transition shadow-sm">View Thread</a>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 mb-3 space-x-4">
                                        <span><i class="fa fa-tag mr-1"></i> <?php echo htmlspecialchars($ticket['category']); ?></span>
                                        <span><i class="fa fa-calendar-alt mr-1"></i> <?php echo date("d M Y, h:i A", strtotime($ticket['created_at'])); ?></span>
                                        <span><i class="fa fa-ticket-alt mr-1"></i> #TKT-<?php echo $ticket['ticket_id']; ?></span>
                                    </div>
                                    <div class="text-gray-700 text-sm bg-white p-4 rounded border border-gray-200">
                                        <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 bg-gray-50 rounded border border-dashed border-gray-300">
                            <div class="text-gray-400 mb-3"><i class="fa fa-inbox text-5xl"></i></div>
                            <p class="text-gray-600 font-semibold">No support tickets found.</p>
                            <p class="text-sm text-gray-500">If you face any issues, feel free to open a new ticket.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include '../views/layouts/footer.php'; ?>