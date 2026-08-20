<?php
$status = '';
$statusType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dbname = "db9dh4gg0yfw3q";
    include('../join/logintodatabase.php');

    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $firstName = isset($_POST["firstName"]) ? $_POST["firstName"] : '';
    $lastName = isset($_POST["lastName"]) ? $_POST["lastName"] : '';
    $phoneNumber = isset($_POST["phoneNumber"]) ? $_POST["phoneNumber"] : '';
    $interests = isset($_POST["interest"]) ? $_POST["interest"] : [];
    $availability = isset($_POST["availability"]) ? $_POST["availability"] : '';

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    $interestLabels = [];
    foreach ($interests as $i) {
        if ($i === 'build') $interestLabels[] = 'Pirate Walk Through - Build Crew';
        if ($i === 'perform') $interestLabels[] = 'Pirate Walk Through - Performer';
    }
    $roleText = implode(', ', $interestLabels);

    if ($email && $roleText) {
        $checkStmt = mysqli_prepare($conn, "SELECT id, roles FROM people WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $existing = mysqli_fetch_assoc($result);
        mysqli_stmt_close($checkStmt);

        if ($existing) {
            // Already in the system — append the new interest to their roles if not already there
            $existingRoles = $existing['roles'] ? array_map('trim', explode(',', $existing['roles'])) : [];
            foreach ($interestLabels as $label) {
                if (!in_array($label, $existingRoles)) {
                    $existingRoles[] = $label;
                }
            }
            $mergedRoles = implode(', ', $existingRoles);
            $updateStmt = mysqli_prepare($conn, "UPDATE people SET roles = ?, message = CONCAT(COALESCE(message, ''), '\n[Pirate Walk Through] ', ?) WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "ssi", $mergedRoles, $availability, $existing['id']);
            $success = mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        } else {
            $insertStmt = mysqli_prepare($conn, "INSERT INTO people (email, first, last, phone, roles, message, involvement_type) VALUES (?, ?, ?, ?, ?, ?, 'Talent')");
            $msgWithTag = '[Pirate Walk Through] ' . $availability;
            mysqli_stmt_bind_param($insertStmt, "ssssss", $email, $firstName, $lastName, $phoneNumber, $roleText, $msgWithTag);
            $success = mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);
        }

        if ($success) {
            $statusType = 'success';
            $status = "You're signed up! We'll be in touch with details as we get closer to October 31st.";

            $subject = "You're In — Pirate Walk Through at Horizons Community Church";
            $safeFirstName = htmlspecialchars($firstName ?: 'there');
            $safeInterest = htmlspecialchars($roleText);
            $bodyMsg = "
<html>
<head><meta charset='UTF-8'><meta name='color-scheme' content='light only'><meta name='supported-color-schemes' content='light only'></head>
<body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='max-width:560px;width:100%;background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:40px;'>
<tr><td>
<h1 style='margin:0 0 16px;font-family:Georgia,serif;font-size:26px;color:#FFFFFF;'>Ahoy, $safeFirstName!</h1>
<p style='margin:0 0 16px;font-size:15px;color:#C4A8E8;line-height:1.7;'>You're signed up to help with the <strong>Pirate Walk Through</strong> at Horizons Community Church's Trunk or Treat on <strong>Saturday, October 31st at 6:00 PM</strong>.</p>
<p style='margin:0 0 16px;font-size:15px;color:#C4A8E8;line-height:1.7;'>You signed up for: $safeInterest</p>
<p style='margin:0;font-size:15px;color:#C4A8E8;line-height:1.7;'>We'll follow up with more details as the date gets closer. Questions in the meantime? Reply to this email or reach us at <a href='mailto:wadehawkins@mythosevents.com' style='color:#9B6FD0;'>wadehawkins@mythosevents.com</a>.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: wadehawkins@mythosevents.com\r\n";
            mail($email, $subject, $bodyMsg, $headers);

            $teamSubject = "Pirate Walk Through signup: " . $firstName . " " . $lastName;
            $teamBody = "Name: $firstName $lastName\nEmail: $email\nPhone: $phoneNumber\nInterest: $roleText\nAvailability/notes: $availability";
            mail("wadehawkins@mythosevents.com", $teamSubject, $teamBody);
        } else {
            $statusType = 'error';
            $status = "Something went wrong. Please try again, or email wadehawkins@mythosevents.com directly.";
        }
    } else {
        $statusType = 'error';
        $status = "Please enter a valid email and select at least one way you'd like to help.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pirate Walk Through — Trunk or Treat at Horizons Community Church</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
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
  html { scroll-behavior: smooth; }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
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
  .nav-logo span { color: var(--gold); }
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; transition: color 0.2s; }
  .nav-back:hover { color: var(--white); }
  main { flex: 1; position: relative; z-index: 1; }

  /* HERO */
  .hero { text-align: center; padding: 70px 20px 40px; max-width: 760px; margin: 0 auto; }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--gold); margin-bottom: 16px;
  }
  .hero h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(32px, 5.5vw, 54px); color: var(--white);
    line-height: 1.1; margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .hero p { font-size: 16px; color: var(--muted); max-width: 600px; margin: 0 auto; }

  /* EVENT DETAILS CARD */
  .event-card {
    max-width: 640px; margin: 36px auto 0;
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 28px 32px;
    display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .event-detail { text-align: center; min-width: 140px; }
  .event-detail .label { font-family: 'Cinzel Decorative', serif; font-size: 9px; letter-spacing: 0.25em; color: var(--purple-lt); text-transform: uppercase; margin-bottom: 6px; }
  .event-detail .value { font-size: 14px; color: var(--white); }
  .event-note { max-width: 640px; margin: 16px auto 0; text-align: center; font-size: 12px; color: var(--muted); }
  .event-note a { color: var(--purple-lt); }

  /* ABOUT */
  .about-section { padding: 60px 20px; }
  .about-wrap { max-width: 760px; margin: 0 auto; text-align: center; }
  .section-title {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(24px, 3.5vw, 34px); color: var(--white);
    margin-bottom: 20px;
  }
  .about-wrap p { font-size: 15px; color: var(--muted); line-height: 1.8; max-width: 640px; margin: 0 auto; }

  /* GET INVOLVED */
  .involved-section { padding: 20px 20px 70px; border-top: 1px solid var(--purple-dim); }
  .involved-wrap { max-width: 900px; margin: 0 auto; padding-top: 50px; }
  .involved-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-top: 40px; }
  .involved-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 32px; text-align: center;
  }
  .involved-icon { font-size: 34px; margin-bottom: 16px; }
  .involved-card h3 { font-family: 'Cinzel', serif; font-size: 18px; color: var(--white); margin-bottom: 12px; }
  .involved-card p { font-size: 14px; color: var(--muted); line-height: 1.7; }

  /* FORM */
  .form-section { padding: 20px 20px 80px; border-top: 1px solid var(--purple-dim); }
  .form-wrap { max-width: 560px; margin: 0 auto; padding-top: 50px; }
  .form-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 44px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .field { margin-bottom: 22px; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  label {
    display: block; font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 8px;
  }
  .label-optional { font-family: 'Inter', sans-serif; font-size: 10px; color: var(--muted); letter-spacing: 0; margin-left: 6px; font-style: italic; }
  input[type="text"], input[type="email"], input[type="tel"], textarea {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 14px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s; resize: vertical;
  }
  input:focus, textarea:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { min-height: 90px; }

  .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px; }
  .checkbox-item {
    display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03);
    border: 1px solid var(--purple-dim); border-radius: 8px; padding: 14px 16px;
    cursor: pointer; transition: background 0.15s, border-color 0.15s;
  }
  .checkbox-item:hover { background: rgba(107,63,160,0.1); border-color: var(--purple); }
  .checkbox-item input[type="checkbox"] { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--gold); cursor: pointer; padding: 0; margin: 0; }
  .checkbox-item span { font-size: 14px; color: var(--lilac); line-height: 1.3; }

  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer;
    margin-top: 8px; transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-message { margin-top: 24px; padding: 16px; border-radius: 8px; font-size: 15px; text-align: center; }
  .status-message.success { background: rgba(82,200,122,0.15); border: 1px solid rgba(82,200,122,0.4); color: var(--green); }
  .status-message.error { background: rgba(224,85,85,0.15); border: 1px solid rgba(224,85,85,0.4); color: var(--red); }

  footer {
    position: relative; z-index: 1; text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--white); }

  @media (max-width: 700px) {
    .involved-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 32px 24px; }
    .field-row, .checkbox-grid { grid-template-columns: 1fr; }
    .event-card { padding: 24px 20px; }
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

  <div class="hero">
    <div class="eyebrow">A Mythos Events Production</div>
    <h1>The Pirate Walk Through</h1>
    <p>Mythos Events is bringing an immersive pirate walk-through experience to Horizons Community Church's Trunk or Treat — a free, family-friendly Halloween night for the Peoria community.</p>
  </div>

  <div class="event-card">
    <div class="event-detail">
      <div class="label">Date</div>
      <div class="value">Sat, October 31</div>
    </div>
    <div class="event-detail">
      <div class="label">Time</div>
      <div class="value">6:00 PM</div>
    </div>
    <div class="event-detail">
      <div class="label">Location</div>
      <div class="value">Horizons Community Church<br>14120 N 79th Ave, Peoria, AZ</div>
    </div>
    <div class="event-detail">
      <div class="label">Cost</div>
      <div class="value">Free</div>
    </div>
  </div>
  <p class="event-note">Hosted by <a href="https://horizonscc.com" target="_blank" rel="noopener">Horizons Community Church</a> — full event details on their site.</p>

  <div class="about-section">
    <div class="about-wrap">
      <div class="section-title">A Safer, More Magical Halloween</div>
      <p>Trunk or Treat is a free, less-scary alternative to trick-or-treating — cotton candy, a playground, and cars full of candy for families in the community. The Pirate Walk Through adds an immersive, story-driven attraction to the night: sets, characters, and an experience kids and families won't forget.</p>
      <p style="margin-top: 20px;">This isn't a one-off idea — the Pirate Walk Through has been part of Horizons Community Church's Trunk or Treat for several years running, always on Halloween night. It's happening again this year, which makes it the soonest, most reliable way to get involved with Mythos Events and see what we're building firsthand.</p>
    </div>
  </div>

  <div class="involved-section">
    <div class="involved-wrap">
      <div class="section-title">We Need Your Help</div>
      <p style="text-align: center; max-width: 600px; margin: 0 auto; font-size: 15px; color: var(--muted); line-height: 1.8;">No experience necessary for either role — just show up ready to help. This is a great low-pressure way to get a feel for working with Mythos Events before committing to anything bigger.</p>
      <div class="involved-grid">
        <div class="involved-card">
          <div class="involved-icon">🔨</div>
          <h3>Build Crew</h3>
          <p>Help design and build the sets, props, and decor that bring the walk-through to life before Halloween night — carpentry, painting, prop-making, set dressing, and general hands-on problem solving. Work happens in the weeks leading up to October 31st, and you can pitch in as much or as little time as you're able.</p>
        </div>
        <div class="involved-card">
          <div class="involved-icon">🏴‍☠️</div>
          <h3>Performers</h3>
          <p>Play a pirate character and bring the walk-through to life for families on the night of October 31st. Stay in character, improvise with kids, and help create an experience people remember. Performers, improvisers, cosplayers, and anyone who loves a good costume are all welcome — this is a fun, family-friendly crowd, not a haunted house.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-wrap">
      <div class="section-title">Sign Up to Help</div>

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
            <div class="field-row">
              <div class="field">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" placeholder="First name" required>
              </div>
              <div class="field">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" placeholder="Last name" required>
              </div>
            </div>
            <div class="field-row">
              <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" required>
              </div>
              <div class="field">
                <label for="phoneNumber">Phone <span class="label-optional">optional</span></label>
                <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="(555) 000-0000">
              </div>
            </div>
            <div class="field">
              <label>How Do You Want to Help?</label>
              <div class="checkbox-grid">
                <label class="checkbox-item">
                  <input type="checkbox" name="interest[]" value="build">
                  <span>🔨 Build Crew</span>
                </label>
                <label class="checkbox-item">
                  <input type="checkbox" name="interest[]" value="perform">
                  <span>🏴‍☠️ Performer</span>
                </label>
              </div>
            </div>
            <div class="field">
              <label for="availability">Availability or Notes <span class="label-optional">optional</span></label>
              <textarea id="availability" name="availability" placeholder="Any dates/times you're available to help build, or experience you'd like to mention"></textarea>
            </div>
            <button type="submit" class="submit-btn">Sign Up to Help ✦</button>
          </form>
        </div>
      <?php endif; ?>

    </div>
  </div>

</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
</footer>

<script>
  const container = document.getElementById('stars');
  for (let i = 0; i < 150; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }
</script>
</body>
</html>
