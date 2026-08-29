<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Mythos Events</title>
<meta name="description" content="Mythos Events brings performers, venues, artists, and promoters together with people looking for a good time.">
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
  .nav-left { display: flex; align-items: center; gap: 24px; }
  .nav-about-link {
    color: var(--lilac); text-decoration: none; font-size: 14px;
    letter-spacing: 0.08em; transition: color 0.2s;
  }
  .nav-about-link:hover { color: var(--white); }
  .nav-links { display: flex; gap: 32px; align-items: center; }
  .nav-links a { color: var(--lilac); text-decoration: none; font-size: 14px; letter-spacing: 0.08em; transition: color 0.2s; }
  .nav-links a:hover { color: var(--white); }
  .nav-cta {
    background: var(--purple); color: var(--white) !important;
    padding: 9px 22px; border-radius: 6px; font-weight: 500;
    transition: background 0.2s !important;
  }
  .nav-cta:hover { background: var(--purple-lt) !important; color: var(--white) !important; }

  /* NAV HOVER FORMS — see /techniques/nav-hover-forms.md for the reusable pattern */
  .nav-hover { position: relative; }
  .nav-hover-panel { display: none; position: absolute; top: 100%; right: 0; padding-top: 14px; z-index: 50; }
  .nav-hover:hover .nav-hover-panel, .nav-hover:focus-within .nav-hover-panel { display: block; }
  .nav-hover-panel-inner {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 10px;
    padding: 20px; width: 240px; box-shadow: 0 12px 40px rgba(0,0,0,0.5);
  }
  .nav-hover-panel input {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 6px; padding: 10px 12px; font-size: 13px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; margin-bottom: 10px; box-sizing: border-box;
  }
  .nav-hover-panel input:focus { border-color: var(--purple-lt); }
  .nav-hover-panel input::placeholder { color: var(--muted); }
  .nav-hover-panel button {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.1em;
    padding: 11px; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s;
  }
  .nav-hover-panel button:hover { background: var(--purple-lt); }
  .nav-hover-panel .hover-link { display: block; text-align: center; margin-top: 10px; font-size: 11px; color: var(--muted) !important; text-decoration: none; }
  .nav-hover-panel .hover-link:hover { color: var(--white) !important; }
  .nav-toggle {
    display: none; background: none; border: none; cursor: pointer; padding: 8px; z-index: 101;
  }
  .nav-toggle span {
    display: block; width: 24px; height: 2px; background: var(--white); margin: 5px 0;
    border-radius: 2px;
  }
  .hover-menu-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 8px; border-radius: 6px; font-size: 13px;
    color: var(--lilac) !important; text-decoration: none; transition: background 0.15s, color 0.15s;
  }
  .hover-menu-link:hover { background: rgba(107,63,160,0.15); color: var(--white) !important; }

  /* ── NAV ACCOUNT WIDGET — see /techniques/nav-account-widget.md ── */
  .nav-account { display: flex; align-items: center; gap: 18px; }
  .nav-logged-out { display: flex; align-items: center; gap: 18px; }
  .nav-logged-in { display: none; }
  .nav-clock {
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.06em;
    color: var(--muted); min-width: 58px; text-align: right; white-space: nowrap;
  }

  main { flex: 1; position: relative; z-index: 1; }
  section { padding: 80px 0; border-top: 1px solid var(--purple-dim); }
  section:first-of-type { border-top: none; padding-top: 90px; }
  .wrap { max-width: 900px; margin: 0 auto; padding: 0 20px; }

  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; text-align: center; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(32px, 5.5vw, 54px);
    color: var(--white); line-height: 1.15; margin-bottom: 20px; text-shadow: 0 0 40px rgba(107,63,160,0.7);
    text-align: center;
  }
  h2 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(24px, 3.5vw, 34px);
    color: var(--white); margin-bottom: 24px; text-align: center;
  }
  .hero-sub { text-align: center; max-width: 640px; margin: 0 auto; font-size: 16px; color: var(--muted); }

  p { font-size: 16px; color: var(--lilac); margin-bottom: 18px; line-height: 1.8; }
  .pull-quote {
    border-left: 3px solid var(--gold); padding: 4px 0 4px 24px; margin: 32px 0;
    font-family: 'Cinzel', serif; font-size: 19px; color: var(--white); font-style: italic; line-height: 1.5;
  }

  .model-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 40px; }
  .model-card {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 28px 22px; text-align: center;
  }
  .model-icon { font-size: 28px; margin-bottom: 14px; }
  .model-card h3 { font-family: 'Cinzel', serif; font-size: 16px; color: var(--white); margin-bottom: 10px; }
  .model-card p { font-size: 13.5px; color: var(--muted); margin: 0; line-height: 1.65; }
  .model-arrow { text-align: center; font-size: 22px; color: var(--gold); margin: 28px 0; }

  .bottom-cta { text-align: center; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 15px 36px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.1em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }

  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--white); }

  @media (max-width: 800px) {
    .model-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 768px) {
    nav { padding: 0 20px; }
    .nav-toggle { display: block; }
    .nav-links {
      display: none; position: absolute; top: 100%; left: 0; right: 0;
      flex-direction: column; align-items: stretch; gap: 0;
      background: rgba(13,11,26,0.98); border-bottom: 1px solid var(--purple-dim);
      padding: 8px 20px 20px; z-index: 50;
    }
    .nav-links.open { display: flex; }
    .nav-links > a, .nav-links > .nav-hover > a { padding: 14px 4px; display: block; }
    .nav-links .nav-cta { text-align: center; margin-top: 8px; }
    .nav-hover-panel { position: static; padding-top: 0; }
    .nav-hover-panel-inner { width: 100%; box-shadow: none; }
    .nav-account { flex-direction: column; align-items: stretch; gap: 0; }
    .nav-logged-out { flex-direction: column; align-items: stretch; gap: 0; }
    .nav-logged-in > a { padding: 14px 4px; display: block; }
    .nav-clock { text-align: center; padding: 14px 4px; }
  }
  @media (max-width: 600px) {
    .model-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <div class="nav-left">
    <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
    <a href="/" class="nav-about-link">Home</a>
  </div>
  <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
  <div class="nav-links" id="navLinks">
    <a href="/#events">Events</a>
    <div class="nav-hover">
      <a href="/subscribe/">Subscribe</a>
      <div class="nav-hover-panel">
        <div class="nav-hover-panel-inner">
          <form action="/subscribe/" method="post">
            <input type="text" name="firstName" placeholder="First name (optional)">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="hidden" class="nav-recruiter-input" name="recruiter" value="">
            <button type="submit">Subscribe ✦</button>
          </form>
        </div>
      </div>
    </div>

    <div class="nav-account" id="navAccount">
      <div class="nav-logged-out" id="navLoggedOut">
        <a href="/join/" class="nav-cta">Join the Team</a>
        <div class="nav-hover">
          <a href="/join/login.php">Log In</a>
          <div class="nav-hover-panel">
            <div class="nav-hover-panel-inner">
              <form action="/join/LoginProcess.php" method="post">
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In ✦</button>
                <a href="/join/ForgotPassword.php" class="hover-link">Forgot Password?</a>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="nav-hover nav-logged-in" id="navLoggedIn">
        <a href="#" id="navUserName">Account</a>
        <div class="nav-hover-panel">
          <div class="nav-hover-panel-inner" style="padding: 10px; width: 180px;">
            <a href="/join/dashboard.php" class="hover-menu-link">📋 Dashboard</a>
            <a href="/join/logout.php" class="hover-menu-link">🚪 Log Out</a>
          </div>
        </div>
      </div>
      <div class="nav-clock" id="navClock">--:--</div>
    </div>
  </div>
</nav>

<main>

  <section>
    <div class="wrap">
      <div class="eyebrow">About Mythos Events</div>
      <h1>Performers. Venues. Artists. Promoters.</h1>
      <p class="hero-sub">We bring them all together — and match them with people looking for a good time. If you've got something to offer, we'll help you find who needs it.</p>
      <div class="hero-cta" style="text-align:center;margin-top:32px;">
        <a href="/join/" class="btn-primary">Get Involved ✦</a>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <h2>What We Believe</h2>
      <p>Most events put the creation on display. Mythos Events puts the <em style="color:var(--white);">creator</em> in the room. Every festival we build is designed around one idea: direct, unmediated contact between the people who make things and the people who experience them.</p>
      <p>The craftsperson is at the table. The improv performer riffs until the crowd is breathless. The local game designer is right there explaining the rules they wrote. No glass case, no velvet rope — just people, stories, and wonder, shared face to face.</p>
      <div class="pull-quote">
        <p>"Museums show you what someone made. Festivals let you meet the person who made it."</p>
      </div>
      <p>Based in Glendale, Arizona. Built for dreamers, families, artists, and anyone who ever wanted to see behind the curtain.</p>
    </div>
  </section>

  <section>
    <div class="wrap">
      <h2>How It Works</h2>
      <p style="text-align:center; max-width:640px; margin:0 auto 20px;">Mythos Events doesn't just throw festivals — we build the connections that make them possible. Four groups make up the network, and we bring them together:</p>

      <div class="model-grid">
        <div class="model-card">
          <div class="model-icon">🎭</div>
          <h3>Talent</h3>
          <p>Performers, artists, vendors, and workshop leaders who bring the experience to life.</p>
        </div>
        <div class="model-card">
          <div class="model-icon">🏛️</div>
          <h3>Venues</h3>
          <p>Businesses and spaces looking to attract customers with real, memorable entertainment.</p>
        </div>
        <div class="model-card">
          <div class="model-icon">🗺️</div>
          <h3>Organizers</h3>
          <p>People who run events locally — or bring Mythos Events to a whole new city.</p>
        </div>
        <div class="model-card">
          <div class="model-icon">🎟️</div>
          <h3>Affiliates</h3>
          <p>People who spread the word, sell tickets, and bring new faces into the community.</p>
        </div>
      </div>

      <div class="model-arrow">↓</div>

      <p style="text-align:center; max-width:640px; margin:0 auto;">We match the right talent with the right venue, give organizers the tools and network to run a great event, and let affiliates get credit for everyone they bring in. The result: real events, built by real relationships — not a booking platform, a community.</p>
    </div>
  </section>

  <section>
    <div class="wrap">
      <h2>Why Events Matter</h2>
      <p>An event is more than a night out — it's an exchange. When a performer, a vendor, an audience member, and a venue owner are all standing in the same space, ideas move in every direction. Audiences discover art forms they didn't know they loved. Artists find their next opportunity. Venue owners meet the talent that will define their space. Everyone leaves having traded something — a story, a connection, an idea worth carrying home.</p>
      <p>That exchange is the whole point. Mythos Events exists to make it happen more often, in more places, for more people.</p>
    </div>
  </section>

  <section>
    <div class="wrap bottom-cta">
      <h2>Be Part of the Exchange</h2>
      <p style="margin-bottom: 28px;">Whether you're talent, a venue, an organizer, or an affiliate — there's a place for you.</p>
      <a href="/join/" class="btn-primary">Get Involved ✦</a>
    </div>
  </section>

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

  // Mobile nav toggle
  const navToggle = document.getElementById('navToggle');
  const navLinksEl = document.getElementById('navLinks');
  if (navToggle && navLinksEl) {
    navToggle.addEventListener('click', () => {
      navLinksEl.classList.toggle('open');
    });
  }

  // Nav clock — see /techniques/nav-account-widget.md
  (function() {
    const clockEl = document.getElementById('navClock');
    if (!clockEl) return;
    function updateClock() {
      const now = new Date();
      let h = now.getHours();
      const m = String(now.getMinutes()).padStart(2, '0');
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12; if (h === 0) h = 12;
      clockEl.textContent = h + ':' + m + ' ' + ampm;
    }
    updateClock();
    setInterval(updateClock, 1000);
  })();

  // Nav account state
  (function() {
    const loggedOutEl = document.getElementById('navLoggedOut');
    const loggedInEl = document.getElementById('navLoggedIn');
    const nameEl = document.getElementById('navUserName');
    if (!loggedOutEl || !loggedInEl) return;
    fetch('/join/whoami.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (data && data.loggedIn) {
          loggedOutEl.style.display = 'none';
          loggedInEl.style.display = 'block';
          if (nameEl) nameEl.textContent = data.name + ' ▾';
        }
      })
      .catch(() => {});
  })();

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
      document.querySelectorAll('.nav-recruiter-input').forEach(input => {
        input.value = affId;
      });
    }
  })();
</script>
</body>
</html>
