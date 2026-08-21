<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$stmt = mysqli_prepare($conn, "SELECT is_admin, first FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$viewer = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$viewer || !$viewer['is_admin']) {
    header("Location: dashboard.php");
    exit();
}

// Summary stats
$totalPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM people"))['c'];
$totalVenues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM assets WHERE type = 'Venue'"))['c'];
$totalAssets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM assets"))['c'];
$pirateInterest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM people WHERE roles LIKE '%Pirate Walk Through%'"))['c'];

$typeBreakdown = [];
$typeResult = mysqli_query($conn, "SELECT involvement_type, COUNT(*) as c FROM people GROUP BY involvement_type ORDER BY c DESC");
while ($row = mysqli_fetch_assoc($typeResult)) {
    $typeBreakdown[] = $row;
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$peopleSql = "SELECT id, first, last, email, phone, roles, involvement_type, `date` FROM people";
if ($filter === 'pirate') {
    $peopleSql .= " WHERE roles LIKE '%Pirate Walk Through%'";
} elseif ($filter !== '') {
    $safeFilter = mysqli_real_escape_string($conn, $filter);
    $peopleSql .= " WHERE involvement_type = '$safeFilter'";
}
$peopleSql .= " ORDER BY `date` DESC LIMIT 200";
$peopleResult = mysqli_query($conn, $peopleSql);
$people = [];
while ($row = mysqli_fetch_assoc($peopleResult)) {
    $people[] = $row;
}

// Assets
$assetsResult = mysqli_query($conn, "
    SELECT a.id, a.type, a.name, a.created_at, p.first, p.last, p.email,
           v.address, v.capacity, v.indoor_outdoor
    FROM assets a
    JOIN people p ON p.id = a.owner_person_id
    LEFT JOIN asset_venues v ON v.asset_id = a.id
    ORDER BY a.created_at DESC
");
$assets = [];
while ($row = mysqli_fetch_assoc($assetsResult)) {
    $assets[] = $row;
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
    --row-alt:    rgba(255,255,255,0.02);
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6;
    min-height: 100vh;
  }
  nav {
    padding: 0 32px; height: 60px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(13,11,26,0.95); border-bottom: 1px solid var(--purple-dim);
  }
  .nav-logo { font-family: 'Cinzel', serif; font-weight: 900; font-size: 18px; color: var(--white); letter-spacing: 0.05em; text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-links { display: flex; gap: 20px; align-items: center; }
  .nav-links a { color: var(--muted); text-decoration: none; font-size: 13px; }
  .nav-links a:hover { color: var(--white); }

  main { max-width: 1180px; margin: 0 auto; padding: 32px 24px 80px; }
  .page-title { font-family: 'Cinzel', serif; font-size: 22px; color: var(--white); margin-bottom: 24px; }

  .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
  .stat-card {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 10px;
    padding: 18px 20px;
  }
  .stat-value { font-family: 'Cinzel', serif; font-size: 28px; color: var(--white); font-variant-numeric: tabular-nums; }
  .stat-label { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); margin-top: 4px; }

  .breakdown-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 32px; }
  .breakdown-pill {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 999px;
    padding: 6px 14px; font-size: 12px; color: var(--lilac);
  }
  .breakdown-pill b { color: var(--gold); font-variant-numeric: tabular-nums; }

  .section { margin-bottom: 40px; }
  .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
  .section-title { font-family: 'Cinzel', serif; font-size: 16px; color: var(--white); }

  .filter-links { display: flex; gap: 8px; flex-wrap: wrap; }
  .filter-links a {
    font-size: 12px; color: var(--muted); text-decoration: none; padding: 6px 12px;
    border: 1px solid var(--purple-dim); border-radius: 999px;
  }
  .filter-links a:hover { color: var(--white); border-color: var(--purple); }
  .filter-links a.active { background: var(--purple); color: var(--white); border-color: var(--purple); }

  .table-wrap { overflow-x: auto; border: 1px solid var(--purple-dim); border-radius: 10px; }
  table { width: 100%; border-collapse: collapse; min-width: 720px; }
  th {
    text-align: left; font-family: 'Cinzel', serif; font-size: 10px; letter-spacing: 0.08em;
    text-transform: uppercase; color: var(--purple-lt); padding: 12px 14px;
    background: var(--card); border-bottom: 1px solid var(--purple-dim); white-space: nowrap;
  }
  td { padding: 11px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: top; }
  tr:nth-child(even) td { background: var(--row-alt); }
  tr:last-child td { border-bottom: none; }
  .role-tag {
    display: inline-block; background: var(--purple-dim); border: 1px solid var(--purple);
    color: var(--gold); font-size: 10px; padding: 2px 8px; border-radius: 999px; margin: 1px 2px 1px 0;
  }
  .muted-cell { color: var(--muted); font-size: 12px; }
  .empty-note { color: var(--muted); font-size: 13px; padding: 20px; text-align: center; }
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <div class="nav-links">
    <a href="dashboard.php">My Dashboard</a>
    <a href="logout.php">Log Out</a>
  </div>
</nav>

<main>
  <div class="page-title">Admin Overview</div>

  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-value"><?php echo $totalPeople; ?></div>
      <div class="stat-label">Total People</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo $totalVenues; ?></div>
      <div class="stat-label">Venues on File</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo $totalAssets; ?></div>
      <div class="stat-label">Total Assets</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo $pirateInterest; ?></div>
      <div class="stat-label">Pirate Walk Through</div>
    </div>
  </div>

  <div class="breakdown-row">
    <?php foreach ($typeBreakdown as $t): ?>
      <div class="breakdown-pill"><b><?php echo $t['c']; ?></b> <?php echo htmlspecialchars($t['involvement_type'] ?: 'Unspecified'); ?></div>
    <?php endforeach; ?>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-title">People</div>
      <div class="filter-links">
        <a href="admin.php" class="<?php echo $filter === '' ? 'active' : ''; ?>">All</a>
        <a href="admin.php?filter=Talent" class="<?php echo $filter === 'Talent' ? 'active' : ''; ?>">Talent</a>
        <a href="admin.php?filter=Vendor" class="<?php echo $filter === 'Vendor' ? 'active' : ''; ?>">Vendor</a>
        <a href="admin.php?filter=Venue" class="<?php echo $filter === 'Venue' ? 'active' : ''; ?>">Venue</a>
        <a href="admin.php?filter=Affiliate" class="<?php echo $filter === 'Affiliate' ? 'active' : ''; ?>">Affiliate</a>
        <a href="admin.php?filter=Organizer" class="<?php echo $filter === 'Organizer' ? 'active' : ''; ?>">Organizer</a>
        <a href="admin.php?filter=Subscriber" class="<?php echo $filter === 'Subscriber' ? 'active' : ''; ?>">Subscriber</a>
        <a href="admin.php?filter=pirate" class="<?php echo $filter === 'pirate' ? 'active' : ''; ?>">🏴‍☠️ Pirate Walk Through</a>
      </div>
    </div>
    <div class="table-wrap">
      <?php if ($people): ?>
      <table>
        <tr>
          <th>Name</th><th>Email</th><th>Phone</th><th>Roles</th><th>Type</th><th>Signed Up</th>
        </tr>
        <?php foreach ($people as $p): ?>
        <tr>
          <td><?php echo htmlspecialchars(trim($p['first'] . ' ' . $p['last'])) ?: '<span class="muted-cell">—</span>'; ?></td>
          <td><?php echo htmlspecialchars($p['email']); ?></td>
          <td class="muted-cell"><?php echo htmlspecialchars($p['phone']) ?: '—'; ?></td>
          <td>
            <?php if ($p['roles']): foreach (array_map('trim', explode(',', $p['roles'])) as $r): ?>
              <span class="role-tag"><?php echo htmlspecialchars($r); ?></span>
            <?php endforeach; else: ?><span class="muted-cell">—</span><?php endif; ?>
          </td>
          <td class="muted-cell"><?php echo htmlspecialchars($p['involvement_type']); ?></td>
          <td class="muted-cell"><?php echo $p['date'] ? date('M j, Y', strtotime($p['date'])) : '—'; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?>
        <div class="empty-note">No matches.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-title">Assets</div>
    </div>
    <div class="table-wrap">
      <?php if ($assets): ?>
      <table>
        <tr>
          <th>Name</th><th>Type</th><th>Owner</th><th>Details</th><th>Added</th>
        </tr>
        <?php foreach ($assets as $a): ?>
        <tr>
          <td><?php echo htmlspecialchars($a['name']); ?></td>
          <td><span class="role-tag"><?php echo htmlspecialchars($a['type']); ?></span></td>
          <td><?php echo htmlspecialchars(trim($a['first'] . ' ' . $a['last'])); ?><br><span class="muted-cell"><?php echo htmlspecialchars($a['email']); ?></span></td>
          <td class="muted-cell">
            <?php if ($a['type'] === 'Venue'): ?>
              <?php echo htmlspecialchars($a['address']); ?><?php if ($a['capacity']): ?> · Cap. <?php echo intval($a['capacity']); ?><?php endif; ?><?php if ($a['indoor_outdoor']): ?> · <?php echo htmlspecialchars($a['indoor_outdoor']); ?><?php endif; ?>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td class="muted-cell"><?php echo date('M j, Y', strtotime($a['created_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?>
        <div class="empty-note">No assets yet.</div>
      <?php endif; ?>
    </div>
  </div>
</main>

</body>
</html>
