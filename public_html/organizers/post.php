<?php
session_start();
include(__DIR__ . '/../join/logintodatabase.php');

// Check if logged in
if (!isset($_SESSION['person_id'])) {
    header('Location: /join/?next=' . urlencode('/organizers/post.php'));
    exit;
}

$user_id = (int)$_SESSION['person_id'];

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
    $cover_image_url = trim($_POST['cover_image_url'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');

    // Validation
    if (!$title) {
        $error = 'Event title is required.';
    }

    // An uploaded file takes priority over a pasted URL
    if (!$error && isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cover_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'There was a problem uploading that image. Please try again.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'Image must be smaller than 5MB.';
        } else {
            $imageInfo = @getimagesize($file['tmp_name']);
            $allowedTypes = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG  => 'png',
                IMAGETYPE_GIF  => 'gif',
                IMAGETYPE_WEBP => 'webp',
            ];
            if (!$imageInfo || !isset($allowedTypes[$imageInfo[2]])) {
                $error = 'That file isn\'t a valid image. Please use a JPG, PNG, GIF, or WEBP.';
            } else {
                $destDir = __DIR__ . '/../uploads/events/';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $filename = 'event_' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$imageInfo[2]];
                if (move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
                    $cover_image_url = '/uploads/events/' . $filename;
                } else {
                    $error = 'Could not save the uploaded image. Please try again.';
                }
            }
        }
    }

    if (!$error) {
        // Insert event
        $insertStmt = $conn->prepare(
            "INSERT INTO events (organizer_id, title, description, cover_image_url, event_type, contact_email, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending_approval')"
        );
        $insertStmt->bind_param(
            "isssss",
            $user_id, $title, $description, $cover_image_url, $event_type, $contact_email
        );
        if ($insertStmt->execute()) {
            $success = 'Event submitted for approval! Once it\'s approved, you can schedule it — pick a date, place, and directions from your dashboard.';
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
$cover_image_url_val = htmlspecialchars($_POST['cover_image_url'] ?? '');
$contact_email_val = htmlspecialchars($_POST['contact_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create an Event — Mythos Events</title>
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
  input[type="url"],
  input[type="file"],
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
  input[type="file"]::-webkit-file-upload-button {
    background: var(--purple); color: var(--white); border: none;
    border-radius: 6px; padding: 8px 14px; margin-right: 12px;
    font-family: 'Inter', sans-serif; cursor: pointer;
  }

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
  <h1>Create an Event</h1>
  <p>This is the concept — the title, description, and look of your event. Once it's approved, you'll schedule one or more dates for it with the time, place, and directions.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post" class="form-card" enctype="multipart/form-data">
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

    <div class="field">
      <label>COVER IMAGE <span style="font-weight:400;text-transform:none;letter-spacing:0;opacity:0.7;">(optional — JPG, PNG, GIF, or WEBP, up to 5MB)</span></label>
      <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
    </div>

    <div class="field">
      <label>OR COVER IMAGE URL <span style="font-weight:400;text-transform:none;letter-spacing:0;opacity:0.7;">(a link to an image hosted elsewhere — used only if you don't upload one)</span></label>
      <input type="url" name="cover_image_url" placeholder="https://..." value="<?php echo $cover_image_url_val; ?>">
    </div>

    <div class="field">
      <label>CONTACT EMAIL</label>
      <input type="email" name="contact_email" placeholder="Where people can reach you" value="<?php echo $contact_email_val; ?>">
    </div>

    <button type="submit" class="submit-btn">SUBMIT FOR APPROVAL ✦</button>
    <p class="form-note">Events are reviewed within 24 hours. Dates and locations come next, once approved.</p>
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
