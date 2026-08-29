<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: /join/login.php?return_url=/sonlight/dashboard.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('../join/logintodatabase.php');

$personId = intval($_SESSION['person_id']);

$stmt = mysqli_prepare($conn, "SELECT first, last, email FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$person = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$person) {
    session_destroy();
    header("Location: /join/login.php");
    exit();
}

// Check Sonlight membership
$memStmt = mysqli_prepare($conn, "SELECT 1 FROM group_memberships gm JOIN `groups` g ON gm.group_id = g.id WHERE gm.person_id = ? AND g.slug = 'sonlight'");
mysqli_stmt_bind_param($memStmt, "i", $personId);
mysqli_stmt_execute($memStmt);
$isSonlightMember = mysqli_stmt_get_result($memStmt)->num_rows > 0;
mysqli_stmt_close($memStmt);

// Upcoming signups for this person
$upcomingSignups = [];
if ($isSonlightMember) {
    $sigStmt = mysqli_prepare($conn, "SELECT category, event_date, question FROM sonlight_signups WHERE person_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC");
    mysqli_stmt_bind_param($sigStmt, "i", $personId);
    mysqli_stmt_execute($sigStmt);
    $sigResult = mysqli_stmt_get_result($sigStmt);
    while ($row = mysqli_fetch_assoc($sigResult)) { $upcomingSignups[] = $row; }
    mysqli_stmt_close($sigStmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Sonlight Drama Team</title>
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
    --sun-green: #3FA66B;
  }
  body {
    background: var(--sun-bg); color: var(--sun-text);
    font-family: 'Nunito', sans-serif; font-size: 16px; line-height: 1.7;
    min-height: 100vh; display: flex; flex-direction: column;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; }

  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: var(--sun-card); border-bottom: 2px solid var(--sun-border); position: sticky; top: 0; z-index: 10;
  }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 20px; color: var(--sun-text); text-decoration: none; }
  .nav-logo span { color: var(--sun-primary); }
  .nav-links { display: flex; gap: 20px; align-items: center; }
  .nav-links a { font-size: 14px; color: var(--sun-muted); text-decoration: none; font-weight: 700; }
  .nav-links a:hover { color: var(--sun-primary); }
  .nav-clock { font-family: 'Poppins', sans-serif; font-size: 13px; letter-spacing: 0.06em; color: var(--sun-muted); min-width: 58px; text-align: right; white-space: nowrap; }
  .nav-dropdown { position: relative; }
  .nav-dropdown > a { color: var(--sun-text); text-decoration: none; font-weight: 700; font-size: 14px; }
  .nav-dropdown > a:hover { color: var(--sun-primary); }
  .nav-dropdown-panel { display: none; position: absolute; right: 0; top: calc(100% + 14px); background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 12px; padding: 8px; min-width: 160px; z-index: 100; box-shadow: 0 8px 24px rgba(58,46,42,0.1); }
  .nav-dropdown:hover .nav-dropdown-panel { display: block; }
  .nav-dropdown-panel a { display: block; padding: 10px 14px; font-weight: 700; font-size: 14px; color: var(--sun-muted); text-decoration: none; border-radius: 8px; }
  .nav-dropdown-panel a:hover { background: #FFF3E8; color: var(--sun-primary); }

  main { flex: 1; max-width: 760px; margin: 0 auto; padding: 48px 20px 80px; width: 100%; }

  .greeting { margin-bottom: 36px; }
  .greeting .eyebrow { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11px; letter-spacing: 0.2em; color: var(--sun-primary); text-transform: uppercase; margin-bottom: 8px; }
  .greeting h1 { font-weight: 900; font-size: clamp(26px, 5vw, 36px); color: var(--sun-text); }

  .card { background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 16px; padding: 28px 30px; margin-bottom: 20px; }
  .card-title { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--sun-primary); margin-bottom: 16px; }

  .quick-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .btn {
    display: inline-block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13px;
    padding: 11px 22px; border-radius: 30px; text-decoration: none;
    transition: transform 0.15s, background 0.2s;
  }
  .btn:hover { transform: translateY(-2px); }
  .btn-primary { background: var(--sun-primary); color: #fff; }
  .btn-primary:hover { background: var(--sun-primary-dk); }
  .btn-outline { background: transparent; color: var(--sun-text); border: 2px solid var(--sun-border); }
  .btn-outline:hover { border-color: var(--sun-primary); color: var(--sun-primary); }
  .btn-ghost { background: transparent; color: var(--sun-muted); border: 2px solid var(--sun-border); }
  .btn-ghost:hover { color: var(--sun-primary); border-color: var(--sun-primary); }

  .signup-row {
    display: flex; align-items: flex-start; gap: 16px; padding: 16px 0;
    border-bottom: 2px solid var(--sun-border);
  }
  .signup-row:last-child { border-bottom: none; padding-bottom: 0; }
  .signup-row:first-child { padding-top: 0; }
  .signup-cat {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 12px;
    padding: 4px 12px; border-radius: 999px; white-space: nowrap;
  }
  .signup-cat.bible { background: rgba(255,193,69,0.18); color: #7A5800; }
  .signup-cat.theater { background: rgba(46,169,200,0.12); color: #1A6A82; }
  .signup-date { font-weight: 700; font-size: 15px; }
  .signup-question { font-size: 14px; color: var(--sun-muted); font-style: italic; margin-top: 3px; }
  .empty-state { text-align: center; padding: 20px 0; color: var(--sun-muted); font-size: 15px; }

  .join-prompt {
    background: #FFF3E8; border: 2px solid var(--sun-gold); border-radius: 14px; padding: 24px 28px; margin-bottom: 20px; text-align: center;
  }
  .join-prompt p { color: var(--sun-muted); margin-bottom: 16px; }

  .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--sun-border); font-size: 15px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: var(--sun-muted); font-size: 13px; font-weight: 700; }

  footer { text-align: center; padding: 24px; border-top: 2px solid var(--sun-border); font-size: 13px; color: var(--sun-muted); }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .quick-actions { flex-direction: column; }
    .quick-actions .btn { text-align: center; }
  }
