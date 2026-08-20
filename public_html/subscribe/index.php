<?php
// Check for form submission
$status = '';
$statusType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dbname = "db9dh4gg0yfw3q";
    include('../join/logintodatabase.php');

    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $firstName = isset($_POST["firstName"]) ? mysqli_real_escape_string($conn, $_POST["firstName"]) : '';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $checkSql = "SELECT * FROM people WHERE email = '$email'";
        $result = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($result) > 0) {
            // Update existing record to add subscriber involvement type
            $updateSql = "UPDATE people SET involvement_type = 'Subscriber' WHERE email = '$email'";
            if (mysqli_query($conn, $updateSql)) {
                $statusType = 'success';
                $status = "You're already in our system! We've marked you as a subscriber.";
            } else {
                $statusType = 'error';
                $status = "Something went wrong. Please try again.";
            }
        } else {
            // Insert new subscriber
            $insertSql = "INSERT INTO people (email, first, involvement_type)
                          VALUES ('$email', '$firstName', 'Subscriber')";

            if (mysqli_query($conn, $insertSql)) {
                $statusType = 'success';
                $status = "Thanks for subscribing! We'll send you updates about upcoming Mythos Events.";

                // Send confirmation email
                $subject = "Welcome to Mythos Events";
                $safeFirstName = htmlspecialchars($firstName ?: 'there');
                $message = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light only">
<title>Welcome to Mythos Events</title></head>
<body style="margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0D0B1A;padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;">

        <!-- Header -->
        <tr>
          <td align="center" style="padding:40px 0 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#9B6FD0;text-transform:uppercase;">You Are Subscribed</p>
            <h1 style="margin:16px 0 0;font-family:Georgia,serif;font-size:42px;font-weight:900;color:#FFFFFF;letter-spacing:2px;text-shadow:none;">Mythos<span style="color:#E8C547;">✦</span>Events</h1>
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;">

            <h2 style="margin:0 0 16px;font-family:Georgia,serif;font-size:26px;font-weight:600;color:#FFFFFF;line-height:1.2;">Welcome, ' . $safeFirstName . '!</h2>
            <p style="margin:0 0 20px;font-size:16px;color:#C4A8E8;line-height:1.7;">Thank you for subscribing to Mythos Events. You will now receive updates about upcoming festivals, events, and opportunities to get involved.</p>
            <p style="margin:0;font-size:16px;color:#C4A8E8;line-height:1.7;">If you would like to join our talent pool, become a vendor, or explore other ways to participate, you can apply any time.</p>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td align="center" style="padding:32px 0 0;">
            <p style="margin:0 0 8px;font-size:12px;color:rgba(196,168,232,0.4);letter-spacing:2px;text-transform:uppercase;">Mythos Events &nbsp;·&nbsp; Glendale, Arizona</p>
            <p style="margin:0;font-size:12px;color:rgba(196,168,232,0.3);">Problems? Email <a href="mailto:wadehawkins@mythosevents.com" style="color:rgba(196,168,232,0.5);">wadehawkins@mythosevents.com</a></p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: wadehawkins@mythosevents.com\r\n";

                mail($email, $subject, $message, $headers);
            } else {
                $statusType = 'error';
                $status = "Something went wrong. Please try again.";
            }
        }
    } else {
        $statusType = 'error';
        $status = "Please enter a valid email address.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscribe to Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --card:       #201C32;
    --purple:     #6B3FA0;
    --purple-lt:  #9B6FD0;
    --purple-dim: rgba(107,63,160,0.25);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
    --green:      #52C87A;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden;
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
  .nav-logo span { color: #E8C547; }
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; transition: color 0.2s; }
  .nav-back:hover { color: var(--white); }
  main {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 60px 20px; position: relative; z-index: 1;
  }
  .subscribe-wrap { width: 100%; max-width: 480px; }
  .subscribe-header { text-align: center; margin-bottom: 40px; }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px;
  }
  .subscribe-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(32px, 5vw, 52px); color: var(--white);
    line-height: 1.1; margin-bottom: 16px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .subscribe-header p {
    font-size: 16px; color: var(--muted);
    margin: 0 auto; line-height: 1.7;
  }
  .form-card {
    background: var(--card);
    border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 48px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .field { margin-bottom: 24px; }
  label {
    display: block;
    font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.2em; color: var(--purple-lt);
    margin-bottom: 10px;
  }
  input[type="text"],
  input[type="email"] {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--purple-dim);
    border-radius: 8px;
    padding: 16px 18px;
    font-size: 16px; font-family: 'Inter', sans-serif;
    color: var(--white);
    outline: none;
    transition: border-color 0.2s, background 0.2s;
  }
  input:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder { color: var(--muted); }
  .submit-btn {
    width: 100%;
    background: var(--purple);
    color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px;
    letter-spacing: 0.15em;
    padding: 16px 32px;
    border: none; border-radius: 8px;
    cursor: pointer; margin-top: 8px;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-message {
    margin-top: 24px; padding: 16px; border-radius: 8px; font-size: 15px;
    text-align: center;
  }
  .status-message.success {
    background: rgba(82,200,122,0.15); border: 1px solid rgba(82,200,122,0.4); color: var(--green);
  }
  .status-message.error {
    background: rgba(224,85,85,0.15); border: 1px solid rgba(224,85,85,0.4); color: #E05555;
  }
  .form-note {
    text-align: center; margin-top: 20px;
    font-size: 13px; color: var(--muted);
  }
  footer {
    position: relative; z-index: 1;
    text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim);
    font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }
  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 32px 24px; }
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
  <div class="subscribe-wrap">

    <div class="subscribe-header">
      <div class="eyebrow">Stay In the Loop</div>
      <h1>Subscribe to Mythos</h1>
      <p>Get updates on upcoming festivals, events, and ways to get involved.</p>
    </div>

    <?php if ($status): ?>
      <div class="form-card">
        <div class="status-message <?php echo htmlspecialchars($statusType); ?>">
          <?php echo htmlspecialchars($status); ?>
        </div>
        <div style="text-align: center; margin-top: 24px;">
          <a href="/" style="color: var(--purple-lt); text-decoration: none;">← Back to Home</a>
        </div>
      </div>
    <?php else: ?>
      <div class="form-card">
        <form action="index.php" method="post">
          <div class="field">
            <label for="firstName">First Name <span style="font-size: 11px; color: var(--muted);">optional</span></label>
            <input type="text" id="firstName" name="firstName"
              placeholder="Your name (optional)">
          </div>
          <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
              placeholder="your@email.com" required autofocus>
          </div>
          <button type="submit" class="submit-btn">Subscribe ✦</button>
        </form>
        <p class="form-note">We respect your inbox. Expect updates about upcoming events, opportunities to get involved, and occasional special announcements. Unsubscribe anytime.</p>
      </div>
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
