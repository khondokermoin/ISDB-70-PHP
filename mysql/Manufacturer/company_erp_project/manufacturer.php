<?php
require_once __DIR__ . '/db.php';

if (isset($_POST['btnSubmit'])) {
    $name = trim($_POST['mname'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if ($name === '' || $contact === '') {
        set_flash('Manufacturer name and contact are required.', 'error');
    } elseif (!preg_match('/^[0-9+]{11,14}$/', $contact)) {
        set_flash('Contact number must be 11 to 14 digits or +.', 'error');
    } else {
        $stmt = $db->prepare('CALL add_manufacture(?, ?)');
        if ($stmt) {
            $stmt->bind_param('ss', $name, $contact);
            if ($stmt->execute()) {
                set_flash('Manufacturer added successfully.');
            } else {
                set_flash('Failed to add manufacturer.', 'error');
            }
            $stmt->close();
            while ($db->more_results() && $db->next_result()) {
                $temp = $db->store_result();
                if ($temp instanceof mysqli_result) {
                    $temp->free();
                }
            }
        } else {
            set_flash('Stored procedure not found. Run setup.sql first.', 'error');
        }
    }
    redirect_to('manufacturer.php');
}

$pageTitle = 'Manufacturer';
$list = $db->query('SELECT id, name, contact, created_at FROM manufacturer ORDER BY id DESC');
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>Add Manufacturer</h2>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="mname">Manufacturer Name</label>
                <input type="text" id="mname" name="mname" required>
            </div>
            <div>
                <label for="contact">Contact</label>
                <input type="text" id="contact" name="contact" required>
            </div>
        </div>
        <div style="margin-top:14px;">
            <input type="submit" name="btnSubmit" value="Save Manufacturer">
        </div>
    </form>
</div>

<div class="card">
    <h2>Manufacturer List</h2>
    <div class="table-wrap">
        <table>
            <tr><th>ID</th><th>Name</th><th>Contact</th><th>Created</th></tr>
            <?php if ($list instanceof mysqli_result && $list->num_rows > 0): ?>
                <?php while ($row = $list->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No manufacturer found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
