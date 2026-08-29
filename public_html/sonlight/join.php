<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Join Sonlight Drama Team</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --sun-bg: #FFF6E9;
    --sun-card: #FFFFFF;
    --sun-primary: #FF7B4F;
    --sun-primary-dk: #E86335;
    --sun-secondary: #2EA9C8;
    --sun-gold: #FFC145;
    --sun-text: #3A2E2A;
    --sun-muted: #8C7B72;
    --sun-border: #F0DFC8;
  }
  body {
    background: var(--sun-bg); color: var(--sun-text);
    font-family: 'Nunito', sans-serif; font-size: 17px; line-height: 1.7;
    min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; }

  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: var(--sun-card); border-bottom: 2px solid var(--sun-border); position: sticky; top: 0; z-index: 10;
  }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 20px; color: var(--sun-text); text-decoration: none; }
  .nav-logo span { color: var(--sun-primary); }
  .nav-right { display: flex; align-items: center; gap: 20px; }
  .nav-right a { font-size: 14px; color: var(--sun-muted); text-decoration: none; font-weight: 700; }
  .nav-right a:hover { color: var(--sun-primary); }
  .nav-clock { font-family: 'Poppins', sans-serif; font-size: 13px; letter-spacing: 0.06em; color: var(--sun-muted); min-width: 58px; text-align: right; white-space: nowrap; }

  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; }
  .card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 20px;
    padding: 48px 44px; max-width: 480px; width: 100%; box-shadow: 0 12px 40px rgba(255,123,79,0.08);
  }

  .sun-badge {
    width: 56px; height: 56px; border-radius: 50%;
    background: #FFF3E8; border: 2px solid var(--sun-gold);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; margin-bottom: 20px;
  }
  .eyebrow {
    font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11px; letter-spacing: 0.2em;
    color: var(--sun-primary); text-transform: uppercase; margin-bottom: 10px;
  }
  h1 { font-weight: 900; font-size: 28px; color: var(--sun-text); margin-bottom: 10px; line-height: 1.2; }
  .subtitle { font-size: 15px; color: var(--sun-muted); margin-bottom: 32px; line-height: 1.6; }

  label { display: block; font-weight: 700; font-size: 14px; margin-bottom: 8px; color: var(--sun-text); }
  input[type="email"] {
    width: 100%; padding: 14px 16px; border: 2px solid var(--sun-border); border-radius: 10px;
    font-family: 'Nunito', sans-serif; font-size: 15px; color: var(--sun-text); background: var(--sun-bg);
    transition: border-color 0.2s;
  }
  input[type="email"]:focus { outline: none; border-color: var(--sun-primary); }

  .btn {
    display: block; width: 100%; margin-top: 20px;
    background: var(--sun-primary); color: #fff; border: none;
    padding: 15px 20px; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px;
    border-radius: 30px; cursor: pointer; transition: background 0.2s, transform 0.15s; text-align: center;
  }
  .btn:hover { background: var(--sun-primary-dk); transform: translateY(-2px); }

  .steps {
    margin-top: 28px; border-top: 2px solid var(--sun-border); padding-top: 24px;
    display: flex; flex-direction: column; gap: 12px;
  }
  .step-row { display: flex; gap: 12px; align-items: flex-start; }
  .step-num {
    width: 24px; height: 24px; border-radius: 50%; background: #FFF3E8; border: 2px solid var(--sun-gold);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 700; color: var(--sun-primary);
    flex-shrink: 0; margin-top: 2px;
  }
  .step-row span { font-size: 13px; color: var(--sun-muted); line-height: 1.5; }

  footer {
    text-align: center; padding: 24px; border-top: 2px solid var(--sun-border);
    font-size: 13px; color: var(--sun-muted);
  }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }

  @media (max-width: 560px) {
    nav { padding: 0 20px; }
    .card { padding: 32px 24px; }
  }
</style>
</head>
<body>

<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
  <div class="nav-right">
    <a href="/join/login.php">Log In</a>
    <div class="nav-clock" id="navClock">--:--</div>
  </div>
</nav>

<main>
  <div class="card">
    <div class="sun-badge">☀️</div>
    <div class="eyebrow">Sonlight Drama Team</div>
    <h1>Join the Team</h1>
    <p class="subtitle">Enter your email and we'll send you a confirmation link to set up your profile. Takes about two minutes.</p>

    <form action="/join/email.php" method="post">
      <input type="hidden" name="source" value="sonlight">
      <div>
        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <button type="submit" class="btn">Send Confirmation Link ☀️</button>
    </form>

    <div class="steps">
      <div class="step-row">
        <div class="step-num">1</div>
        <span>Check your inbox for a confirmation email from <strong>confirm@mythosevents.com</strong></span>
      </div>
      <div class="step-row">
        <div class="step-num">2</div>
        <span>Click the link to open your profile form</span>
      </div>
      <div class="step-row">
        <div class="step-num">3</div>
        <span>Fill in your name and a little about yourself — you're in!</span>
      </div>
    </div>
  </div>
</main>

<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; part of <a href="/">Mythos Events</a> &nbsp;·&nbsp; Already have an account? <a href="/join/login.php">Log in</a></p>
</footer>

<script>
(function() {
  const clockEl = document.getElementById('navClock');
  if (!clockEl) return;
  function tick() {
    const now = new Date(), m = String(now.getMinutes()).padStart(2,'0');
    let h = now.getHours(); const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
    clockEl.textContent = h + ':' + m + ' ' + ampm;
  }
  tick(); setInterval(tick, 1000);
})();
</script>
</body>
</html>
