<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Mythos Events</title>
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
  .wrap { width: 100%; max-width: 440px; }
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
  .field:last-of-type { margin-bottom: 0; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px; }
  input[type="email"], input[type="password"] {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 16px 18px; font-size: 16px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s;
  }
  input:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder { color: var(--muted); }
  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 8px;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .form-note { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }
  .form-note a { color: var(--purple-lt); text-decoration: none; }
  .form-note a:hover { text-decoration: underline; }
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
      <div class="eyebrow">Welcome Back</div>
      <h1>Login</h1>
      <p>Sign in to manage your applications and events.</p>
    </div>

    <div class="form-card">
      <form action="LoginProcess.php" method="post">
        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="your@email.com" required autofocus>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Your password" required>
        </div>
        <button type="submit" class="submit-btn">Login ✦</button>
      </form>
      <p class="form-note"><a href="ForgotPassword.php">Forgot Password?</a></p>
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
