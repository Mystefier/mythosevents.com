<?php
session_start();
$logged_in = false;
$is_approved = false;
$user_name = '';

if (isset($_SESSION['user_id'])) {
    include(__DIR__ . '/../join/logintodatabase.php');
    $user_id = (int)$_SESSION['user_id'];
    $userStmt = $conn->prepare("SELECT first, application_status FROM people WHERE id = ?");
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
    if ($user) {
        $logged_in = true;
        $user_name = $user['first'];
        $is_approved = ($user['application_status'] === 'approved');
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Run Your Own Event — Mythos Events</title>
<meta name="description" content="Mythos Events gives organizers the network, the talent pool, and the support to run a real event — from a neighborhood pop-up to a full festival.">
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
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.7;
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

  /* DASHBOARD FOR APPROVED ORGANIZERS */
  .dashboard-section { padding: 40px 20px 70px; background: rgba(107,63,160,0.05); border-top: 1px solid var(--purple-dim); }
  .dashboard-wrap { max-width: 960px; margin: 0 auto; }
  .dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; }
  .dashboard-header h2 { font-family: 'Cinzel', serif; font-size: 26px; font-weight: 900; color: var(--white); }
  .btn-secondary {
    display: inline-block; background: var(--gold); color: var(--midnight);
    padding: 12px 24px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em; font-weight: 700;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-secondary:hover { background: #f0d960; transform: translateY(-2px); }
  .dashboard-note { color: var(--muted); font-size: 14px; margin-top: 8px; }

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
    .dashboard-header { flex-direction: column; align-items: flex-start; }
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

  <?php if ($logged_in && $is_approved): ?>
    <!-- DASHBOARD FOR APPROVED ORGANIZERS -->
    <div class="dashboard-section">
      <div class="dashboard-wrap">
        <div class="dashboard-header">
          <div>
            <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>! ✦</h2>
            <p class="dashboard-note">You're approved to post events to the network.</p>
          </div>
          <a href="/organizers/post.php" class="btn-secondary">POST AN EVENT</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="hero">
    <div class="eyebrow">For Organizers</div>
    <h1>Run Your Own Event</h1>
    <p>You don't have to build a talent network, a venue list, and an audience from scratch. Plug into ours — from a neighborhood pop-up to a full festival, we give you the pieces to make it happen.</p>
    <div class="hero-cta">
      <?php if (!$logged_in || !$is_approved): ?>
        <a href="/join/" class="btn-primary">Become an Organizer ✦</a>
      <?php endif; ?>
      <a href="/events/" class="btn-primary" style="margin-left: 12px; background: var(--purple-dim); border: 1px solid var(--purple); color: var(--white);">View Events</a>
    </div>
  </div>

  <div class="value-section">
    <div class="value-wrap">
      <div class="value-grid">
        <div class="value-card">
          <div class="value-icon">🎭</div>
          <h3>A Ready Talent Pool</h3>
          <p>Performers, artists, and vendors already in the network — you're not starting your contact list from zero.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🏛️</div>
          <h3>Venues Looking for Events</h3>
          <p>Businesses already asking for entertainment to bring in customers — a home for what you're planning.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🗺️</div>
          <h3>You're Not Doing It Alone</h3>
          <p>Bring your event into Mythos Events and draw on a growing community instead of building support from scratch.</p>
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
          <h3>Join & Share Your Idea</h3>
          <p>Sign up as an Organizer and tell us what you're picturing — the kind of event, where, and roughly when.</p>
        </div>
        <div class="step-item">
          <div class="step-number">2</div>
          <h3>We Connect You</h3>
          <p>We help match you with the talent and venues from our network that fit what you're building.</p>
        </div>
        <div class="step-item">
          <div class="step-number">3</div>
          <h3>Post Your Events</h3>
          <p>Once approved, post your events to the network and connect directly with performers, venues, and attendees.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="who-section">
    <div class="who-wrap">
      <div class="section-title">Who This Is For</div>
      <p style="color:var(--muted);font-size:15px;">Anyone with an idea for an event and the drive to make it real.</p>
      <div class="who-pills">
        <span class="pill">🎪 First-Time Organizers</span>
        <span class="pill">🗺️ Bringing Mythos to a New City</span>
        <span class="pill">🏘️ Neighborhood Pop-Ups</span>
        <span class="pill">🎉 Community Groups</span>
        <span class="pill">✨ Anyone With an Idea</span>
      </div>
    </div>
  </div>

  <div class="showcase-section">
    <div class="showcase-wrap">
      <div class="section-title">Ready to Build Something?</div>
      <p>Signing up takes less than two minutes. Tell us what you're picturing, and we'll help you find the pieces to make it real.</p>
      <?php if (!$logged_in || !$is_approved): ?>
        <a href="/join/" class="btn-primary">Become an Organizer ✦</a>
      <?php else: ?>
        <a href="/organizers/post.php" class="btn-primary">Post Your First Event ✦</a>
      <?php endif; ?>
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
