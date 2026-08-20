<?php
$id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Join the Mythos — Mythos Events</title>
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
  html { scroll-behavior: smooth; }
  body {
    background: var(--midnight);
    color: var(--lilac);
    font-family: 'Inter', sans-serif;
    font-size: 17px; line-height: 1.75;
    min-height: 100vh;
    display: flex; flex-direction: column;
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
    flex: 1; position: relative; z-index: 1;
  }

  /* ── TOP: hero + quick-join form ── */
  .hero-section {
    display: flex; align-items: center; justify-content: center;
    padding: 60px 20px 50px;
  }
  .join-wrap { width: 100%; max-width: 480px; }
  .join-header { text-align: center; margin-bottom: 40px; }
  .join-eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px;
  }
  .join-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(32px, 5vw, 52px); color: var(--white);
    line-height: 1.1; margin-bottom: 16px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .join-header p {
    font-size: 16px; color: var(--muted);
    margin: 0 auto; line-height: 1.7;
  }
  .role-pills {
    display: flex; flex-wrap: wrap; gap: 10px;
    justify-content: center; margin-bottom: 40px;
  }
  .pill {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 40px; padding: 7px 18px;
    font-size: 13px; color: var(--muted);
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
  input[type="email"]:focus {
    border-color: var(--purple-lt);
    background: rgba(107,63,160,0.1);
  }
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
  .submit-btn:hover {
    background: var(--purple-lt);
    transform: translateY(-2px);
  }
  .form-note {
    text-align: center; margin-top: 20px;
    font-size: 13px; color: var(--muted);
  }
  .scroll-hint {
    text-align: center; margin-top: 36px;
  }
  .scroll-hint a {
    color: var(--muted); text-decoration: none; font-size: 13px;
    letter-spacing: 0.1em; border-bottom: 1px dotted var(--purple-dim);
    padding-bottom: 3px; transition: color 0.2s;
  }
  .scroll-hint a:hover { color: var(--white); }

  /* ── BELOW: promotional / informational sections ── */
  .info-section {
    padding: 70px 20px;
    border-top: 1px solid var(--purple-dim);
  }
  .info-section.alt { background: rgba(255,255,255,0.015); }
  .info-wrap { max-width: 960px; margin: 0 auto; }
  .section-eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt);
    text-align: center; margin-bottom: 14px;
  }
  .section-title {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(24px, 3.5vw, 36px); color: var(--white);
    text-align: center; margin-bottom: 16px;
  }
  .section-sub {
    text-align: center; max-width: 620px; margin: 0 auto 48px;
    color: var(--muted); font-size: 15px; line-height: 1.75;
  }

  .category-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
  }
  .category-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 28px 24px;
    transition: transform 0.2s, border-color 0.2s;
  }
  .category-card:hover { transform: translateY(-4px); border-color: var(--purple); }
  .category-icon { font-size: 28px; margin-bottom: 14px; }
  .category-card h3 {
    font-family: 'Cinzel', serif; font-size: 16px; color: var(--white);
    margin-bottom: 10px;
  }
  .category-card p { font-size: 13.5px; color: var(--muted); line-height: 1.65; }

  .why-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
  }
  .why-item { text-align: center; }
  .why-icon { font-size: 30px; margin-bottom: 16px; }
  .why-item h3 {
    font-family: 'Cinzel', serif; font-size: 16px; color: var(--white);
    margin-bottom: 10px;
  }
  .why-item p { font-size: 14px; color: var(--muted); line-height: 1.7; }

  .steps-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
    counter-reset: step;
  }
  .step-item { position: relative; text-align: center; padding-top: 8px; }
  .step-number {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--purple-dim); border: 1px solid var(--purple);
    font-family: 'Cinzel', serif; font-weight: 900; color: var(--gold);
    margin-bottom: 16px; font-size: 15px;
  }
  .step-item h3 {
    font-family: 'Cinzel', serif; font-size: 16px; color: var(--white);
    margin-bottom: 10px;
  }
  .step-item p { font-size: 14px; color: var(--muted); line-height: 1.7; }

  .bottom-cta { text-align: center; padding: 60px 20px 70px; }
  .bottom-cta p { color: var(--muted); margin-bottom: 20px; font-size: 15px; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 15px 36px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.1em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }

  footer {
    position: relative; z-index: 1;
    text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim);
    font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--white); }

  @media (max-width: 800px) {
    .category-grid { grid-template-columns: 1fr 1fr; }
    .why-grid, .steps-grid { grid-template-columns: 1fr; gap: 36px; }
  }
  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 32px 24px; }
    .category-grid { grid-template-columns: 1fr; }
    .info-section { padding: 50px 20px; }
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

  <!-- QUICK JOIN — for people ready to go -->
  <div class="hero-section">
    <div class="join-wrap">

      <div class="join-header">
        <div class="join-eyebrow">Come Be Part of It</div>
        <h1>Join the Mythos</h1>
        <p>We're building a growing pool of performers, artists, vendors, and organizers — for local festivals and, soon, events around the world. Enter your email and we'll send you a confirmation link to get started. Takes 10 seconds.</p>
      </div>

      <div class="role-pills">
        <span class="pill">🎭 Performer</span>
        <span class="pill">🎨 Artist</span>
        <span class="pill">🛖 Vendor</span>
        <span class="pill">🌟 Volunteer</span>
        <span class="pill">🎪 Workshop Leader</span>
        <span class="pill">🎟️ Affiliate</span>
      </div>

      <div class="form-card">
        <form action="email.php" method="post">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
              placeholder="your@email.com" required autofocus>
          </div>
          <button type="submit" class="submit-btn">Send My Confirmation ✦</button>
        </form>
        <p class="form-note">We'll send you a link to complete your profile. No spam, ever.</p>
        <p class="form-note">Already a member? <a href="login.php" style="color: var(--purple-lt);">Log in</a></p>
      </div>

      <div class="scroll-hint">
        <a href="#learn-more">Want to know more first? ↓</a>
      </div>

    </div>
  </div>

  <!-- WHAT WE'RE LOOKING FOR -->
  <div class="info-section" id="learn-more">
    <div class="info-wrap">
      <div class="section-eyebrow">What We're Looking For</div>
      <div class="section-title">A Talent Pool for Every Kind of Event</div>
      <p class="section-sub">Mythos Events is building a network of people who make festivals come alive — and the businesses, organizers, and connectors who help bring it all together.</p>

      <div class="category-grid">
        <div class="category-card">
          <div class="category-icon">🎭</div>
          <h3>Talent</h3>
          <p>Performers, artists, workshop leaders, and volunteers. If you can bring a crowd to life or lend a hand behind the scenes, we want to know you.</p>
        </div>
        <div class="category-card">
          <div class="category-icon">🛖</div>
          <h3>Vendors</h3>
          <p>Artisan goods, local food, handmade crafts. Set up a booth at our events and reach a crowd that's already there to discover new things.</p>
        </div>
        <div class="category-card">
          <div class="category-icon">🎟️</div>
          <h3>Affiliates</h3>
          <p>Help spread the word and sell tickets. Every person you bring in gets tracked back to you — refer talent, vendors, or ticket buyers.</p>
        </div>
        <div class="category-card">
          <div class="category-icon">🗺️</div>
          <h3>Organizers</h3>
          <p>Want to bring Mythos Events to your own city or country? We're actively looking for people to help run events beyond Arizona — including internationally.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- WHY JOIN -->
  <div class="info-section alt">
    <div class="info-wrap">
      <div class="section-eyebrow">Why Join</div>
      <div class="section-title">Built for People Who Want In</div>

      <div class="why-grid">
        <div class="why-item">
          <div class="why-icon">🌍</div>
          <h3>Local or Worldwide</h3>
          <p>Tell us where you're based and how far you're willing to travel — down to a specific address and radius. We're expanding beyond Arizona, so your reach isn't limited to one city.</p>
        </div>
        <div class="why-item">
          <div class="why-icon">🤝</div>
          <h3>Real Connections</h3>
          <p>We're building relationships between talent, vendors, venues, and organizers — not just a list of names. When there's a fit, we reach out directly.</p>
        </div>
        <div class="why-item">
          <div class="why-icon">✨</div>
          <h3>Grow With Us</h3>
          <p>From neighborhood pop-ups to big holiday festivals, Mythos Events is growing fast. Get in early and grow along with it.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- HOW IT WORKS -->
  <div class="info-section">
    <div class="info-wrap">
      <div class="section-eyebrow">Getting Started</div>
      <div class="section-title">How It Works</div>

      <div class="steps-grid">
        <div class="step-item">
          <div class="step-number">1</div>
          <h3>Enter Your Email</h3>
          <p>Just an email address to start — no long form up front.</p>
        </div>
        <div class="step-item">
          <div class="step-number">2</div>
          <h3>Confirm & Complete Your Profile</h3>
          <p>Click the link we send you, then tell us about yourself: your roles, your service area, and a bit about what you do.</p>
        </div>
        <div class="step-item">
          <div class="step-number">3</div>
          <h3>We'll Be In Touch</h3>
          <p>When there's an event or opportunity that fits, we reach out. No spam, no noise — just real opportunities.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-cta">
    <p>Ready to get started?</p>
    <a href="#email" class="btn-primary" onclick="document.getElementById('email').focus(); return true;">Join the Mythos ✦</a>
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
