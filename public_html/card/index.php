<?php
$dbname = "db9dh4gg0yfw3q";
include('../join/logintodatabase.php');

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? intval($_GET['id']) : null;

$person = null;
if ($id) {
    $result = mysqli_query($conn, "SELECT id, first, last, email, phone, roles FROM people WHERE id = $id");
    if ($result && mysqli_num_rows($result) > 0) {
        $person = mysqli_fetch_assoc($result);
    }
}
mysqli_close($conn);

$referralLink = $person ? "https://mythosevents.com/?id=" . $person['id'] : '';
$qrUrl = $referralLink ? "https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=8&data=" . urlencode($referralLink) : '';
$roleTitle = $person && $person['roles'] ? str_replace(',', ' · ', $person['roles']) : 'Mythos Events';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Mythos Events Card</title>
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
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.6;
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
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; }
  .nav-back:hover { color: var(--white); }
  main {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 60px 20px; position: relative; z-index: 1;
  }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 20px; text-align: center;
  }

  /* CARD */
  .business-card {
    width: 3.5in; aspect-ratio: 3.5 / 2;
    background: linear-gradient(135deg, #201C32 0%, #150f24 100%);
    border: 1px solid var(--purple-dim);
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    padding: 22px 24px;
    display: flex; flex-direction: column; justify-content: space-between;
    position: relative; overflow: hidden;
  }
  .business-card::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at top right, rgba(107,63,160,0.35), transparent 60%);
    pointer-events: none;
  }
  .card-top { position: relative; display: flex; justify-content: space-between; align-items: flex-start; }
  .card-brand {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: 13px;
    color: var(--white); letter-spacing: 0.03em;
  }
  .card-brand span { color: var(--gold); }
  .card-qr { width: 56px; height: 56px; border-radius: 6px; background: #fff; padding: 3px; }
  .card-qr img { width: 100%; height: 100%; display: block; }
  .card-name { position: relative; }
  .card-name h2 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px; color: var(--white);
    line-height: 1.15; margin-bottom: 4px;
  }
  .card-role {
    font-size: 11px; color: var(--gold); letter-spacing: 0.08em; text-transform: uppercase;
  }
  .card-bottom { position: relative; font-size: 10.5px; color: var(--muted); line-height: 1.5; }
  .card-bottom .link { color: var(--purple-lt); }

  .card-actions { margin-top: 32px; display: flex; gap: 12px; }
  .btn {
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em;
    padding: 13px 26px; border-radius: 8px; text-decoration: none;
    border: none; cursor: pointer; transition: background 0.2s, transform 0.15s;
  }
  .btn-primary { background: var(--purple); color: var(--white); }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .btn-secondary { background: transparent; color: var(--muted); border: 1px solid var(--purple-dim); }
  .btn-secondary:hover { color: var(--white); border-color: var(--purple); }

  .hint { max-width: 420px; text-align: center; margin-top: 24px; font-size: 13px; color: var(--muted); line-height: 1.7; }

  .error-wrap { text-align: center; max-width: 480px; }
  .error-wrap h1 { font-family: 'Cinzel', serif; font-size: 28px; color: var(--white); margin-bottom: 16px; }
  .error-wrap p { color: var(--muted); margin-bottom: 24px; }

  footer {
    position: relative; z-index: 1; text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }

  @media print {
    body * { visibility: hidden; }
    #printable-card, #printable-card * { visibility: visible; }
    #printable-card {
      position: absolute; top: 0; left: 0; margin: 0;
      box-shadow: none; border: none;
    }
    @page { size: 3.5in 2in; margin: 0; }
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

<?php if (!$person): ?>

  <div class="error-wrap">
    <h1>Card Not Found</h1>
    <p>We couldn't find a profile matching this link. Make sure you're using the link from your confirmation email, or contact us if you think this is a mistake.</p>
    <a href="/join/" class="btn btn-primary">Join Mythos Events</a>
  </div>

<?php else: ?>

  <div class="eyebrow">Your Mythos Events Card</div>

  <div class="business-card" id="printable-card">
    <div class="card-top">
      <div class="card-brand">Mythos<span>✦</span>Events</div>
      <?php if ($qrUrl): ?>
        <div class="card-qr"><img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="QR code to referral link"></div>
      <?php endif; ?>
    </div>
    <div class="card-name">
      <h2><?php echo htmlspecialchars(trim($person['first'] . ' ' . $person['last'])); ?></h2>
      <div class="card-role"><?php echo htmlspecialchars($roleTitle); ?></div>
    </div>
    <div class="card-bottom">
      <?php if ($person['phone']): ?><?php echo htmlspecialchars($person['phone']); ?> &nbsp;·&nbsp; <?php endif; ?>
      <?php if ($person['email']): ?><?php echo htmlspecialchars($person['email']); ?><br><?php endif; ?>
      <span class="link"><?php echo htmlspecialchars(str_replace('https://', '', $referralLink)); ?></span>
    </div>
  </div>

  <div class="card-actions">
    <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
    <a href="/" class="btn btn-secondary">Back to Home</a>
  </div>

  <p class="hint">Scan the QR code or share your link — anyone who joins or subscribes through it gets tracked back to you.</p>

<?php endif; ?>

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
