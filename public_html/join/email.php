<?php
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$status = '';
$statusType = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $id = isset($_POST["id"]) ? $_POST["id"] : '';

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $checkStmt = mysqli_prepare($conn, "SELECT password FROM people WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $existing = mysqli_fetch_assoc($result);
        mysqli_stmt_close($checkStmt);

        // Someone already fully registered (has a password) needs to log in instead.
        // A passwordless row (e.g. subscriber-only) is still free to complete a full
        // application — same email, no dead end.
        if ($existing && !empty($existing['password'])) {
            $statusType = 'warning';
            $status = "The email <strong>$email</strong> is already in our system. Please use a different address or log in below.";
        } else {
            $subject = "Welcome to Mythos Events — Confirm Your Email";

            // Confirmation email — inline styles for email client compatibility
            $confirmLink = 'https://mythosevents.com/join/addapplicant.php?email=' . urlencode($email) . '&id=' . urlencode($id);

            $message = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light only">
<title>Confirm Your Email — Mythos Events</title></head>
<body style="margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0D0B1A;padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;">

        <!-- Header -->
        <tr>
          <td align="center" style="padding:40px 0 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#9B6FD0;text-transform:uppercase;">You\'re Almost In</p>
            <h1 style="margin:16px 0 0;font-family:Georgia,serif;font-size:42px;font-weight:900;color:#FFFFFF;letter-spacing:2px;text-shadow:none;">Mythos<span style="color:#E8C547;">✦</span>Events</h1>
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;">

            <h2 style="margin:0 0 16px;font-family:Georgia,serif;font-size:26px;font-weight:600;color:#FFFFFF;line-height:1.2;">Confirm Your Email</h2>
            <p style="margin:0 0 20px;font-size:16px;color:#C4A8E8;line-height:1.7;">Thank you for wanting to be part of the Mythos. You\'re one step away — just click the button below to confirm your email address and you\'ll be taken to a short form to complete your profile.</p>

            <!-- Divider -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>

            <!-- CTA Button -->
            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
              <tr>
                <td align="center" style="background-color:#6B3FA0;border-radius:8px;">
                  <a href="' . $confirmLink . '" style="display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;">Confirm My Email ✦</a>
                </td>
              </tr>
            </table>

            <!-- Divider -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>

            <p style="margin:0 0 12px;font-size:14px;color:rgba(196,168,232,0.6);line-height:1.6;">If the button doesn\'t work, copy and paste this link into your browser:</p>
            <p style="margin:0;font-size:13px;word-break:break-all;">
              <a href="' . $confirmLink . '" style="color:#9B6FD0;text-decoration:underline;">' . $confirmLink . '</a>
            </p>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td align="center" style="padding:32px 0 0;">
            <p style="margin:0 0 8px;font-size:12px;color:rgba(196,168,232,0.4);letter-spacing:2px;text-transform:uppercase;">Mythos Events &nbsp;·&nbsp; Glendale, Arizona</p>
            <p style="margin:0;font-size:12px;color:rgba(196,168,232,0.3);">If you didn\'t request this, you can safely ignore this email.</p>
            <p style="margin:8px 0 0;font-size:12px;color:rgba(196,168,232,0.3);">Problems? Email <a href="mailto:wadehawkins@mythosevents.com" style="color:rgba(196,168,232,0.5);">wadehawkins@mythosevents.com</a></p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

            $headers  = "From: confirm@MythosEvents.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $statusType = 'success';
                $status = "We've sent a confirmation link to <strong>$email</strong>. Check your inbox and click the link to complete your profile.";
            } else {
                $statusType = 'error';
                $status = "Something went wrong sending your confirmation email. Please try again, or email <a href='mailto:wadehawkins@mythosevents.com' style='color:var(--purple-lt)'>wadehawkins@mythosevents.com</a> directly.";
            }
        }
    } else {
        $statusType = 'error';
        $status = "That doesn't look like a valid email address. Please go back and try again.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mythos Events — Check Your Email</title>
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
  .star {
    position: absolute; border-radius: 50%; background: #fff;
    animation: twinkle var(--dur) ease-in-out infinite var(--delay);
  }
  @keyframes twinkle {
    0%,100% { opacity: 0.1; transform: scale(1); }
    50%      { opacity: 0.9; transform: scale(1.5); }
  }
  nav {
    position: relative; z-index: 10;
    padding: 0 40px; height: 68px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(13,11,26,0.95);
    border-bottom: 1px solid var(--purple-dim);
  }
  .nav-logo {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px;
    color: var(--white); letter-spacing: 0.05em; text-decoration: none;
  }
  .nav-logo span { color: var(--gold); }
  .nav-back {
    font-size: 13px; color: var(--muted); text-decoration: none;
    letter-spacing: 0.08em; transition: color 0.2s;
  }
  .nav-back:hover { color: var(--white); }
  main {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 60px 20px; position: relative; z-index: 1;
  }
  .response-wrap { width: 100%; max-width: 520px; text-align: center; }
  .status-icon { font-size: 56px; margin-bottom: 24px; }
  .response-wrap h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(28px, 4vw, 40px); color: var(--white);
    line-height: 1.2; margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .status-card {
    background: var(--card); border-radius: 14px; padding: 40px 44px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    margin-bottom: 32px;
  }
  .status-card.success { border: 1px solid rgba(82,200,122,0.4); }
  .status-card.warning { border: 1px solid rgba(232,197,71,0.4); }
  .status-card.error   { border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 16px; color: var(--lilac); line-height: 1.7; margin: 0; }
  .check-steps {
    background: var(--deep); border: 1px solid var(--purple-dim);
    border-radius: 10px; padding: 28px 32px; text-align: left; margin-bottom: 32px;
  }
  .check-steps h3 {
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.15em;
    color: var(--purple-lt); margin-bottom: 16px;
  }
  .step {
    display: flex; gap: 14px; align-items: flex-start;
    font-size: 14px; color: var(--muted); margin-bottom: 12px;
  }
  .step:last-child { margin-bottom: 0; }
  .step-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--purple-dim); border: 1px solid var(--purple);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Cinzel', serif; font-size: 11px; color: var(--purple-lt);
    flex-shrink: 0; margin-top: 2px;
  }
  .btn-back {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-size: 14px; font-weight: 600; letter-spacing: 0.05em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-back:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .btn-ghost {
    display: inline-block; background: transparent; color: var(--lilac);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-size: 14px; letter-spacing: 0.05em;
    border: 1px solid var(--purple-dim);
    transition: border-color 0.2s, transform 0.15s;
    margin-left: 12px;
  }
  .btn-ghost:hover { border-color: var(--purple-lt); transform: translateY(-2px); }
  footer {
    position: relative; z-index: 1;
    text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim);
    font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }
  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .status-card { padding: 28px 24px; }
    .btn-ghost { margin-left: 0; margin-top: 12px; }
  }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/" class="nav-back">← Back to Home</a>
