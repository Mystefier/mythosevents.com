<?php
session_start();
include(__DIR__ . '/../join/logintodatabase.php');

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /join/?next=' . urlencode('/organizers/post.php'));
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Check if organizer with approved status
$userStmt = $conn->prepare("SELECT id, first, email, application_status FROM people WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user || $user['application_status'] !== 'approved') {
    http_response_code(403);
    die('You must be an approved organizer to post events.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');

    // Validation
    if (!$title) {
        $error = 'Event title is required.';
    } elseif (!$start_date) {
        $error = 'Start date is required.';
    } else {
        // Insert event
        $insertStmt = $conn->prepare(
            "INSERT INTO events (organizer_id, title, description, event_type, start_date, start_time, end_date, end_time, location, website, contact_email, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_approval')"
        );
        $insertStmt->bind_param(
            "isssssssss",
            $user_id, $title, $description, $event_type, $start_date, $start_time, $end_date, $end_time, $location, $website, $contact_email
        );
        if ($insertStmt->execute()) {
            $success = 'Event submitted for approval! We\'ll review it and get back to you shortly.';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Error submitting event. Please try again.';
        }
        $insertStmt->close();
    }
}

$title_val = htmlspecialchars($_POST['title'] ?? '');
$description_val = htmlspecialchars($_POST['description'] ?? '');
$event_type_val = htmlspecialchars($_POST['event_type'] ?? '');
$start_date_val = htmlspecialchars($_POST['start_date'] ?? '');
$start_time_val = htmlspecialchars($_POST['start_time'] ?? '');
$end_date_val = htmlspecialchars($_POST['end_date'] ?? '');
$end_time_val = htmlspecialchars($_POST['end_time'] ?? '');
$location_val = htmlspecialchars($_POST['location'] ?? '');
$website_val = htmlspecialchars($_POST['website'] ?? '');
$contact_email_val = htmlspecialchars($_POST['contact_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post an Event — Mythos Events</title>
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
  input[type="email"],
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
  textarea { resize: vertical; min-height: 120px; }

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
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/organizers/" class="nav-back">← Back</a>
</nav>

<main>
  <h1>Post an Event</h1>
  <p>Share your event with the Mythos Events network. Events are reviewed before going live.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post" class="form-card">
    <div class="field">
      <label>EVENT TITLE *</label>
      <input type="text" name="title" placeholder="e.g., Moonlit Masquerade Ball" value="<?php echo $title_val; ?>" required>
    </div>

    <div class="field">
      <label>DESCRIPTION</label>
      <textarea name="description" placeholder="Tell people what to expect..."><?php echo $description_val; ?></textarea>
    </div>

    <div class="field">
      <label>EVENT TYPE</label>
      <input type="text" name="event_type" placeholder="e.g., Festival, Pop-up, Theater, Workshop" value="<?php echo $event_type_val; ?>">
    </div>

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
      <label>WEBSITE / TICKETING</label>
      <input type="url" name="website" placeholder="https://..." value="<?php echo $website_val; ?>">
    </div>

    <div class="field">
      <label>CONTACT EMAIL</label>
      <input type="email" name="contact_email" placeholder="Where people can reach you" value="<?php echo $contact_email_val; ?>">
    </div>

    <button type="submit" class="submit-btn">SUBMIT FOR APPROVAL ✦</button>
    <p class="form-note">Events are reviewed and published within 24 hours.</p>
  </form>
</main>

<script>
// Twinkling stars background
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
