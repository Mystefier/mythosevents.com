<?php
$status = '';
$statusType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dbname = "db9dh4gg0yfw3q";
    include('../join/logintodatabase.php');

    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $businessName = isset($_POST["businessName"]) ? mysqli_real_escape_string($conn, $_POST["businessName"]) : '';
    $firstName = isset($_POST["firstName"]) ? mysqli_real_escape_string($conn, $_POST["firstName"]) : '';
    $lastName = isset($_POST["lastName"]) ? mysqli_real_escape_string($conn, $_POST["lastName"]) : '';
    $phoneNumber = isset($_POST["phoneNumber"]) ? mysqli_real_escape_string($conn, $_POST["phoneNumber"]) : '';
    $website = isset($_POST["website"]) ? mysqli_real_escape_string($conn, $_POST["website"]) : '';
    $message = isset($_POST["message"]) ? mysqli_real_escape_string($conn, $_POST["message"]) : '';

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email && $businessName) {
        $insertSql = "INSERT INTO people (email, first, last, phone, business_name, website, message, roles, involvement_type)
                      VALUES ('$email', '$firstName', '$lastName', '$phoneNumber', '$businessName', '$website', '$message', 'Venue', 'Venue')";

        if (mysqli_query($conn, $insertSql)) {
            $statusType = 'success';
            $status = "Thanks for reaching out! We've received your info for <strong>" . htmlspecialchars($businessName) . "</strong> and will be in touch soon to talk about bringing Mythos Events to your venue.";

            // Confirmation to the business
            $subject = "Thanks for Your Interest — Mythos Events";
            $bodyMsg = "
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='max-width:560px;width:100%;background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:40px;'>
<tr><td>
<h1 style='margin:0 0 16px;font-family:Georgia,serif;font-size:26px;color:#FFFFFF;'>Thanks, " . htmlspecialchars($firstName ?: 'there') . "!</h1>
<p style='margin:0 0 16px;font-size:15px;color:#C4A8E8;line-height:1.7;'>We received your interest in bringing Mythos Events to <strong>" . htmlspecialchars($businessName) . "</strong>. Our team will review your info and reach out soon to talk through what a partnership could look like.</p>
<p style='margin:0;font-size:15px;color:#C4A8E8;line-height:1.7;'>Questions in the meantime? Just reply to this email or reach us at <a href='mailto:wade@mythosevents.com' style='color:#9B6FD0;'>wade@mythosevents.com</a>.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Events@Mythos.com\r\n";
            mail($email, $subject, $bodyMsg, $headers);

            // Notify the team of a new venue lead
            $teamSubject = "New Venue Lead: " . $businessName;
            $teamBody = "New venue inquiry submitted:\n\nBusiness: $businessName\nContact: $firstName $lastName\nEmail: $email\nPhone: $phoneNumber\nWebsite: $website\nMessage: $message";
            mail("wade@mythosevents.com", $teamSubject, $teamBody);
        } else {
            $statusType = 'error';
            $status = "Something went wrong submitting your info. Please try again, or email us directly at wade@mythosevents.com.";
        }
    } else {
        $statusType = 'error';
        $status = "Please enter your business name and a valid email address.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bring Mythos Events to Your Venue</title>
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
  .hero {
    text-align: center; padding: 70px 20px 50px; max-width: 760px; margin: 0 auto;
  }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px;
  }
  .hero h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(30px, 5vw, 50px); color: var(--white);
    line-height: 1.15; margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .hero p { font-size: 16px; color: var(--muted); max-width: 600px; margin: 0 auto; }

  /* VALUE PROPS */
  .value-section { padding: 40px 20px 70px; }
  .value-wrap { max-width: 960px; margin: 0 auto; }
  .value-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
  .value-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 30px 26px; text-align: center;
  }
  .value-icon { font-size: 30px; margin-bottom: 16px; }
  .value-card h3 { font-family: 'Cinzel', serif; font-size: 16px; color: var(--white); margin-bottom: 10px; }
  .value-card p { font-size: 14px; color: var(--muted); line-height: 1.7; }

  /* HOW IT WORKS */
  .how-section { padding: 20px 20px 70px; border-top: 1px solid var(--purple-dim); }
  .how-wrap { max-width: 900px; margin: 0 auto; padding-top: 50px; }
  .section-title {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(24px, 3.5vw, 34px); color: var(--white);
    text-align: center; margin-bottom: 44px;
  }
  .steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
  .step-item { text-align: center; }
  .step-number {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--purple-dim); border: 1px solid var(--purple);
    font-family: 'Cinzel', serif; font-weight: 900; color: var(--gold);
    margin-bottom: 16px; font-size: 15px;
  }
  .step-item h3 { font-family: 'Cinzel', serif; font-size: 16px; color: var(--white); margin-bottom: 10px; }
  .step-item p { font-size: 14px; color: var(--muted); line-height: 1.7; }

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
  input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 14px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s; resize: vertical;
  }
  input:focus, textarea:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { min-height: 100px; }
  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer;
    margin-top: 8px; transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-message {
    margin-top: 24px; padding: 16px; border-radius: 8px; font-size: 15px; text-align: center;
  }
  .status-message.success { background: rgba(82,200,122,0.15); border: 1px solid rgba(82,200,122,0.4); color: var(--green); }
  .status-message.error { background: rgba(224,85,85,0.15); border: 1px solid rgba(224,85,85,0.4); color: var(--red); }

  footer {
    position: relative; z-index: 1; text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--white); }

  @media (max-width: 800px) {
    .value-grid, .steps-grid { grid-template-columns: 1fr; gap: 24px; }
  }
  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 32px 24px; }
    .field-row { grid-template-columns: 1fr; }
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
    <div class="eyebrow">For Venues & Businesses</div>
    <h1>Bring the Mythos to Your Venue</h1>
    <p>Turn your space into a destination. We bring performers, artists, and immersive entertainment to bars, restaurants, event spaces, and community venues — giving your customers a reason to show up, stay longer, and come back.</p>
  </div>

  <div class="value-section">
    <div class="value-wrap">
      <div class="value-grid">
        <div class="value-card">
          <div class="value-icon">📈</div>
          <h3>Attract New Customers</h3>
          <p>Live entertainment draws people who might never have walked through your door — and gives your regulars a reason to come back more often.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">✨</div>
          <h3>Stand Out</h3>
          <p>Immersive performers, artists, and interactive experiences set your venue apart from every other bar, restaurant, or shop on the block.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🎯</div>
          <h3>We Handle the Talent</h3>
          <p>You don't have to find, vet, or book performers yourself. We draw from our own growing talent pool and match the right fit for your space.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="how-section">
    <div class="how-wrap">
      <div class="section-title">How a Partnership Works</div>
      <div class="steps-grid">
        <div class="step-item">
          <div class="step-number">1</div>
          <h3>Tell Us About Your Venue</h3>
          <p>What kind of space you have, your typical crowd, and what you're hoping to get out of it.</p>
        </div>
        <div class="step-item">
          <div class="step-number">2</div>
          <h3>We Match You With Talent</h3>
          <p>Performers, artists, or workshop leaders from our network who fit your venue and audience.</p>
        </div>
        <div class="step-item">
          <div class="step-number">3</div>
          <h3>Host a Night to Remember</h3>
          <p>We help coordinate the details so the event runs smoothly — and your customers remember it.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="form-section">
    <div class="form-wrap">
      <div class="section-title">Get in Touch</div>

      <?php if ($status): ?>
        <div class="form-card">
          <div class="status-message <?php echo htmlspecialchars($statusType); ?>">
            <?php echo $status; ?>
          </div>
          <div style="text-align: center; margin-top: 24px;">
            <a href="/" style="color: var(--purple-lt); text-decoration: none;">← Back to Home</a>
          </div>
        </div>
      <?php else: ?>
        <div class="form-card">
          <form action="index.php" method="post">
            <div class="field">
              <label for="businessName">Business / Venue Name</label>
              <input type="text" id="businessName" name="businessName" placeholder="Your business name" required>
            </div>
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
                <input type="email" id="email" name="email" placeholder="you@business.com" required>
              </div>
              <div class="field">
                <label for="phoneNumber">Phone <span class="label-optional">optional</span></label>
                <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="(555) 000-0000">
              </div>
            </div>
            <div class="field">
              <label for="website">Website or Social Link <span class="label-optional">optional</span></label>
              <input type="url" id="website" name="website" placeholder="https://yourbusiness.com">
            </div>
            <div class="field">
              <label for="message">Tell Us About Your Venue <span class="label-optional">optional</span></label>
              <textarea id="message" name="message" placeholder="What kind of venue is it? What's your typical crowd like? What are you hoping to bring in?"></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Inquiry ✦</button>
          </form>
        </div>
      <?php endif; ?>

    </div>
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
</script>
</body>
</html>
