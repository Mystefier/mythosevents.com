<?php
session_start();
include(__DIR__ . '/../join/logintodatabase.php');

if (!isset($_SESSION['person_id'])) {
    header('Location: /join/?next=' . urlencode('/organizers/schedule.php'));
    exit;
}

$user_id = (int)$_SESSION['person_id'];

$userStmt = $conn->prepare("SELECT id, application_status FROM people WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user || $user['application_status'] !== 'approved') {
    http_response_code(403);
    die('You must be an approved organizer to schedule events.');
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$error = '';
$success = '';

// If an event is chosen, make sure it belongs to this organizer and is approved
$event = null;
if ($event_id) {
    $eventStmt = $conn->prepare("SELECT id, title, description, cover_image_url, event_type FROM events WHERE id = ? AND organizer_id = ? AND status = 'approved'");
    $eventStmt->bind_param("ii", $event_id, $user_id);
    $eventStmt->execute();
    $event = $eventStmt->get_result()->fetch_assoc();
    $eventStmt->close();

    if (!$event) {
        http_response_code(404);
        die('Event not found, not yours, or not yet approved.');
    }
}

if ($event && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = trim($_POST['start_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $directions = trim($_POST['directions'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $peatix_url = trim($_POST['peatix_url'] ?? '');

    if (!$start_date) {
        $error = 'Start date is required.';
    } else {
        $insertStmt = $conn->prepare(
            "INSERT INTO event_occurrences (event_id, start_date, start_time, end_date, end_time, location, directions, website, peatix_url, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')"
        );
        $insertStmt->bind_param(
            "isssssss",
            $event_id, $start_date, $start_time, $end_date, $end_time, $location, $directions, $website, $peatix_url
        );
        if ($insertStmt->execute()) {
            $success = 'Scheduled! This date is now live on the public events page.';
            $_POST = [];
        } else {
            $error = 'Error scheduling this date. Please try again.';
        }
        $insertStmt->close();
    }
}

// No event chosen yet: show a picker of this organizer's approved events
$myEvents = [];
if (!$event) {
    $listStmt = $conn->prepare("SELECT id, title, event_type FROM events WHERE organizer_id = ? AND status = 'approved' ORDER BY title ASC");
    $listStmt->bind_param("i", $user_id);
    $listStmt->execute();
    $myEvents = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $listStmt->close();
}

// Existing occurrences for this event, if one is chosen
$occurrences = [];
if ($event) {
    $occStmt = $conn->prepare("SELECT id, start_date, start_time, location, status FROM event_occurrences WHERE event_id = ? ORDER BY start_date ASC");
    $occStmt->bind_param("i", $event_id);
    $occStmt->execute();
    $occurrences = $occStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $occStmt->close();
}

$start_date_val = htmlspecialchars($_POST['start_date'] ?? '');
$start_time_val = htmlspecialchars($_POST['start_time'] ?? '');
$end_date_val = htmlspecialchars($_POST['end_date'] ?? '');
$end_time_val = htmlspecialchars($_POST['end_time'] ?? '');
$location_val = htmlspecialchars($_POST['location'] ?? '');
$directions_val = htmlspecialchars($_POST['directions'] ?? '');
$website_val = htmlspecialchars($_POST['website'] ?? '');
$peatix_url_val = htmlspecialchars($_POST['peatix_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedule an Event — Mythos Events</title>
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
    min-height: 100vh; padding: 40px 20px;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: #fff; animation: twinkle var(--dur) ease-in-out infinite var(--delay); }
  @keyframes twinkle { 0%,100% { opacity: 0.1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.5); } }
  nav {
    position: relative; z-index: 10; margin-bottom: 40px;
    display: flex; align-items: center; justify-content: space-between;
  }
  .nav-logo { font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px; color: var(--white); letter-spacing: 0.05em; text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; }
  .nav-back:hover { color: var(--white); }

  main { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; }
  h1 { font-family: 'Cinzel', serif; font-size: 36px; font-weight: 900; color: var(--white); margin-bottom: 10px; text-shadow: 0 0 40px rgba(107,63,160,0.7); }
  p { color: var(--muted); margin-bottom: 30px; }
  .event-pill { display: inline-block; font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.1em; color: var(--gold); margin-bottom: 4px; }

  .alert {
    padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 15px; line-height: 1.6;
  }
  .alert-error { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.4); color: #fca5a5; }
  .alert-success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #86efac; }

  .form-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 40px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }

  .field { margin-bottom: 24px; }
  label {
    display: block; font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px;
  }
  input[type="text"],
  input[type="date"],
  input[type="time"],
  input[type="url"],
  textarea {
    width: 100%; background: rgba(255,255,255,0.05);
    border: 1px solid var(--purple-dim); border-radius: 8px;
    padding: 12px 14px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none;
    transition: border-color 0.2s, background 0.2s;
  }
  input:focus, textarea:focus {
    border-color: var(--purple-lt); background: rgba(107,63,160,0.1);
  }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { resize: vertical; min-height: 90px; }

  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }

  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px;
    cursor: pointer; transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }

  .form-note { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }

  .picker-list { list-style: none; }
  .picker-item {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 10px; padding: 18px 22px; margin-bottom: 14px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
  }
  .picker-item a {
    background: var(--purple); color: var(--white); text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.1em;
    padding: 10px 18px; border-radius: 6px; white-space: nowrap;
  }
  .picker-item a:hover { background: var(--purple-lt); }
  .empty { text-align: center; padding: 40px 20px; color: var(--muted); }

  .occ-list { margin-top: 30px; }
  .occ-item {
    background: rgba(255,255,255,0.03); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 14px 18px; margin-bottom: 10px; font-size: 14px;
    display: flex; justify-content: space-between; gap: 12px;
  }
  .occ-status { font-size: 11px; letter-spacing: 0.1em; color: var(--gold); text-transform: uppercase; }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/organizers/" class="nav-back">← Back</a>
</nav>

<main>
  <?php if (!$event): ?>
    <h1>Schedule an Event</h1>
    <p>Pick one of your approved events to add a date, place, and directions.</p>

    <?php if (count($myEvents) === 0): ?>
      <div class="empty">You don't have any approved events yet. <a href="/organizers/post.php" style="color: var(--purple-lt);">Create one first →</a></div>
    <?php else: ?>
      <ul class="picker-list">
        <?php foreach ($myEvents as $e): ?>
          <li class="picker-item">
            <div>
              <div><?php echo htmlspecialchars($e['title']); ?></div>
              <?php if ($e['event_type']): ?><div class="event-pill"><?php echo htmlspecialchars($e['event_type']); ?></div><?php endif; ?>
            </div>
            <a href="/organizers/schedule.php?event_id=<?php echo (int)$e['id']; ?>">SCHEDULE ✦</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  <?php else: ?>
    <div class="event-pill"><?php echo htmlspecialchars($event['event_type'] ?: 'EVENT'); ?></div>
    <h1>Schedule: <?php echo htmlspecialchars($event['title']); ?></h1>
    <p>Add the date, place, and directions for this occurrence. It goes live on the public events page as soon as you submit — no extra approval needed.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="form-card">
      <div class="row">
        <div class="field">
          <label>START DATE *</label>
          <input type="date" name="start_date" value="<?php echo $start_date_val; ?>" required>
        </div>
        <div class="field">
          <label>START TIME</label>
          <input type="time" name="start_time" value="<?php echo $start_time_val; ?>">
        </div>
      </div>

      <div class="row">
        <div class="field">
          <label>END DATE</label>
          <input type="date" name="end_date" value="<?php echo $end_date_val; ?>">
        </div>
        <div class="field">
          <label>END TIME</label>
          <input type="time" name="end_time" value="<?php echo $end_time_val; ?>">
        </div>
      </div>

      <div class="field">
        <label>LOCATION</label>
        <input type="text" name="location" placeholder="City, venue, or area" value="<?php echo $location_val; ?>">
      </div>

      <div class="field">
        <label>HOW TO GET THERE</label>
        <textarea name="directions" placeholder="Parking, entrance, landmarks — anything that helps people actually find it"><?php echo $directions_val; ?></textarea>
      </div>

      <div class="field">
        <label>WEBSITE / TICKETING</label>
        <input type="url" name="website" placeholder="https://..." value="<?php echo $website_val; ?>">
      </div>

      <div class="field">
        <label>PEATIX LINK <span style="font-weight:400;text-transform:none;letter-spacing:0;opacity:0.7;">(optional — e.g. for Malaysia-friendly payment options)</span></label>
        <input type="url" name="peatix_url" placeholder="https://peatix.com/event/..." value="<?php echo $peatix_url_val; ?>">
      </div>

      <button type="submit" class="submit-btn">SCHEDULE THIS DATE ✦</button>
      <p class="form-note">Want another date for the same event? Submit this, then come back and schedule again.</p>
    </form>

    <?php if (count($occurrences) > 0): ?>
      <div class="occ-list">
        <p style="margin-bottom: 14px; color: var(--white); font-family: 'Cinzel', serif; font-size: 14px;">Already scheduled:</p>
        <?php foreach ($occurrences as $o): ?>
          <div class="occ-item">
            <div>
              <?php
              $d = new DateTime($o['start_date']);
              echo $d->format('M j, Y');
              if ($o['start_time']) echo ' ' . date('g:ia', strtotime($o['start_time']));
              if ($o['location']) echo ' — ' . htmlspecialchars($o['location']);
              ?>
            </div>
            <div class="occ-status"><?php echo htmlspecialchars($o['status']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>

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
