<?php
session_start();

$file = __DIR__ . '/data.txt';
$message = '';

if (isset($_SESSION['username'])) {
    header('Location: view_products.php');
    exit();
}

if (isset($_POST['btnLogin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'All fields are required.';
    } else {
        $loginSuccess = false;

        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $parts = strpos($line, '|') !== false ? explode('|', $line) : explode(',', $line);

                if (count($parts) >= 2) {
                    $savedUsername = trim($parts[0]);
                    $savedPassword = trim($parts[1]);

                    if ($savedUsername === $username) {
                        if (password_verify($password, $savedPassword) || $password === $savedPassword) {
                            $_SESSION['username'] = $username;
                            $loginSuccess = true;
                            header('Location: view_products.php');
                            exit();
                        }
                    }
                }
            }
        }

        if (!$loginSuccess) {
            $message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — ProductHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:    #0c0c0e;
            --paper:  #faf9f7;
            --accent: #3b82f6;
            --muted:  #8a8a8e;
            --border: #e4e2de;
            --card:   #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--paper);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* LEFT PANEL */
        .left-panel {
            background: #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 56px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 460px; height: 460px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 70%);
            top: -100px; right: -130px;
            pointer-events: none;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,0.10) 0%, transparent 70%);
            bottom: -80px; left: -80px;
            pointer-events: none;
        }

        .brand { position: relative; z-index: 1; }

        .brand-mark {
            width: 42px; height: 42px;
            background: var(--accent);
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 52px;
        }

        .brand-mark svg { width: 22px; height: 22px; }

        .panel-headline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 3.5vw, 54px);
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .panel-headline em { font-style: italic; color: var(--accent); }

        .panel-sub {
            margin-top: 20px;
            font-size: 15px;
            font-weight: 300;
            color: rgba(255,255,255,0.40);
            line-height: 1.65;
            max-width: 300px;
        }

        .panel-footer {
            position: relative; z-index: 1;
            font-size: 12px;
            color: rgba(255,255,255,0.18);
            letter-spacing: 0.5px;
        }

        /* RIGHT PANEL */
        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 56px;
        }

        .form-box {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.45s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .form-sub {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 36px;
        }

        .form-sub a { color: var(--accent); text-decoration: none; font-weight: 500; }
        .form-sub a:hover { text-decoration: underline; }

        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .field input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background: var(--card);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
            letter-spacing: 0.2px;
        }

        .btn-login:hover  { background: #1e293b; }
        .btn-login:active { transform: scale(0.99); }

        .alert {
            background: #fff1ee;
            border: 1px solid #f5c4ba;
            color: #a8341a;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert::before { content: '⚠'; }

        @media (max-width: 720px) {
            body { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="left-panel">
    <div class="brand">
        <div class="brand-mark">
            <svg viewBox="0 0 24 24" fill="white">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
        <h1 class="panel-headline">Welcome<br><em>back.</em></h1>
        <p class="panel-sub">Sign in to manage your products, brands, and inventory all in one place.</p>
    </div>
    <p class="panel-footer">© <?= date('Y') ?> ProductHub</p>
</div>

<div class="right-panel">
    <div class="form-box">
        <h2 class="form-title">Sign in</h2>
        <p class="form-sub">New here? <a href="register.php">Create an account</a></p>

        <?php if ($message !== ''): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="your_username" autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" name="btnLogin" class="btn-login">Sign in →</button>
        </form>
    </div>
</div>

</body>
</html>
