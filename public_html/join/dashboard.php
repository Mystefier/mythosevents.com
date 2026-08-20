<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$stmt = mysqli_prepare($conn, "SELECT * FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    // Session points at a person that no longer exists — clear it out
    session_destroy();
    header("Location: login.php");
    exit();
}

$person = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$assetStmt = mysqli_prepare($conn, "SELECT id, type, name FROM assets WHERE owner_person_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($assetStmt, "i", $personId);
mysqli_stmt_execute($assetStmt);
$assetResult = mysqli_stmt_get_result($assetStmt);
$assets = [];
while ($row = mysqli_fetch_assoc($assetResult)) {
    $assets[] = $row;
}
mysqli_stmt_close($assetStmt);
mysqli_close($conn);

$referralLink = "https://mythosevents.com/?id=" . $person['id'];
$cardLink = "https://mythosevents.com/card/?id=" . $person['id'];
$rolesList = $person['roles'] ? array_map('trim', explode(',', $person['roles'])) : [];
$isWelcome = isset($_GET['welcome']) && $_GET['welcome'] == '1';

$hasVenueRole = in_array('Venue Manager/Owner', $rolesList);
$hasVenueAsset = false;
foreach ($assets as $a) {
    if ($a['type'] === 'Venue') { $hasVenueAsset = true; break; }
}
$needsVenueNudge = $hasVenueRole && !$hasVenueAsset;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — Mythos Events</title>
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
    --green:      #52C87A;
    --red:        #E05555;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.7;
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
  .nav-links { display: flex; align-items: center; gap: 24px; }
  .nav-links a { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; transition: color 0.2s; }
  .nav-links a:hover { color: var(--white); }

  main { flex: 1; position: relative; z-index: 1; max-width: 760px; margin: 0 auto; padding: 60px 20px 80px; width: 100%; }
  .page-header { margin-bottom: 40px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 14px; }
  .page-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 4.5vw, 40px);
    color: var(--white); text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }

  .welcome-card {
    background: var(--card); border: 1px solid rgba(82,200,122,0.4);
    border-radius: 14px; padding: 28px 32px; margin-bottom: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .welcome-card .status-icon { font-size: 32px; margin-bottom: 10px; }
  .welcome-card h2 { font-family: 'Cinzel', serif; font-size: 17px; color: var(--white); margin-bottom: 10px; }
  .welcome-card p { font-size: 14px; color: var(--muted); line-height: 1.7; margin-bottom: 0; }
  .card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 32px; margin-bottom: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .card h2 { font-family: 'Cinzel', serif; font-size: 15px; color: var(--white); letter-spacing: 0.08em; margin-bottom: 20px; }
  .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: var(--muted); }
  .info-value { color: var(--lilac); text-align: right; max-width: 60%; }
  .role-tags { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
  .role-tag { background: var(--purple-dim); border: 1px solid var(--purple); color: var(--gold); font-size: 11px; padding: 4px 12px; border-radius: 999px; }

  .link-box {
    background: rgba(255,255,255,0.04); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 14px 16px; font-size: 13px; word-break: break-all;
    color: var(--purple-lt); margin-top: 10px;
  }

  .btn-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
  .btn {
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em;
    padding: 13px 24px; border-radius: 8px; text-decoration: none; text-align: center;
    border: none; cursor: pointer; transition: background 0.2s, transform 0.15s;
    display: inline-block;
  }
  .btn-primary { background: var(--purple); color: var(--white); }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .btn-secondary { background: transparent; color: var(--muted); border: 1px solid var(--purple-dim); }
  .btn-secondary:hover { color: var(--white); border-color: var(--purple); }
  .btn-danger { background: transparent; color: var(--red); border: 1px solid rgba(220,80,80,0.4); }
  .btn-danger:hover { background: rgba(220,80,80,0.12); }

  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .card { padding: 24px; }
    .info-row { flex-direction: column; gap: 4px; }
    .info-value { text-align: left; max-width: 100%; }
    .role-tags { justify-content: flex-start; }
  }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <div class="nav-links">
    <a href="/">Home</a>
    <a href="logout.php">Log Out</a>
  </div>
