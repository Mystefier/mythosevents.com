<?php
session_start();
include(__DIR__ . '/../join/logintodatabase.php');

if (!isset($_SESSION['person_id'])) {
    header('Location: /join/?next=' . urlencode('/organizers/edit-event.php'));
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
    die('You must be an approved organizer to manage events.');
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : (isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0);
if (!$event_id) {
    http_response_code(404);
    die('No event specified.');
}

$eventStmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$eventStmt->bind_param("ii", $event_id, $user_id);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();

if (!$event) {
    http_response_code(404);
    die('Event not found, or it is not yours.');
}

$error = '';
$success = '';
$deleted = false;

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteEvent'])) {
    if ($event['cover_image_url'] && strpos($event['cover_image_url'], '/uploads/events/') === 0) {
        $oldPath = __DIR__ . '/..' . $event['cover_image_url'];
        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }
    $delStmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
    $delStmt->bind_param("ii", $event_id, $user_id);
    $delStmt->execute();
    $delStmt->close();
    $deleted = true;
}

// Save edits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveEvent'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $cover_image_url = trim($_POST['cover_image_url'] ?? ($event['cover_image_url'] ?? ''));
    $contact_email = trim($_POST['contact_email'] ?? '');

    if (!$title) {
        $error = 'Event title is required.';
    }

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
                    // Clean up the old uploaded file, if there was one
                    if ($event['cover_image_url'] && strpos($event['cover_image_url'], '/uploads/events/') === 0) {
                        $oldPath = __DIR__ . '/..' . $event['cover_image_url'];
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $cover_image_url = '/uploads/events/' . $filename;
                } else {
                    $error = 'Could not save the uploaded image. Please try again.';
                }
            }
        }
    }

    if (!$error) {
        // Editing content always sends it back for review — content can change after
        // approval, so a fresh look is required before it is public again.
        $newStatus = 'pending_approval';
        $updateStmt = $conn->prepare(
            "UPDATE events SET title = ?, description = ?, cover_image_url = ?, event_type = ?, contact_email = ?, status = ?, rejection_reason = NULL WHERE id = ? AND organizer_id = ?"
        );
        $updateStmt->bind_param(
            "ssssssii",
            $title, $description, $cover_image_url, $event_type, $contact_email, $newStatus, $event_id, $user_id
        );
        if ($updateStmt->execute()) {
            $success = $event['status'] === 'approved'
                ? 'Saved! Since this event was already live, it will be reviewed again before it shows on the public events page.'
                : 'Saved!';
            // Reload the event so the form reflects what is now saved
            $eventStmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
            $eventStmt->bind_param("i", $event_id);
            $eventStmt->execute();
            $event = $eventStmt->get_result()->fetch_assoc();
            $eventStmt->close();
        } else {
            $error = 'Error saving changes. Please try again.';
        }
        $updateStmt->close();
    }
}