</nav>

<main>
  <div class="response-wrap">

    <?php if ($statusType === 'success'): ?>
      <div class="status-icon">✉️</div>
      <h1>Check Your Inbox</h1>
      <div class="status-card success">
        <p><?php echo $status; ?></p>
      </div>
      <div class="check-steps">
        <h3>What Happens Next</h3>
        <div class="step">
          <div class="step-num">1</div>
          <span>Open the confirmation email from <strong style="color:var(--lilac)">confirm@mythosevents.com</strong></span>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <span>Click the confirmation link inside</span>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <span>Fill out your profile — name, role, a little about yourself</span>
        </div>
        <div class="step">
          <div class="step-num">4</div>
          <span>We'll be in touch about upcoming events</span>
        </div>
      </div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:28px;">
        Don't see the email in 10 minutes? Check your spam folder or
        <a href="mailto:wadehawkins@mythosevents.com" style="color:var(--purple-lt)">email us directly</a>.
      </p>
      <a href="/" class="btn-back">Back to Mythos Events</a>

    <?php elseif ($statusType === 'warning'): ?>
      <div class="status-icon">⚠️</div>
      <h1>Already Registered</h1>
      <div class="status-card warning">
        <p><?php echo $status; ?></p>
      </div>
      <a href="/join/" class="btn-back">Try a Different Email</a>
      <a href="/" class="btn-ghost">Back to Home</a>

    <?php else: ?>
      <div class="status-icon">✦</div>
      <h1>Something Went Wrong</h1>
      <div class="status-card error">
        <p><?php echo $status; ?></p>
      </div>
      <a href="/join/" class="btn-back">Try Again</a>
      <a href="/" class="btn-ghost">Back to Home</a>

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