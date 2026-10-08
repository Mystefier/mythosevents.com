<?php
include(__DIR__ . '/../join/logintodatabase.php');

$search = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$knownRoles = ['Vendor', 'Organizer', 'volunteer', 'Sales', 'Performer', 'Artist', 'Operations', 'Venue Manager/Owner', 'Other'];
$roleLabels = [
    'Vendor' => '🛖 Vendor',
    'Organizer' => '📋 Organizer',
    'volunteer' => '🌟 Volunteer',
    'Sales' => '🎟️ Sales',
    'Performer' => '🎭 Performer',
    'Artist' => '🎨 Artist',
    'Operations' => '⚙️ Operations',
    'Venue Manager/Owner' => '🏛️ Venue Manager/Owner',
    'Other' => '✨ Other',
];

$sql = "SELECT id, first, last, roles, description, service_area_address, website, email
        FROM people
        WHERE application_status = 'approved' AND directory_opt_in = 1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (first LIKE ? OR last LIKE ? OR description LIKE ? OR roles LIKE ? OR service_area_address LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}
if ($roleFilter !== '' && in_array($roleFilter, $knownRoles, true)) {
    $sql .= " AND roles LIKE ?";
    $params[] = '%' . $roleFilter . '%';
    $types .= 's';
}
$sql .= " ORDER BY first ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$people = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Talent Directory — Mythos Events</title>
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

  .hero { text-align: center; padding: 70px 20px 40px; max-width: 760px; margin: 0 auto; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  .hero h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(30px, 5vw, 50px);
    color: var(--white); line-height: 1.15; margin-bottom: 20px; text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .hero p { font-size: 16px; color: var(--muted); max-width: 600px; margin: 0 auto; }

  .search-form { margin-top: 28px; display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap; }
  .search-form input[type="text"] {
    width: 280px; max-width: 100%; background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 12px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s;
  }
  .search-form select {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 8px;
    padding: 12px 16px; font-size: 14px; font-family: 'Inter', sans-serif; color: var(--white); outline: none;
  }
  .search-form input[type="text"]:focus, .search-form select:focus { border-color: var(--purple-lt); }
  .search-form input[type="text"]::placeholder { color: var(--muted); }
  .search-form button {
    background: var(--purple); color: var(--white); border: none; border-radius: 8px;
    padding: 12px 22px; font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em;
    cursor: pointer; transition: background 0.2s;
  }
  .search-form button:hover { background: var(--purple-lt); }
  .search-clear { font-size: 13px; color: var(--muted); text-decoration: none; }
  .search-clear:hover { color: var(--white); }
  .search-result-note { color: var(--muted); font-size: 14px; margin-bottom: 20px; }

  .directory-section { padding: 30px 20px 70px; }
  .directory-wrap { max-width: 960px; margin: 0 auto; }
  .directory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

  .person-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 24px; transition: transform 0.2s, border-color 0.2s; }
  .person-card:hover { transform: translateY(-4px); border-color: var(--purple-lt); }
  .person-name { font-family: 'Cinzel', serif; font-size: 18px; font-weight: 700; color: var(--white); margin-bottom: 10px; }
  .role-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
  .role-pill { font-size: 11px; background: rgba(107,63,160,0.3); color: var(--lilac); padding: 4px 10px; border-radius: 20px; }
  .person-location { font-size: 14px; color: var(--muted); margin-bottom: 12px; }
  .person-bio { font-size: 15px; color: var(--muted); line-height: 1.6; margin-bottom: 16px; }

  .person-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .person-link {
    display: inline-block; font-size: 13px; font-family: 'Cinzel', serif;
    padding: 8px 14px; border-radius: 6px; text-decoration: none; transition: background 0.2s, transform 0.15s;
  }
  .person-link-primary { background: var(--purple); color: var(--white); letter-spacing: 0.1em; }
  .person-link-primary:hover { background: var(--purple-lt); transform: translateY(-1px); }
  .person-link-secondary { background: rgba(107,63,160,0.3); color: var(--lilac); letter-spacing: 0.05em; }
  .person-link-secondary:hover { background: rgba(107,63,160,0.5); }

  .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
  .empty p { font-size: 16px; margin-bottom: 20px; }
  .empty a { color: var(--purple-lt); text-decoration: none; font-weight: 600; }

  footer { position: relative; z-index: 2; background: rgba(13,11,26,0.95); border-top: 1px solid var(--purple-dim); padding: 30px 20px; text-align: center; font-size: 14px; color: var(--muted); }
  footer a { color: var(--purple-lt); text-decoration: none; }
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
    <div class="eyebrow">FIND YOUR PEOPLE</div>
    <h1>Talent Directory</h1>
    <p>Performers, vendors, and crew who've opted in to be found by organizers and venues looking for their talent.</p>
    <form method="get" class="search-form">
      <input type="text" name="q" placeholder="Search by name, skill, or location..." value="<?php echo htmlspecialchars($search); ?>">
      <select name="role">
        <option value="">All Roles</option>
        <?php foreach ($knownRoles as $r): ?>
          <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $roleFilter === $r ? 'selected' : ''; ?>><?php echo htmlspecialchars($roleLabels[$r]); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">SEARCH</button>
      <?php if ($search !== '' || $roleFilter !== ''): ?>
        <a href="/directory/" class="search-clear">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="directory-section">
    <div class="directory-wrap">
      <?php if ($search !== '' || $roleFilter !== ''): ?>
        <p class="search-result-note"><?php echo count($people); ?> result<?php echo count($people) === 1 ? '' : 's'; ?></p>
      <?php endif; ?>

      <?php if (count($people) > 0): ?>
        <div class="directory-grid">
          <?php foreach ($people as $person): ?>
            <?php $roleList = $person['roles'] ? array_map('trim', explode(',', $person['roles'])) : []; ?>
            <div class="person-card">
              <div class="person-name"><?php echo htmlspecialchars(trim($person['first'] . ' ' . $person['last'])); ?></div>
              <?php if ($roleList): ?>
                <div class="role-pills">
                  <?php foreach ($roleList as $r): ?>
                    <span class="role-pill"><?php echo htmlspecialchars($roleLabels[$r] ?? $r); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <?php if ($person['service_area_address']): ?>
                <div class="person-location">📍 <?php echo htmlspecialchars($person['service_area_address']); ?></div>
              <?php endif; ?>
              <?php if ($person['description']): ?>
                <div class="person-bio"><?php echo htmlspecialchars(substr($person['description'], 0, 180)); ?><?php echo strlen($person['description']) > 180 ? '…' : ''; ?></div>
              <?php endif; ?>
              <div class="person-links">
                <a href="mailto:<?php echo htmlspecialchars($person['email']); ?>" class="person-link person-link-primary">CONTACT</a>
                <?php if ($person['website']): ?>
                  <a href="<?php echo htmlspecialchars($person['website']); ?>" target="_blank" class="person-link person-link-secondary">WEBSITE</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty">
          <?php if ($search !== '' || $roleFilter !== ''): ?>
            <p>No one matches that search.</p>
            <p><a href="/directory/">Clear search →</a></p>
          <?php else: ?>
            <p>No one has opted into the directory yet.</p>
            <p><a href="/join/">Join Mythos Events →</a></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
</footer>

<script>
const starsContainer = document.getElementById('stars');
for (let i = 0; i < 50; i++) {
  const star = document.createElement('div');
  star.className = 'star';
  star.style.width = Math.random() * 3 + 'px';
  star.style.height = star.style.width;
  star.style.left = Math.random() * 100 + '%';
  star.style.top = Math.random() * 100 + '%';
  star.style.setProperty('--dur', (Math.random() * 3 + 2) + 's');
  star.style.setProperty('--delay', Math.random() * 2 + 's');
  starsContainer.appendChild(star);
}
</script>

</body>
</html>