</nav>

<main>
  <div class="page-header">
    <div class="eyebrow"><?php echo $isWelcome ? "You're All Set" : "Welcome Back"; ?></div>
    <h1><?php echo htmlspecialchars($person['first']); ?>'s Dashboard</h1>
  </div>

  <?php if ($needsVenueNudge): ?>
  <div class="welcome-card">
    <div class="status-icon">🏛️</div>
    <h2>Tell Us About Your Venue</h2>
    <p>You mentioned you manage or own a venue — add its details now (address, capacity) so we can match it with the right talent and events. Takes about a minute.</p>
    <div style="text-align:center;margin-top:16px;">
      <a href="add-asset.php?type=Venue" class="btn btn-primary" style="display:inline-block;">Add Your Venue</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($isWelcome): ?>
  <div class="welcome-card">
    <div class="status-icon">✦</div>
    <h2>Application Submitted!</h2>
    <p>Thanks for joining Mythos Events — we've sent a confirmation to your email. We'll reach out when there's an event or opportunity that's a good fit. In the meantime, everything below is yours: your referral link, your business card, and your profile whenever you want to update it.</p>
  </div>
  <?php endif; ?>

  <div class="card">
    <h2>Your Info</h2>
    <div class="info-row"><span class="info-label">Name</span><span class="info-value"><?php echo htmlspecialchars($person['first'] . ' ' . $person['last']); ?></span></div>
    <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?php echo htmlspecialchars($person['email']); ?></span></div>
    <?php if ($person['phone']): ?>
    <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?php echo htmlspecialchars($person['phone']); ?></span></div>
    <?php endif; ?>
    <?php if ($rolesList): ?>
    <div class="info-row">
      <span class="info-label">Roles</span>
      <span class="info-value role-tags">
        <?php foreach ($rolesList as $r): ?><span class="role-tag"><?php echo htmlspecialchars($r); ?></span><?php endforeach; ?>
      </span>
    </div>
    <?php endif; ?>
    <?php if ($person['service_area_address']): ?>
    <div class="info-row"><span class="info-label">Service Area</span><span class="info-value"><?php echo htmlspecialchars($person['service_area_address']); ?> (<?php echo intval($person['service_area_radius_miles']); ?> mi)</span></div>
    <?php endif; ?>

    <div class="btn-row" style="margin-top: 24px;">
      <a href="edit-profile.php" class="btn btn-primary">Edit My Info</a>
    </div>
  </div>

  <div class="card">
    <h2>Your Assets</h2>
    <?php if ($assets): ?>
      <?php foreach ($assets as $asset): ?>
        <div class="info-row">
          <span class="info-label"><?php echo htmlspecialchars($asset['name']); ?></span>
          <span class="info-value"><span class="role-tag"><?php echo htmlspecialchars($asset['type']); ?></span></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="font-size: 13px; color: var(--muted);">You haven't added any assets yet — venues, equipment, shows, and more can all live here.</p>
    <?php endif; ?>
    <div class="btn-row" style="margin-top: 24px;">
      <a href="add-asset.php" class="btn btn-primary">Add Asset</a>
    </div>
  </div>

  <div class="card">
    <h2>Your Referral Link</h2>
    <p style="font-size: 13px; color: var(--muted);">Share this link — anyone who joins or subscribes through it gets tracked back to you.</p>
    <div class="link-box"><?php echo htmlspecialchars($referralLink); ?></div>
    <div class="btn-row" style="margin-top: 16px;">
      <a href="<?php echo htmlspecialchars($cardLink); ?>" class="btn btn-secondary">View My Business Card</a>
    </div>
  </div>

  <div class="card">
    <h2>Account</h2>
    <div class="btn-row">
      <a href="delete-account.php" class="btn btn-danger">Delete My Account</a>
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
</script>
</body>
</html>
