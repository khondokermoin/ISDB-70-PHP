<?php
require_once __DIR__ . '/formHandler.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; }
        .field { margin-bottom: 12px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input { width: 100%; padding: 10px; box-sizing: border-box; }
        button { padding: 10px 16px; cursor: pointer; }
        .success { color: green; margin-bottom: 12px; }
        .error { color: red; margin-bottom: 12px; }
        .result-box { background: #f8f8f8; padding: 12px; border-radius: 6px; margin-top: 10px; line-height: 1.8; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Student Management System</h1>

            <?php if (!empty($message)): ?>
                <p class="<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <div class="field">
                    <label for="txtId">ID</label>
                    <input type="text" id="txtId" name="txtId" required>
                </div>

                <div class="field">
                    <label for="txtName">Name</label>
                    <input type="text" id="txtName" name="txtName" required>
                </div>

                <div class="field">
                    <label for="txtBatch">Batch</label>
                    <input type="text" id="txtBatch" name="txtBatch" required>
                </div>

                <button type="submit" name="btnSubmit">Submit</button>
            </form>
        </div>

        <div class="card">
            <h2>Search Student by ID</h2>
            <form action="" method="post">
                <div class="field">
                    <label for="searchId">Enter Student ID</label>
                    <input type="text" id="searchId" name="searchId" required>
                </div>
                <button type="submit" name="btnSearch">Show Student</button>
            </form>

            <?php if ($resultMessage !== ''): ?>
                <div class="result-box"><?= $resultMessage ?></div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Student List</h2>
            <?php require __DIR__ . '/displayData.php'; ?>
        </div>
    </div>
</body>
</html>
