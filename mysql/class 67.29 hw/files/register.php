<?php
session_start();

$file = __DIR__ . '/data.txt';
$message = '';
$msgType = 'error';

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

if (!file_exists($file)) {
    file_put_contents($file, '');
}

if (isset($_POST['btnRegister'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $message = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } else {
        $exists = false;
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $parts = strpos($line, '|') !== false ? explode('|', $line) : explode(',', $line);
            if (count($parts) >= 2 && trim($parts[0]) === $username) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $message = 'Username already taken. Try another.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $saveLine = $username . '|' . $hashedPassword . PHP_EOL;
            file_put_contents($file, $saveLine, FILE_APPEND | LOCK_EX);
            $message = 'Account created! You can now sign in.';
            $msgType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0c0c0e;
            --paper: #faf9f7;
            --accent: #c8522a;
            --muted: #8a8a8e;
            --border: #e4e2de;
            --card: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--paper);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .left-panel {
            background: var(--ink);
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
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200,82,42,0.15) 0%, transparent 65%);
            bottom: -150px;
            right: -150px;
            pointer-events: none;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 48px;
        }

        .brand-mark svg { width: 22px; height: 22px; }

        .panel-headline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 3.5vw, 52px);
            font-weight: 900;
            color: #ffffff;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .panel-headline em { font-style: italic; color: var(--accent); }

        .panel-sub {
            margin-top: 20px;
            font-size: 15px;
            font-weight: 300;
            color: rgba(255,255,255,0.45);
            line-height: 1.6;
            max-width: 320px;
        }

        .steps {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            flex-shrink: 0;
            margin-top: 1px;
        }

        .step-text {
            font-size: 13px;
            color: rgba(255,255,255,0.35);
            line-height: 1.5;
        }

        .panel-footer {
            font-size: 12px;
            color: rgba(255,255,255,0.2);
            letter-spacing: 0.5px;
        }

        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 56px;
        }

        .form-box {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
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

        .form-sub a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .form-sub a:hover { text-decoration: underline; }

        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.7px;
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
            box-shadow: 0 0 0 3px rgba(200,82,42,0.10);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-register:hover  { background: #b34525; }
        .btn-register:active { transform: scale(0.99); }

        .alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert.error   { background:#fff1ee; border:1px solid #f5c4ba; color:#a8341a; }
        .alert.success { background:#eefaf4; border:1px solid #aadec4; color:#1a6644; }
        .alert.error::before   { content: '⚠'; }
        .alert.success::before { content: '✓'; }

        @media (max-width: 720px) {
            body { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="left-panel">
    <div>
        <div class="brand-mark">
            <svg viewBox="0 0 24 24" fill="white">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <h1 class="panel-headline">Join us<br><em>today.</em></h1>
        <p class="panel-sub">Create your account and start uploading files in seconds.</p>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">Pick a username and password</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">Sign in to your new account</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">Start uploading and managing files</div>
            </div>
        </div>
    </div>
    <p class="panel-footer">© <?= date('Y') ?> FileVault</p>
</div>

<div class="right-panel">
    <div class="form-box">
        <h2 class="form-title">Create account</h2>
        <p class="form-sub">Already have one? <a href="index.php">Sign in</a></p>

        <?php if ($message !== ''): ?>
            <div class="alert <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="choose_a_username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="min. 6 characters" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="repeat your password" autocomplete="new-password">
            </div>
            <button type="submit" name="btnRegister" class="btn-register">Create account →</button>
        </form>
    </div>
</div>

</body>
</html>
