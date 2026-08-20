<?php
// Open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Make sure the token table exists (self-healing, no manual migration needed)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expiration DATETIME NOT NULL,
  INDEX (token)
)");

$statusType = '';
$status = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';

    // Validate and sanitize the email address
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Check if the email exists using a prepared statement
        $checkSql = "SELECT * FROM people WHERE email = ?";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $storeTokenSql = "INSERT INTO password_reset_tokens (email, token, expiration) VALUES (?, ?, ?)";
            $storeStmt = mysqli_prepare($conn, $storeTokenSql);
            mysqli_stmt_bind_param($storeStmt, "sss", $email, $token, $expiration);
            mysqli_stmt_execute($storeStmt);
            mysqli_stmt_close($storeStmt);

            // Craft the password reset link — correct domain and path
            $resetLink = "https://mythosevents.com/join/ResetPassword.php?token=" . urlencode($token);

            $subject = "Reset Your Mythos Events Password";
            $message = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light only">
<title>Reset Your Password — Mythos Events</title></head>
<body style="margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0D0B1A;padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;">
        <tr>
          <td align="center" style="padding:40px 0 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#9B6FD0;text-transform:uppercase;">Password Reset</p>
            <h1 style="margin:16px 0 0;font-family:Georgia,serif;font-size:42px;font-weight:900;color:#FFFFFF;letter-spacing:2px;">Mythos<span style="color:#E8C547;">✦</span>Events</h1>
          </td>
        </tr>
        <tr>
          <td style="background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;">
            <h2 style="margin:0 0 16px;font-family:Georgia,serif;font-size:26px;font-weight:600;color:#FFFFFF;line-height:1.2;">Reset Your Password</h2>
            <p style="margin:0 0 20px;font-size:16px;color:#C4A8E8;line-height:1.7;">We received a request to reset your Mythos Events password. Click the button below to choose a new one. This link expires in 1 hour.</p>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
              <tr>
                <td align="center" style="background-color:#6B3FA0;border-radius:8px;">
                  <a href="' . $resetLink . '" style="display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;">Reset My Password ✦</a>
                </td>
              </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>
            <p style="margin:0 0 12px;font-size:14px;color:rgba(196,168,232,0.6);line-height:1.6;">If the button doesn\'t work, copy and paste this link into your browser:</p>
            <p style="margin:0;font-size:13px;word-break:break-all;">
              <a href="' . $resetLink . '" style="color:#9B6FD0;text-decoration:underline;">' . $resetLink . '</a>
            </p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:32px 0 0;">
            <p style="margin:0 0 8px;font-size:12px;color:rgba(196,168,232,0.4);letter-spacing:2px;text-transform:uppercase;">Mythos Events &nbsp;·&nbsp; Glendale, Arizona</p>
            <p style="margin:0;font-size:12px;color:rgba(196,168,232,0.3);">If you didn\'t request this, you can safely ignore this email — your password will stay the same.</p>
            <p style="margin:8px 0 0;font-size:12px;color:rgba(196,168,232,0.3);">Problems? Email <a href="mailto:wadehawkins@mythosevents.com" style="color:rgba(196,168,232,0.5);">wadehawkins@mythosevents.com</a></p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

            $headers  = "From: wadehawkins@mythosevents.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $statusType = 'success';
                $status = "We've sent a password reset link to <strong>" . htmlspecialchars($email) . "</strong>. Check your inbox — the link expires in 1 hour.";
            } else {
                $statusType = 'error';
                $status = "Something went wrong sending the reset email. Please try again, or email <a href='mailto:wadehawkins@mythosevents.com' style='color:var(--purple-lt)'>wadehawkins@mythosevents.com</a> directly.";
            }
        } else {
            $statusType = 'warning';
            $status = "We couldn't find an account with that email address.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $statusType = 'error';
        $status = "That doesn't look like a valid email address. Please try again.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password — Mythos Events</title>
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
  .wrap { width: 100%; max-width: 480px; }
  .page-header { text-align: center; margin-bottom: 40px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  .page-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(30px, 5vw, 46px);
    color: var(--white); line-height: 1.1; margin-bottom: 16px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .page-header p { font-size: 15px; color: var(--muted); }
  .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 48px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .field { margin-bottom: 24px; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px; }
  input[type="email"] {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 16px 18px; font-size: 16px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s;
  }
  input[type="email"]:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder { color: var(--muted); }
  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 8px;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-card { border-radius: 14px; padding: 32px 36px; margin-bottom: 28px; }
  .status-card.success { background: var(--card); border: 1px solid rgba(82,200,122,0.4); }
  .status-card.warning { background: var(--card); border: 1px solid rgba(232,197,71,0.4); }
  .status-card.error   { background: var(--card); border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin: 0; }
  .form-note { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }
  .form-note a { color: var(--purple-lt); }
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

    <div class="page-header">
      <div class="eyebrow">Account Recovery</div>
      <h1>Forgot Password</h1>
      <p>Enter your email and we'll send you a link to choose a new password.</p>
    </div>

    <?php if ($statusType): ?>
      <div class="status-card <?php echo $statusType; ?>">
        <p><?php echo $status; ?></p>
      </div>
    <?php endif; ?>

    <?php if ($statusType !== 'success'): ?>
    <div class="form-card">
      <form action="ForgotPassword.php" method="post">
        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="your@email.com" required autofocus>
        </div>
        <button type="submit" class="submit-btn">Send Reset Link ✦</button>
      </form>
      <p class="form-note"><a href="login.php">Back to Login</a></p>
    </div>
    <?php else: ?>
      <p class="form-note" style="text-align:center;"><a href="login.php">Back to Login</a></p>
    <?php endif; ?>

  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
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
</script>
</body>
</html>
