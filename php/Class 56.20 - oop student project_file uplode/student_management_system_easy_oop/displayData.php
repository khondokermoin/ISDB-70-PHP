<?php
require_once __DIR__ . '/studentRepository.php';

// Repository object তৈরি হচ্ছে
$repo = new StudentRepository();

// সব student object array আকারে নিচ্ছে
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
                <th>Course</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <!-- getter method দিয়ে object data দেখানো হচ্ছে -->
                    <td><?= ($s->getId()) ?></td>
                    <td><?= ($s->getName()) ?></td>
                    <td><?= ($s->getCourse()) ?></td>
                    <td><?= ($s->getPhone()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
