<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

// Open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$statusType = 'error';
$status = 'Invalid request. Please submit the form.';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = isset($_POST["firstName"]) ? $_POST["firstName"] : '';
    $lastName = isset($_POST["lastName"]) ? $_POST["lastName"] : '';
    $phoneNumber = isset($_POST["phoneNumber"]) ? $_POST["phoneNumber"] : '';
    $dob = isset($_POST["dob"]) && $_POST["dob"] !== '' ? $_POST["dob"] : null;
    $message = isset($_POST["message"]) ? $_POST["message"] : '';
    $description = isset($_POST["description"]) ? $_POST["description"] : '';
    $website = isset($_POST["website"]) ? $_POST["website"] : '';
    $roles = isset($_POST["roles"]) ? implode(", ", $_POST["roles"]) : '';

    // No address means no restriction (worldwide) — don't store a radius with nothing to center it on
    $serviceAreaAddress = isset($_POST["serviceAreaAddress"]) ? trim($_POST["serviceAreaAddress"]) : '';
    $serviceAreaLatitude = ($serviceAreaAddress !== '' && isset($_POST["serviceAreaLatitude"]) && $_POST["serviceAreaLatitude"] !== '') ? floatval($_POST["serviceAreaLatitude"]) : null;
    $serviceAreaLongitude = ($serviceAreaAddress !== '' && isset($_POST["serviceAreaLongitude"]) && $_POST["serviceAreaLongitude"] !== '') ? floatval($_POST["serviceAreaLongitude"]) : null;
    $serviceAreaRadius = $serviceAreaAddress !== '' ? (isset($_POST["serviceAreaRadius"]) ? intval($_POST["serviceAreaRadius"]) : 30) : null;

    // Derive involvement type from core Mythos roles (group memberships live in group_memberships table)
    $roleToInvolvementType = [
        'Vendor'              => 'Vendor',
        'Organizer'           => 'Organizer',
        'volunteer'           => 'Talent',
        'Sales'               => 'Affiliate',
        'Performer'           => 'Talent',
        'Artist'              => 'Talent',
        'Operations'          => 'Talent',
        'Venue Manager/Owner' => 'Venue',
        'Other'               => 'Talent',
    ];
    $selectedRoles = isset($_POST["roles"]) ? $_POST["roles"] : [];
    $involvementTypes = [];
    foreach ($selectedRoles as $role) {
        if (isset($roleToInvolvementType[$role]) && !in_array($roleToInvolvementType[$role], $involvementTypes)) {
            $involvementTypes[] = $roleToInvolvementType[$role];
        }
    }
    $involvementType = $involvementTypes ? implode(", ", $involvementTypes) : 'Talent';

    $updateSql = "UPDATE people SET first = ?, last = ?, phone = ?, dob = ?, message = ?, roles = ?, description = ?, website = ?, service_area_address = ?, service_area_latitude = ?, service_area_longitude = ?, service_area_radius_miles = ?, involvement_type = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssddisi",
        $firstName, $lastName, $phoneNumber, $dob, $message, $roles, $description, $website,
        $serviceAreaAddress, $serviceAreaLatitude, $serviceAreaLongitude, $serviceAreaRadius, $involvementType, $personId
    );

    if (mysqli_stmt_execute($stmt)) {
        $statusType = 'success';
        $status = 'Your profile has been updated successfully.';
    } else {
        $statusType = 'error';
        $status = 'Something went wrong updating your profile. Please try again.';
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Updated — Mythos Events</title>
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
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column;
    overflow-x: hidden;
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
  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; position: relative; z-index: 1; }
  .wrap { width: 100%; max-width: 440px; text-align: center; }
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 38px);
    color: var(--white); margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .status-card { background: var(--card); border-radius: 14px; padding: 32px 36px; margin-bottom: 28px; }
  .status-card.success { border: 1px solid rgba(82,200,122,0.4); }
  .status-card.error   { border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin: 0; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>
</nav>

<main>
  <div class="wrap">
    <div class="status-icon"><?php echo $statusType === 'success' ? '✦' : '✗'; ?></div>
    <h1><?php echo $statusType === 'success' ? 'Profile Updated' : 'Update Failed'; ?></h1>
    <div class="status-card <?php echo $statusType; ?>">
      <p><?php echo htmlspecialchars($status); ?></p>
    </div>
    <a href="dashboard.php" class="btn-primary">Back to Dashboard ✦</a>
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