</style>
</head>
<body>

<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
  <div class="nav-links">
    <a href="/sonlight/scheduler.php">Scheduler</a>
    <a href="/sonlight/themes.php">Theme Picker</a>
    <div class="nav-dropdown">
      <a href="/sonlight/dashboard.php"><?php echo htmlspecialchars($person['first']); ?> ▾</a>
      <div class="nav-dropdown-panel">
        <a href="/join/edit-profile.php">✏️ Edit Profile</a>
        <a href="/join/logout.php">🚪 Log Out</a>
      </div>
    </div>
    <div class="nav-clock" id="navClock">--:--</div>
  </div>
</nav>

<main>

  <div class="greeting">
    <div class="eyebrow">Sonlight Drama Team</div>
    <h1>Hi, <?php echo htmlspecialchars($person['first']); ?>!</h1>
  </div>

  <?php if ($isSonlightMember): ?>

  <!-- Quick Actions -->
  <div class="card">
    <div class="card-title">Quick Actions</div>
    <div class="quick-actions">
      <a href="/sonlight/scheduler.php" class="btn btn-primary">☀️ Stage Scheduler</a>
      <a href="/sonlight/themes.php" class="btn btn-outline">🎭 Theme Picker</a>
      <a href="/join/edit-profile.php" class="btn btn-ghost">✏️ Edit Profile</a>
    </div>
  </div>

  <!-- Upcoming signups -->
  <div class="card">
    <div class="card-title">Your Upcoming Sundays</div>
    <?php if ($upcomingSignups): ?>
      <?php foreach ($upcomingSignups as $sig):
        $date = date('D, M j', strtotime($sig['event_date']));
        $catClass = strtolower($sig['category']);
        $catIcon = $sig['category'] === 'Bible' ? '📖' : '🎭';
      ?>
      <div class="signup-row">
        <div>
          <div class="signup-date"><?php echo $date; ?></div>
          <span class="signup-cat <?php echo $catClass; ?>"><?php echo $catIcon; ?> <?php echo htmlspecialchars($sig['category']); ?> Question</span>
          <?php if ($sig['question']): ?>
          <div class="signup-question">"<?php echo htmlspecialchars($sig['question']); ?>"</div>
          <?php else: ?>
          <div class="signup-question" style="color:#B0A090;">No question written yet — <a href="/sonlight/scheduler.php" style="color:var(--sun-primary);font-weight:700;">add one</a></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <p style="margin-bottom:14px;">You don't have any upcoming Sundays claimed yet.</p>
        <a href="/sonlight/scheduler.php" class="btn btn-primary">Open the Scheduler</a>
      </div>
    <?php endif; ?>
  </div>

  <?php else: ?>

  <div class="join-prompt">
    <div style="font-size:32px;margin-bottom:10px;">☀️</div>
    <h3 style="margin-bottom:8px;">Join the Sonlight Drama Team</h3>
    <p>You have a Mythos Events account but haven't joined Sonlight yet. Sign up to get access to the Stage Scheduler.</p>
    <a href="/sonlight/join.php" class="btn btn-primary">Join Sonlight</a>
  </div>

  <?php endif; ?>

  <!-- Account info -->
  <div class="card">
    <div class="card-title">Your Account</div>
    <div class="info-row">
      <span class="info-label">Name</span>
      <span><?php echo htmlspecialchars($person['first'] . ' ' . $person['last']); ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Email</span>
      <span><?php echo htmlspecialchars($person['email']); ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Sonlight</span>
      <span><?php echo $isSonlightMember ? '✅ Member' : '—'; ?></span>
    </div>
    <div style="margin-top:16px;">
      <a href="/join/edit-profile.php" class="btn btn-ghost" style="font-size:13px;">✏️ Edit Profile</a>
    </div>
  </div>

</main>

<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">Questions?</a> &nbsp;·&nbsp; <a href="/join/logout.php">Log Out</a></p>
</footer>

<script>
(function() {
  const clockEl = document.getElementById('navClock');
  if (!clockEl) return;
  function tick() {
    const now = new Date(), m = String(now.getMinutes()).padStart(2,'0');
    let h = now.getHours(); const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
    clockEl.textContent = h + ':' + m + ' ' + ampm;
  }
  tick(); setInterval(tick, 1000);
})();
</script>
</body>
</html>
