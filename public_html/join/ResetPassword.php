<?php
// Open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

function generateSalt() {
    return bin2hex(random_bytes(16));
}

$token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');
$stage = 'invalid'; // invalid | form | success
$email = '';
$errorMsg = '';

if ($token) {
    // Look up a non-expired token
    $checkSql = "SELECT * FROM password_reset_tokens WHERE token = ? AND expiration > NOW()";
    $stmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $email = $row['email'];
        $stage = 'form';
    }
    mysqli_stmt_close($stmt);
}

if ($stage === 'form' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    if (strlen($password) < 8) {
        $errorMsg = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $errorMsg = "Passwords do not match.";
    } else {
        $salt = generateSalt();
        $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

        $updateSql = "UPDATE people SET password = ?, salt = ? WHERE email = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "sss", $hashedPassword, $salt, $email);

        if (mysqli_stmt_execute($updateStmt)) {
            // Invalidate this (and any other) reset tokens for this email
            $deleteSql = "DELETE FROM password_reset_tokens WHERE email = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteSql);
            mysqli_stmt_bind_param($deleteStmt, "s", $email);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);

            $stage = 'success';
        } else {
            $errorMsg = "Something went wrong updating your password. Please try again.";
        }
        mysqli_stmt_close($updateStmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --deep:       #1A1628;
    --card:       #201C32;
    --purple:     #6B3FA0;
    --purple-lt:  #9B6FD0;
    --purple-dim: rgba(107,63,160,0.25);
    --gold:       #E8C547;
    --gold-dim:   rgba(232,197,71,0.15);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
    --green:      #52C87A;
    --red:        #E05555;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column;
    overflow-x: hidden;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: #fff; animation: twinkle var(--dur) ease-in-out infinite var(--delay); }
  @keyframes twinkle { 0%,100% { opacity: 0.1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.5); } }
  nav {
    position: relative; z-index: 10; padding: 0 40px; height: 68px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(13,11,26,0.95); border-bottom: 1px solid var(--purple-dim);
  }
  .nav-logo { font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px; color: var(--white); letter-spacing: 0.05em; text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; transition: color 0.2s; }
  .nav-back:hover { color: var(--white); }
  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; position: relative; z-index: 1; }
  .wrap { width: 100%; max-width: 480px; text-align: center; }
  .page-header { text-align: center; margin-bottom: 40px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  .page-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(30px, 5vw, 46px);
    color: var(--white); line-height: 1.1; margin-bottom: 16px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .page-header p { font-size: 15px; color: var(--muted); }
  .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 48px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); text-align: left; }
  .field { margin-bottom: 24px; }
  .field:last-of-type { margin-bottom: 0; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px; }
  input[type="password"] {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 16px 18px; font-size: 16px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s;
  }
  input[type="password"]:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder { color: var(--muted); }
  .password-match { font-size: 13px; margin-top: 8px; min-height: 18px; }
  .password-match.ok  { color: var(--green); }
  .password-match.bad { color: var(--red); }
  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 8px;
    transition: background 0.2s, transform 0.15s, opacity 0.2s;
  }
  .submit-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
  .submit-btn:not(:disabled):hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  .status-card { border-radius: 14px; padding: 32px 36px; margin-bottom: 28px; text-align: center; }
  .status-card.error { background: var(--card); border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin: 0; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-size: 14px; font-weight: 600; letter-spacing: 0.05em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
  @media (max-width: 600px) { nav { padding: 0 20px; } .form-card { padding: 32px 24px; } }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/" class="nav-back">← Back to Home</a>
</nav>

<main>
  <div class="wrap">

    <?php if ($stage === 'invalid'): ?>
      <div class="status-icon">⚠️</div>
      <h1 style="font-family:'Cinzel',serif;font-size:clamp(26px,4vw,38px);color:var(--white);margin-bottom:20px;text-shadow:0 0 40px rgba(107,63,160,0.7);">Link Expired or Invalid</h1>
      <div class="status-card error">
        <p>This password reset link is invalid or has expired. Reset links are only valid for 1 hour.</p>
      </div>
      <a href="ForgotPassword.php" class="btn-primary">Request a New Link</a>

    <?php elseif ($stage === 'form'): ?>
      <div class="page-header">
        <div class="eyebrow">Account Recovery</div>
        <h1>Choose a New Password</h1>
        <p>Resetting the password for <?php echo htmlspecialchars($email); ?></p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="status-card error"><p><?php echo htmlspecialchars($errorMsg); ?></p></div>
      <?php endif; ?>

      <div class="form-card">
        <form action="ResetPassword.php" method="post" onsubmit="return checkFinal()">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
          <div class="field">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required minlength="8" oninput="checkPasswordMatch()">
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required oninput="checkPasswordMatch()">
            <div class="password-match" id="passwordMatchMessage"></div>
          </div>
          <button type="submit" id="submitButton" disabled class="submit-btn">Reset Password ✦</button>
        </form>
      </div>

    <?php else: ?>
      <div class="status-icon">✦</div>
      <h1 style="font-family:'Cinzel',serif;font-size:clamp(26px,4vw,38px);color:var(--white);margin-bottom:20px;text-shadow:0 0 40px rgba(107,63,160,0.7);">Password Reset</h1>
      <div class="status-card" style="border:1px solid rgba(82,200,122,0.4);background:var(--card);">
        <p>Your password has been updated. You can now log in with your new password.</p>
      </div>
      <a href="login.php" class="btn-primary">Go to Login</a>
    <?php endif; ?>

  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wade@mythosevents.com">wade@mythosevents.com</a></p>
</footer>

<script>
  const container = document.getElementById('stars');
  for (let i = 0; i < 120; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }

  function checkPasswordMatch() {
    const pwField = document.getElementById('password');
    const cpwField = document.getElementById('confirmPassword');
    if (!pwField) return;
    const pw = pwField.value;
    const cpw = cpwField.value;
    const msg = document.getElementById('passwordMatchMessage');
    const btn = document.getElementById('submitButton');
    if (cpw.length === 0) {
      msg.textContent = ''; msg.className = 'password-match';
      btn.disabled = true; return;
    }
    if (pw === cpw && pw.length >= 8) {
      msg.textContent = '✓ Passwords match';
      msg.className = 'password-match ok';
      btn.disabled = false;
    } else if (pw !== cpw) {
      msg.textContent = '✗ Passwords do not match';
      msg.className = 'password-match bad';
      btn.disabled = true;
    } else {
      msg.textContent = '';
      msg.className = 'password-match';
      btn.disabled = true;
    }
  }
  function checkFinal() { return true; }
</script>
</body>
</html>
