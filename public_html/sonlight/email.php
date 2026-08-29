<?php
$dbname = "db9dh4gg0yfw3q";
include('../join/logintodatabase.php');

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

        if ($existing && !empty($existing['password'])) {
            $statusType = 'warning';
            $status = "The email <strong>$email</strong> is already in our system. Please use a different address or <a href='/join/login.php' style='color:var(--sun-primary)'>log in</a>.";
        } else {
            $confirmLink = 'https://mythosevents.com/join/addapplicant.php?email=' . urlencode($email) . '&id=' . urlencode($id) . '&source=sonlight';

            $message = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light only">
<title>Confirm Your Email — Sonlight Drama Team</title></head>
<body style="margin:0;padding:0;background-color:#FFF6E9;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFF6E9;padding:40px 20px;">
    <tr><td align="center">
      <table width="540" cellpadding="0" cellspacing="0" style="max-width:540px;width:100%;">

        <tr>
          <td align="center" style="padding:40px 0 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#FF7B4F;text-transform:uppercase;">You\'re Almost In</p>
            <h1 style="margin:16px 0 0;font-family:Georgia,serif;font-size:38px;font-weight:900;color:#3A2E2A;">☀️ Sonlight</h1>
            <p style="margin:8px 0 0;font-family:Georgia,serif;font-size:14px;color:#8C7B72;letter-spacing:1px;">Drama Team</p>
          </td>
        </tr>

        <tr>
          <td style="background-color:#FFFFFF;border:2px solid #F0DFC8;border-radius:16px;padding:44px 44px 36px;">
            <h2 style="margin:0 0 16px;font-family:Georgia,serif;font-size:22px;font-weight:700;color:#3A2E2A;line-height:1.3;">Confirm Your Email</h2>
            <p style="margin:0 0 20px;font-size:15px;color:#8C7B72;line-height:1.7;">You\'re one step away from joining the Sonlight Drama Team. Click the button below to confirm your email and finish setting up your profile.</p>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
              <tr><td style="border-top:1px solid #F0DFC8;"></td></tr>
            </table>

            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
              <tr>
                <td align="center" style="background-color:#FF7B4F;border-radius:30px;">
                  <a href="' . $confirmLink . '" style="display:inline-block;padding:15px 40px;font-family:Georgia,serif;font-size:14px;font-weight:700;letter-spacing:2px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;">Confirm My Email ☀️</a>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
              <tr><td style="border-top:1px solid #F0DFC8;"></td></tr>
            </table>

            <p style="margin:0 0 10px;font-size:13px;color:#8C7B72;line-height:1.6;">If the button doesn\'t work, copy and paste this link into your browser:</p>
            <p style="margin:0;font-size:12px;word-break:break-all;">
              <a href="' . $confirmLink . '" style="color:#FF7B4F;text-decoration:underline;">' . $confirmLink . '</a>
            </p>
          </td>
        </tr>

        <tr>
          <td align="center" style="padding:28px 0 0;">
            <p style="margin:0 0 6px;font-size:12px;color:#8C7B72;letter-spacing:2px;text-transform:uppercase;">Sonlight Drama Team</p>
            <p style="margin:0;font-size:12px;color:#B0A090;">If you didn\'t request this, you can safely ignore this email.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

            $headers  = "From: confirm@sonlight.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($email, "Join Sonlight Drama Team — Confirm Your Email", $message, $headers)) {
                $statusType = 'success';
                $status = "We've sent a confirmation link to <strong>$email</strong>. Check your inbox and click the link to finish setting up your profile.";
            } else {
                $statusType = 'error';
                $status = "Something went wrong sending your confirmation email. Please try again, or email <a href='mailto:wadehawkins@mythosevents.com' style='color:var(--sun-primary)'>wadehawkins@mythosevents.com</a> directly.";
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
<title>Check Your Email — Sonlight Drama Team</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --sun-bg: #FFF6E9;
    --sun-card: #FFFFFF;
    --sun-primary: #FF7B4F;
    --sun-primary-dk: #E86335;
    --sun-gold: #FFC145;
    --sun-text: #3A2E2A;
    --sun-muted: #8C7B72;
    --sun-border: #F0DFC8;
  }
  body {
    background: var(--sun-bg); color: var(--sun-text);
    font-family: 'Nunito', sans-serif; font-size: 16px; line-height: 1.7;
    min-height: 100vh; display: flex; flex-direction: column;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; }
  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: var(--sun-card); border-bottom: 2px solid var(--sun-border);
  }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 20px; color: var(--sun-text); text-decoration: none; }
  .nav-logo span { color: var(--sun-primary); }
  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; }
  .wrap { max-width: 520px; width: 100%; text-align: center; }
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  h1 { font-weight: 900; font-size: clamp(24px, 5vw, 34px); color: var(--sun-text); margin-bottom: 16px; }
  .status-card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 16px;
    padding: 32px 36px; margin-bottom: 24px; text-align: left;
  }
  .status-card.success { border-color: rgba(63,166,107,0.4); }
  .status-card.warning { border-color: rgba(255,193,69,0.6); }
  .status-card.error   { border-color: rgba(224,86,79,0.4); }
  .status-card p { font-size: 15px; color: var(--sun-text); line-height: 1.7; }
  .steps { background: #FFF3E8; border: 2px solid var(--sun-gold); border-radius: 12px; padding: 24px 28px; margin-bottom: 24px; text-align: left; }
  .steps h3 { font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--sun-primary); margin-bottom: 14px; }
  .step { display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--sun-muted); margin-bottom: 10px; }
  .step:last-child { margin-bottom: 0; }
  .step-num { width: 22px; height: 22px; border-radius: 50%; background: #fff; border: 2px solid var(--sun-gold); display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; font-size: 10px; font-weight: 700; color: var(--sun-primary); flex-shrink: 0; margin-top: 2px; }
  .btn { display: inline-block; background: var(--sun-primary); color: #fff; padding: 13px 28px; border-radius: 30px; text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 14px; transition: background 0.2s, transform 0.15s; }
  .btn:hover { background: var(--sun-primary-dk); transform: translateY(-2px); }
  .btn-outline { background: transparent; color: var(--sun-text); border: 2px solid var(--sun-border); margin-left: 10px; }
  .btn-outline:hover { border-color: var(--sun-primary); color: var(--sun-primary); }
  footer { text-align: center; padding: 24px; border-top: 2px solid var(--sun-border); font-size: 13px; color: var(--sun-muted); }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }
</style>
</head>
<body>
<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
</nav>
<main>
  <div class="wrap">
    <?php if ($statusType === 'success'): ?>
      <div class="status-icon">✉️</div>
      <h1>Check Your Inbox</h1>
      <div class="status-card success">
        <p><?php echo $status; ?></p>
      </div>
      <div class="steps">
        <h3>What Happens Next</h3>
        <div class="step"><div class="step-num">1</div><span>Open the confirmation email from <strong>confirm@sonlight.com</strong></span></div>
        <div class="step"><div class="step-num">2</div><span>Click the link inside to open your profile form</span></div>
        <div class="step"><div class="step-num">3</div><span>Fill in your name and a little about yourself</span></div>
        <div class="step"><div class="step-num">4</div><span>You're in — the Scheduler will be waiting for you ☀️</span></div>
      </div>
      <a href="/sonlight/" class="btn">Back to Sonlight</a>
    <?php elseif ($statusType === 'warning'): ?>
      <div class="status-icon">⚠️</div>
      <h1>Already Registered</h1>
      <div class="status-card warning">
        <p><?php echo $status; ?></p>
      </div>
      <a href="/sonlight/join.php" class="btn">Try a Different Email</a>
      <a href="/sonlight/" class="btn-outline btn">Back to Sonlight</a>
    <?php else: ?>
      <div class="status-icon">☀️</div>
      <h1>Something Went Wrong</h1>
      <div class="status-card error">
        <p><?php echo $status; ?></p>
      </div>
      <a href="/sonlight/join.php" class="btn">Try Again</a>
    <?php endif; ?>
  </div>
</main>
<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">Questions?</a></p>
</footer>
</body>
</html>
