<?php
require_once __DIR__ . '/studentRepository.php';

$repo = new StudentRepository();
$students = $repo->getAll();
?>

<?php if (empty($students)): ?>
    <p>No student data found.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Phone</th>
        </tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student->getId()); ?></td>
                <td><?php echo htmlspecialchars($student->getName()); ?></td>
                <td><?php echo htmlspecialchars($student->getCourse()); ?></td>
                <td><?php echo htmlspecialchars($student->getPhone()); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