$title_val = htmlspecialchars($event['title'] ?? '');
$description_val = htmlspecialchars($event['description'] ?? '');
$event_type_val = htmlspecialchars($event['event_type'] ?? '');
$cover_image_url_val = htmlspecialchars($event['cover_image_url'] ?? '');
$contact_email_val = htmlspecialchars($event['contact_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $deleted ? 'Event Deleted' : 'Edit Event'; ?> — Mythos Events</title>
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
    --red:        #E05555;
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

  .alert { padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 15px; line-height: 1.6; }
  .alert-error { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.4); color: #fca5a5; }
  .alert-success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #86efac; }

  .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .field { margin-bottom: 24px; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px; }
  input[type="text"], input[type="email"], input[type="url"], input[type="file"], textarea {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim); border-radius: 8px;
    padding: 12px 14px; font-size: 15px; font-family: 'Inter', sans-serif; color: var(--white); outline: none;
    transition: border-color 0.2s, background 0.2s;
  }
  input:focus, textarea:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { resize: vertical; min-height: 120px; }
  input[type="file"]::-webkit-file-upload-button {
    background: var(--purple); color: var(--white); border: none; border-radius: 6px;
    padding: 8px 14px; margin-right: 12px; font-family: 'Inter', sans-serif; cursor: pointer;
  }
  .current-cover { width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; display: block; }

  .status-note { font-size: 13px; color: var(--muted); margin: -12px 0 24px; padding: 12px 14px; background: rgba(232,197,71,0.08); border: 1px solid rgba(232,197,71,0.25); border-radius: 8px; }

  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .form-note { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }

  .danger-zone { margin-top: 36px; padding-top: 28px; border-top: 1px solid rgba(224,85,85,0.3); }
  .danger-zone h2 { font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.15em; color: var(--red); margin-bottom: 10px; }
  .danger-zone p { font-size: 13px; margin-bottom: 16px; }
  .delete-btn {
    width: 100%; background: transparent; color: var(--red); border: 1px solid rgba(224,85,85,0.5);
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em; padding: 14px 28px; border-radius: 8px; cursor: pointer;
    transition: background 0.2s;
  }
  .delete-btn:hover { background: rgba(224,85,85,0.12); }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/join/dashboard.php" class="nav-back">← Back to Dashboard</a>
</nav>

<main>
  <?php if ($deleted): ?>

    <h1>Event Deleted</h1>
    <div class="alert alert-success">"<?php echo htmlspecialchars($event['title']); ?>" and any dates scheduled for it have been removed.</div>
    <a href="/join/dashboard.php" class="submit-btn" style="display:block;text-align:center;text-decoration:none;">BACK TO DASHBOARD</a>

  <?php else: ?>

    <h1>Edit Event</h1>
    <p>Update the concept — title, description, and look. Dates are managed separately from your dashboard.</p>

    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <form method="post" class="form-card" enctype="multipart/form-data">
      <input type="hidden" name="event_id" value="<?php echo (int)$event_id; ?>">

      <?php if ($event['status'] === 'approved'): ?>
        <div class="status-note">⚠ This event is currently live. Saving changes here will send it back for review before it is visible again.</div>
      <?php elseif ($event['status'] === 'rejected'): ?>
        <div class="status-note">This event was rejected<?php echo $event['rejection_reason'] ? ': ' . htmlspecialchars($event['rejection_reason']) : '.'; ?> Saving changes resubmits it for review.</div>
      <?php endif; ?>

      <div class="field">
        <label>EVENT TITLE *</label>
        <input type="text" name="title" value="<?php echo $title_val; ?>" required>
      </div>

      <div class="field">
        <label>DESCRIPTION</label>
        <textarea name="description"><?php echo $description_val; ?></textarea>
      </div>

      <div class="field">
        <label>EVENT TYPE</label>
        <input type="text" name="event_type" placeholder="e.g., Festival, Pop-up, Theater, Workshop" value="<?php echo $event_type_val; ?>">
      </div>

      <div class="field">
        <label>COVER IMAGE <span style="font-weight:400;text-transform:none;letter-spacing:0;opacity:0.7;">(optional — JPG, PNG, GIF, or WEBP, up to 5MB)</span></label>
        <?php if ($cover_image_url_val): ?>
          <img class="current-cover" src="<?php echo $cover_image_url_val; ?>" alt="Current cover image">
        <?php endif; ?>
        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
      </div>

      <div class="field">
        <label>OR COVER IMAGE URL <span style="font-weight:400;text-transform:none;letter-spacing:0;opacity:0.7;">(used only if you do not upload a new one)</span></label>
        <input type="url" name="cover_image_url" placeholder="https://..." value="<?php echo $cover_image_url_val; ?>">
      </div>

      <div class="field">
        <label>CONTACT EMAIL</label>
        <input type="email" name="contact_email" value="<?php echo $contact_email_val; ?>">
      </div>

      <button type="submit" name="saveEvent" value="1" class="submit-btn">SAVE CHANGES ✦</button>
    </form>

    <div class="danger-zone">
      <h2>DANGER ZONE</h2>
      <p>Deleting this event removes it permanently, along with every date scheduled for it. This cannot be undone.</p>
      <form method="post" onsubmit="return confirm('Delete \'<?php echo htmlspecialchars(addslashes($event['title'])); ?>\' and every date scheduled for it? This cannot be undone.');">
        <input type="hidden" name="event_id" value="<?php echo (int)$event_id; ?>">
        <button type="submit" name="deleteEvent" value="1" class="delete-btn">DELETE THIS EVENT</button>
      </form>
    </div>

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
