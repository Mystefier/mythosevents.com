<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy — Mythos Events</title>
<meta name="description" content="What Mythos Events collects, why, and how to control or delete your data.">
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
  }
  html { scroll-behavior: smooth; }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden;
  }
  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: rgba(13,11,26,0.95); border-bottom: 1px solid var(--purple-dim);
  }
  .nav-logo { font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px; color: var(--white); letter-spacing: 0.05em; text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; }
  .nav-back:hover { color: var(--white); }

  main { flex: 1; }
  .wrap { max-width: 760px; margin: 0 auto; padding: 60px 24px 80px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(30px, 5vw, 44px);
    color: var(--white); margin-bottom: 8px; text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .updated { font-size: 13px; color: var(--muted); margin-bottom: 44px; }
  h2 {
    font-family: 'Cinzel', serif; font-size: 20px; color: var(--white);
    margin-top: 40px; margin-bottom: 14px;
  }
  p, li { color: var(--lilac); margin-bottom: 14px; }
  ul, ol { padding-left: 22px; margin-bottom: 14px; }
  li { margin-bottom: 8px; }
  strong { color: var(--white); }
  a { color: var(--purple-lt); }
  .summary-box {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px;
    padding: 26px 28px; margin-bottom: 8px;
  }
  .summary-box p:last-child { margin-bottom: 0; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
  th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--purple-dim); vertical-align: top; }
  th { color: var(--white); font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.05em; }
  td { color: var(--muted); }

  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--white); }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    table, thead, tbody, th, td, tr { display: block; }
    th { border-bottom: none; padding-bottom: 2px; }
    td { border-bottom: none; padding-top: 2px; padding-bottom: 14px; }
  }
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/" class="nav-back">← Back to Home</a>
</nav>

<main>
  <div class="wrap">
    <div class="eyebrow">Privacy</div>
    <h1>Privacy Policy</h1>
    <div class="updated">Last updated September 2026</div>

    <div class="summary-box">
      <p><strong>The short version:</strong> we collect what we need to match you with the right people (talent, venues, organizers, affiliates) and to run your account — nothing more. We don't sell your data. You can see, edit, or delete what we have on you at any time from your dashboard.</p>
    </div>

    <h2>What We Collect</h2>
    <p>When you join, you can give us as little as an email address. Everything past that is optional and added when you're ready — either on your profile at signup, or later from Edit Profile.</p>
    <table>
      <tr><th>Info</th><th>Why we ask</th></tr>
      <tr><td>Name, email</td><td>Identify your account, contact you about opportunities</td></tr>
      <tr><td>Phone, date of birth</td><td>Optional. Add these later if a venue needs them for an event (e.g. age requirements) — never required to join</td></tr>
      <tr><td>Roles &amp; interests (Talent, Venue, Organizer, Affiliate, etc.)</td><td>Match you to the right opportunities</td></tr>
      <tr><td>Service area / address</td><td>Optional. Matches you to nearby venues and events. If you don't set one, you're treated as available anywhere</td></tr>
      <tr><td>Venue or asset details you add</td><td>Lets us match talent to your space</td></tr>
      <tr><td>Messages, descriptions, notes you write</td><td>Give context for what you do or need</td></tr>
      <tr><td>Password</td><td>Stored as a one-way hash — we never store or see your actual password</td></tr>
    </table>

    <h2>Referral Tracking</h2>
    <p>If someone shares their personal Mythos Events link with you (`?id=...`), we note which account referred you. This is stored as a simple attribution on your account — it's how we know who to thank for bringing people in. It's not a payment or rewards system, and it doesn't affect what you see or how your account works.</p>
    <p>Technically: the referral ID is held briefly in your browser's <code>sessionStorage</code> (cleared when you close the tab) until you complete a form, at which point it's saved to your account. This is not a tracking cookie and isn't used for advertising.</p>

    <h2>Your Application</h2>
    <p>New accounts start in a review state before becoming fully active. While pending, your account works normally, but your public digital business card (the one used to share your referral link in person) won't be visible until you're approved.</p>

    <h2>Who Else Sees Your Info</h2>
    <ul>
      <li><strong>Nobody, by default.</strong> Your profile isn't public. Only site administrators can see the full list of members.</li>
      <li><strong>Other members, in limited contexts</strong> — for example, if you join a group like Sonlight, other members of that group can see your name next to what you've signed up for.</li>
      <li><strong>Google</strong>, if you enter a street address — we use Google's Maps API to convert it into map coordinates for matching. Only the address text is sent, and only when you submit one.</li>
      <li><strong>A QR code generator (api.qrserver.com)</strong> creates the scannable code on your digital business card. It's only ever given your public referral link, never your personal details.</li>
    </ul>
    <p>We do not sell, rent, or share your information with advertisers or data brokers.</p>

    <h2>Emails We Send</h2>
    <p>Account-related email only: confirming your signup, welcoming you, occasional reminders relevant to your role (like a nudge to add your venue's details), and replies if you contact us. We don't send marketing email unless you've specifically subscribed to updates, and every email includes a way to stop.</p>

    <h2>Your Controls</h2>
    <ul>
      <li><strong>Edit anytime</strong> — update or remove any optional field from your dashboard whenever you like.</li>
      <li><strong>Delete your account</strong> — a permanent delete option is available from your dashboard. If you referred anyone, their attribution passes up to whoever referred you (or to us directly) instead of just disappearing, so the system stays consistent — but your own personal data is removed.</li>
      <li><strong>Questions or requests</strong> — email <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a> and we'll help directly, including with a full export or removal if our normal tools don't cover what you need.</li>
    </ul>

    <h2>Children's Privacy</h2>
    <p>Mythos Events isn't directed at children under 13, and we don't knowingly collect information from anyone under that age without parental involvement. If you believe a child has created an account, contact us and we'll remove it.</p>

    <h2>Changes to This Policy</h2>
    <p>If how we handle data changes in a meaningful way, we'll update this page and adjust the date at the top. We won't make material changes without a way for you to find out.</p>

    <h2>Contact</h2>
    <p>Mythos Events is based in Arizona and can be reached at <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a> for any privacy question, request, or concern.</p>
  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
</footer>

</body>
</html>
