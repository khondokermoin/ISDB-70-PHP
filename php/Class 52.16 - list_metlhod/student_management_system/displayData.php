<?php
// studentRepository.php file include করা হচ্ছে
// যাতে StudentRepository class ব্যবহার করা যায়
require_once __DIR__ . '/studentRepository.php';

// Repository object তৈরি করা হচ্ছে
// এই object file থেকে student data read করবে
$repo = new StudentRepository();

// getAll() method call করে file থেকে সব student data নেওয়া হচ্ছে
// $students একটি array হবে, যেখানে Student object থাকবে
$students = $repo->getAll();
?>

<?php if (empty($students)): ?>
    <!-- যদি কোনো student data না থাকে -->
    <p>No student data found.</p>
<?php else: ?>
    <!-- যদি student data থাকে তাহলে table show করবে -->
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>

            <!-- students array loop করা হচ্ছে -->
            <?php foreach ($students as $s): ?>
                <tr>
                    <!-- htmlspecialchars() → XSS(Cross-Site Scripting) attack prevent করার জন্য -->
                    <!-- Getter method দিয়ে Student object থেকে data নেওয়া হচ্ছে -->
                    <td><?= htmlspecialchars($s->getId()) ?></td>
                    <td><?= htmlspecialchars($s->getName()) ?></td>
                    <td><?= htmlspecialchars($s->getCourse()) ?></td>
                    <td><?= htmlspecialchars($s->getPhone()) ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
<?php endif; ?>


<!-- displayData.php run
        ↓
StudentRepository include
        ↓
Repository object তৈরি
        ↓
getAll() → file থেকে data read
        ↓
students array পাওয়া
        ↓
if empty → No data message
        ↓
else → table show
        ↓
foreach loop
        ↓
Student object → getter method
        ↓
htmlspecialchars → safe output
        ↓
HTML table show -->