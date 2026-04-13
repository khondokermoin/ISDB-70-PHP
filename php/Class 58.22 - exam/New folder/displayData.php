<?php
require_once __DIR__ . '/studentRepository.php';

$repo = new StudentRepository();
$students = $repo->getAll();
?>

<?php if (empty($students)): ?>
    <p>No student data found.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Batch</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s->getId()) ?></td>
                    <td><?= htmlspecialchars($s->getName()) ?></td>
                    <td><?= htmlspecialchars($s->getBatch()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
