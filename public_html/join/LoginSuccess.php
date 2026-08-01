<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome — Mythos Events</title>
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
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
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
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 40px);
    color: var(--white); margin-bottom: 16px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .subtitle { font-size: 15px; color: var(--muted); margin-bottom: 12px; }
  .email-tag { display: inline-block; font-size: 13px; color: var(--purple-lt); background: rgba(107,63,160,0.15); border: 1px solid var(--purple-dim); border-radius: 999px; padding: 6px 16px; margin-bottom: 32px; }
  .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .btn-primary {
    display: inline-block; width: 100%; background: var(--purple); color: var(--white);
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .btn-danger {
    display: inline-block; width: 100%; background: transparent; color: var(--red);
    padding: 14px 32px; border: 1px solid rgba(220,80,80,0.4); border-radius: 8px; cursor: pointer;
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.12em; margin-top: 16px;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-danger:hover { background: rgba(220,80,80,0.12); transform: translateY(-2px); }
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
    <div class="status-icon">✦</div>
    <h1>Welcome to the Events Team!</h1>
    <p class="subtitle">You are now logged in.</p>
    <p class="email-tag"><?php echo htmlspecialchars($email); ?></p>

    <div class="form-card">
      <form action="editApplication.php" method="post" style="margin-bottom: 16px;">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <button type="submit" class="btn-primary">Edit Profile ✦</button>
      </form>

      <button id="deleteAccountBtn" class="btn-danger">Delete My Account</button>
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

  document.getElementById('deleteAccountBtn').addEventListener('click', function() {
    var confirmDelete = confirm("Are you sure you want to delete your account? This action cannot be undone.");
    if (confirmDelete) {
      var userEmail = '<?php echo $email; ?>';
      window.location.href = 'delete_account2.php?email=' + userEmail;
    }
  });
</script>

</body>
</html>
