<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Get Booked — Mythos Events</title>
<meta name="description" content="Mythos Events pitches you to venues actively looking for talent — performers, artists, and workshop leaders get matched to the right stage without the self-promotion hustle.">
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
  .hero-cta { margin-top: 32px; }

  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 16px 40px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 15px; letter-spacing: 0.12em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }

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

  /* WHO */
  .who-section { padding: 20px 20px 70px; border-top: 1px solid var(--purple-dim); }
  .who-wrap { max-width: 800px; margin: 0 auto; padding-top: 50px; text-align: center; }
  .who-pills { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 28px; }
  .pill {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 40px; padding: 9px 20px; font-size: 14px; color: var(--muted);
  }

  /* CTA */
  .showcase-section { padding: 20px 20px 80px; border-top: 1px solid var(--purple-dim); }
  .showcase-wrap { max-width: 700px; margin: 0 auto; padding-top: 50px; text-align: center; }
  .showcase-wrap p { font-size: 15px; color: var(--muted); line-height: 1.75; max-width: 560px; margin: 0 auto 32px; }

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
    <div class="eyebrow">For Performers &amp; Artists</div>
    <h1>Get Booked. Get Seen.</h1>
    <p>Venues in our network are actively looking for performers, artists, and workshop leaders. Join Mythos Events and we'll pitch you directly — no cold emails, no chasing bookings, no self-promotion hustle.</p>
    <div class="hero-cta">
      <a href="/join/" class="btn-primary">Join as Talent ✦</a>
    </div>
  </div>

  <div class="value-section">
    <div class="value-wrap">
      <div class="value-grid">
        <div class="value-card">
          <div class="value-icon">🎯</div>
          <h3>We Do the Pitching</h3>
          <p>You don't have to cold-call venues or beg for stage time. We already know what venues in our network are looking for — we match your act to the right room.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🌱</div>
          <h3>A Growing Network</h3>
          <p>Every venue we add expands the number of places you could get booked. The bigger the network gets, the more opportunities come your way.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🎪</div>
          <h3>Real Rooms, Real Audiences</h3>
          <p>From intimate venue nights to full festivals like Shadowfest, you're performing in front of audiences who came specifically to see people like you.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="how-section">
    <div class="how-wrap">
      <div class="section-title">How It Works</div>
      <div class="steps-grid">
        <div class="step-item">
          <div class="step-number">1</div>
          <h3>Join & Tell Us Your Act</h3>
          <p>Sign up as talent, describe what you do, and set the areas you're willing to travel to.</p>
        </div>
        <div class="step-item">
          <div class="step-number">2</div>
          <h3>We Match You to Venues</h3>
          <p>When a venue in our network fits what you do, we reach out to you directly — no applications, no waiting rooms.</p>
        </div>
        <div class="step-item">
          <div class="step-number">3</div>
          <h3>You Show Up & Perform</h3>
          <p>Focus on your craft. We handle the introductions, the logistics, and the follow-up.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="who-section">
    <div class="who-wrap">
      <div class="section-title">Who This Is For</div>
      <p style="color:var(--muted);font-size:15px;">Any kind of performer or maker who wants an audience, not just a following.</p>
      <div class="who-pills">
        <span class="pill">🎭 Performers</span>
        <span class="pill">🎨 Artists</span>
        <span class="pill">🎪 Workshop Leaders</span>
        <span class="pill">🎻 Musicians</span>
        <span class="pill">✨ Anyone With an Act</span>
      </div>
    </div>
  </div>

  <div class="showcase-section">
    <div class="showcase-wrap">
      <div class="section-title">Ready to Get Booked?</div>
      <p>Signing up takes less than two minutes. Tell us what you do, and we'll start looking for the right stage.</p>
      <a href="/join/" class="btn-primary">Join as Talent ✦</a>
    </div>
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

  // Affiliate/referral tracking — carry ?id= through to the join form
  (function() {
    const params = new URLSearchParams(window.location.search);
    let affId = params.get('id');
    if (affId && /^\d+$/.test(affId)) {
      sessionStorage.setItem('mythos_ref_id', affId);
    } else {
      affId = sessionStorage.getItem('mythos_ref_id');
    }
    if (affId && /^\d+$/.test(affId)) {
      document.querySelectorAll('a[href="/join/"]').forEach(a => {
        a.href = '/join/?id=' + encodeURIComponent(affId);
      });
    }
  })();
</script>
</body>
</html>
