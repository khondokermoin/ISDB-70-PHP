<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();
$staff_id = $_SESSION['user_id'];

// আজকের কাজ এবং পেন্ডিং কাজের পরিসংখ্যান
$stats = $db->prepare("
    SELECT 
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as todays_tasks,
        COUNT(CASE WHEN status != 'resolved' THEN 1 END) as total_pending,
        COUNT(CASE WHEN status = 'resolved' THEN 1 END) as total_resolved
    FROM tickets WHERE assigned_to = ?
");
$stats->execute([$staff_id]);
$counts = $stats->fetch(PDO::FETCH_ASSOC);

// Daily Works / Pending Tasks (যে কাজগুলো এখনো শেষ হয়নি)
$query = "SELECT t.*, u.full_name as customer_name, u.address, u.phone 
          FROM tickets t 
          JOIN users u ON t.user_id = u.user_id 
          WHERE t.assigned_to = :sid AND t.status != 'resolved' 
          ORDER BY t.status DESC, t.created_at ASC"; 
$stmt = $db->prepare($query);
$stmt->execute([':sid' => $staff_id]);
$pending_works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Daily Works - Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-gray-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-red-500">AMAR IT <span class="text-gray-400 text-sm">| Technician Portal</span></h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm hidden md:block">Hello, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="logout.php" class="bg-red-600 px-4 py-1.5 rounded text-sm font-bold hover:bg-red-700 transition">Logout</a>
                <a href="staff_profile_edit.php" class="text-blue-400 hover:text-blue-300 text-sm font-bold mr-4"><i class="fa fa-user-edit"></i> Edit Profile</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto max-w-6xl py-8 px-4">
        
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">My Daily Works Overview</h2>
            <p class="text-gray-500">Date: <?php echo date('l, d F Y'); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Tasks Assigned Today</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $counts['todays_tasks']; ?></p>
                </div>
                <i class="fa fa-calendar-day text-4xl text-red-100"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Total Pending Works</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $counts['total_pending']; ?></p>
                </div>
                <i class="fa fa-spinner text-4xl text-blue-100"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-wide">Total Resolved</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $counts['total_resolved']; ?></p>
                </div>
                <i class="fa fa-check-circle text-4xl text-green-100"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800"><i class="fa fa-list-check text-blue-500 mr-2"></i> Pending Tasks (To-Do)</h2>
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold"><?php echo count($pending_works); ?> Jobs Left</span>
            </div>
            
            <div class="p-6">
                <?php if(count($pending_works) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach($pending_works as $job): ?>
                            <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition relative <?php echo ($job['status'] == 'open') ? 'bg-white border-l-4 border-l-red-500' : 'bg-blue-50 border-l-4 border-l-blue-500'; ?>">
                                
                                <div class="flex justify-between items-start mb-3">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded <?php echo ($job['status'] == 'open') ? 'bg-red-100 text-red-700' : 'bg-blue-200 text-blue-800'; ?>">
                                        <?php echo $job['status']; ?>
                                    </span>
                                    <span class="text-xs text-gray-400 font-semibold"><i class="fa fa-clock"></i> <?php echo date("h:i A (d M)", strtotime($job['created_at'])); ?></span>
                                </div>

                                <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($job['subject']); ?></h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">"<?php echo htmlspecialchars($job['message']); ?>"</p>

                                <div class="bg-gray-100 p-3 rounded text-sm text-gray-700 mb-4">
                                    <p><i class="fa fa-user mr-2 text-gray-400"></i> <strong><?php echo htmlspecialchars($job['customer_name']); ?></strong></p>
                                    <p class="mt-1"><i class="fa fa-map-marker-alt mr-2 text-gray-400"></i> <?php echo htmlspecialchars($job['address']); ?></p>
                                </div>

                                <div class="flex items-center justify-between border-t pt-4">
                                    <a href="https://wa.me/88<?php echo htmlspecialchars($job['phone']); ?>" target="_blank" class="text-green-600 hover:text-green-800 font-bold text-sm">
                                        <i class="fab fa-whatsapp text-lg mr-1"></i> Contact
                                    </a>
                                    <a href="staff_view_ticket.php?id=<?php echo $job['ticket_id']; ?>" class="bg-gray-800 text-white px-4 py-2 rounded font-bold text-sm hover:bg-black transition">
                                        Start Work <i class="fa fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10">
                        <i class="fa fa-mug-hot text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-700">All caught up!</h3>
                        <p class="text-gray-500">You have no pending works for today. Great job!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>