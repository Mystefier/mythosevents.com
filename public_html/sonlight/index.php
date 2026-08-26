<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sonlight Drama Team</title>
<meta name="description" content="Sonlight is a weekly drama team exploring faith and theater together — one Bible question, one theater question, every Sunday.">
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
  html { scroll-behavior: smooth; }
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
  .nav-links { display: flex; gap: 24px; align-items: center; }
  .nav-links a { color: var(--sun-muted); text-decoration: none; font-weight: 700; font-size: 14px; }
  .nav-links a:hover { color: var(--sun-primary); }
  .nav-cta {
    background: var(--sun-primary); color: #fff !important; padding: 10px 22px; border-radius: 30px;
  }
  .nav-cta:hover { background: var(--sun-primary-dk) !important; }

  main { flex: 1; }
  .wrap { max-width: 900px; margin: 0 auto; padding: 0 20px; }
  section { padding: 64px 0; }
  section.hero { padding: 80px 0 60px; text-align: center; }
  .eyebrow {
    font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 12px; letter-spacing: 0.2em;
    color: var(--sun-primary); text-transform: uppercase; margin-bottom: 16px;
  }
  h1.title { font-weight: 900; font-size: clamp(38px, 7vw, 64px); color: var(--sun-text); line-height: 1.1; margin-bottom: 20px; }
  h1.title span { color: var(--sun-primary); }
  .hero p { max-width: 560px; margin: 0 auto; color: var(--sun-muted); font-size: 17px; }
  .hero-cta { margin-top: 32px; display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
  .btn {
    display: inline-block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 14px;
    padding: 14px 30px; border-radius: 30px; text-decoration: none; transition: transform 0.15s, background 0.2s;
  }
  .btn:hover { transform: translateY(-2px); }
  .btn-primary { background: var(--sun-primary); color: #fff; }
  .btn-primary:hover { background: var(--sun-primary-dk); }
  .btn-outline { background: transparent; color: var(--sun-text); border: 2px solid var(--sun-border); }
  .btn-outline:hover { border-color: var(--sun-primary); color: var(--sun-primary); }

  .value-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
  .value-card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 18px;
    padding: 32px 26px; text-align: center;
  }
  .value-icon { font-size: 34px; margin-bottom: 16px; }
  .value-card h3 { font-size: 17px; margin-bottom: 10px; }
  .value-card p { font-size: 14px; color: var(--sun-muted); }

  .how-section { background: var(--sun-card); border-top: 2px solid var(--sun-border); border-bottom: 2px solid var(--sun-border); }
  .section-title { font-weight: 800; font-size: clamp(24px, 4vw, 32px); text-align: center; margin-bottom: 40px; }
  .steps-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
  .step-item {
    background: var(--sun-bg); border: 2px solid var(--sun-border); border-radius: 16px; padding: 24px;
  }
  .step-item .step-icon { font-size: 26px; margin-bottom: 10px; }
  .step-item h3 { font-size: 16px; margin-bottom: 8px; }
  .step-item p { font-size: 14px; color: var(--sun-muted); }

  .cta-section { text-align: center; }
  .cta-section p { max-width: 500px; margin: 0 auto 28px; color: var(--sun-muted); }

  footer {
    text-align: center; padding: 28px 20px; border-top: 2px solid var(--sun-border);
    font-size: 13px; color: var(--sun-muted);
  }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }

  @media (max-width: 700px) {
    nav { padding: 0 20px; }
    .nav-links { gap: 14px; }
    .value-grid, .steps-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
  <div class="nav-links">
    <a href="/sonlight/scheduler.php">Scheduler</a>
    <a href="/join/login.php">Log In</a>
    <a href="/join/" class="nav-cta">Join the Team</a>
  </div>
</nav>

<main>

  <section class="hero">
    <div class="wrap">
      <div class="eyebrow">A Mythos Events Group</div>
      <h1 class="title">Son<span>light</span> Drama Team</h1>
      <p>A weekly gathering to explore faith and theater side by side — one person brings a Bible question, one brings a theater question, and we dig into both together.</p>
      <div class="hero-cta">
        <a href="/sonlight/scheduler.php" class="btn btn-primary">☀️ Open the Stage Scheduler</a>
        <a href="/join/" class="btn btn-outline">Join the Team</a>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div class="value-grid">
        <div class="value-card">
          <div class="value-icon">📖</div>
          <h3>Explore Together</h3>
          <p>Each week, someone brings a Bible question that gets us thinking — and talking.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🎭</div>
          <h3>Create Together</h3>
          <p>Someone else brings a theater question, so we're always sharpening our craft too.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🤝</div>
          <h3>Grow Together</h3>
          <p>Same room, same table — faith and skill building side by side, every week.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="how-section">
    <div class="wrap">
      <div class="section-title">How a Week Works</div>
      <div class="steps-grid">
        <div class="step-item">
          <div class="step-icon">☀️</div>
          <h3>Sign Up on the Stage Scheduler</h3>
          <p>Grab an open Sunday for the Bible question, the theater question, or both — whichever you have room for. Just one upcoming date per category at a time.</p>
        </div>
        <div class="step-item">
          <div class="step-icon">🗓️</div>
          <h3>Bring It on Your Day</h3>
          <p>When your Sunday comes around, bring your question to the group and lead the discussion.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="wrap">
      <div class="section-title">Ready to Join In?</div>
      <p>Sign up through Mythos Events and check "Sonlight Drama Team" as one of your roles — takes about two minutes.</p>
      <a href="/join/" class="btn btn-primary">Join the Team ✦</a>
    </div>
  </section>

</main>

<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; part of <a href="/">Mythos Events</a> &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">Questions?</a></p>
</footer>

</body>
</html>
