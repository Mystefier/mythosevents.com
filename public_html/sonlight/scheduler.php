<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: /join/login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('../join/logintodatabase.php');

$personId = intval($_SESSION['person_id']);

$stmt = mysqli_prepare($conn, "SELECT first, roles FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$person = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

$isSonlightMember = $person && strpos((string)$person['roles'], 'Sonlight Drama Team') !== false;

$statusMessage = '';
$statusType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isSonlightMember) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'signup') {
        $category = (isset($_POST['category']) && in_array($_POST['category'], ['Bible', 'Theater'], true)) ? $_POST['category'] : null;
        $eventDate = isset($_POST['event_date']) ? $_POST['event_date'] : '';
        $question = trim(isset($_POST['question']) ? $_POST['question'] : '');
        $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate) && strtotime($eventDate) >= strtotime('today');

        if (!$category || !$validDate) {
            $statusType = 'error';
            $statusMessage = "Something about that request did not look right. Please try again.";
        } elseif ($question === '') {
            $statusType = 'error';
            $statusMessage = "Please enter your question before signing up.";
        } else {
            $checkStmt = mysqli_prepare($conn, "SELECT event_date FROM sonlight_signups WHERE person_id = ? AND category = ? AND event_date >= CURDATE()");
            mysqli_stmt_bind_param($checkStmt, "is", $personId, $category);
            mysqli_stmt_execute($checkStmt);
            $existing = mysqli_stmt_get_result($checkStmt)->fetch_assoc();
            mysqli_stmt_close($checkStmt);

            if ($existing) {
                $statusType = 'error';
                $statusMessage = "You already have an upcoming $category signup on " . date('F j', strtotime($existing['event_date'])) . ". You can sign up again once that date passes, or cancel it below first.";
            } else {
                $insertStmt = mysqli_prepare($conn, "INSERT INTO sonlight_signups (person_id, category, event_date, question) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($insertStmt, "isss", $personId, $category, $eventDate, $question);
                if (mysqli_stmt_execute($insertStmt)) {
                    $statusType = 'success';
                    $statusMessage = "You're signed up for $category on " . date('F j', strtotime($eventDate)) . "!";
                } else {
                    $statusType = 'error';
                    $statusMessage = "That slot was just taken by someone else — please pick another date.";
                }
                mysqli_stmt_close($insertStmt);
            }
        }
    } elseif ($action === 'cancel') {
        $signupId = intval(isset($_POST['signup_id']) ? $_POST['signup_id'] : 0);
        $delStmt = mysqli_prepare($conn, "DELETE FROM sonlight_signups WHERE id = ? AND person_id = ? AND event_date >= CURDATE()");
        mysqli_stmt_bind_param($delStmt, "ii", $signupId, $personId);
        mysqli_stmt_execute($delStmt);
        mysqli_stmt_close($delStmt);
        $statusType = 'success';
        $statusMessage = "Signup canceled — that slot is open again.";
    } elseif ($action === 'edit') {
        $signupId = intval(isset($_POST['signup_id']) ? $_POST['signup_id'] : 0);
        $question = trim(isset($_POST['question']) ? $_POST['question'] : '');
        if ($question !== '') {
            $updStmt = mysqli_prepare($conn, "UPDATE sonlight_signups SET question = ? WHERE id = ? AND person_id = ? AND event_date >= CURDATE()");
            mysqli_stmt_bind_param($updStmt, "sii", $question, $signupId, $personId);
            mysqli_stmt_execute($updStmt);
            mysqli_stmt_close($updStmt);
            $statusType = 'success';
            $statusMessage = "Question updated.";
        }
    }
}

// Next 10 upcoming Sundays
$sundays = [];
$d = new DateTime('today');
$daysUntilSunday = (7 - (int)$d->format('w')) % 7;
$d->modify("+$daysUntilSunday days");
for ($i = 0; $i < 10; $i++) {
    $sundays[] = $d->format('Y-m-d');
    $d->modify('+7 days');
}

$placeholders = implode(',', array_fill(0, count($sundays), '?'));
$types = str_repeat('s', count($sundays));
$slotStmt = mysqli_prepare($conn, "SELECT s.*, p.first, p.last FROM sonlight_signups s JOIN people p ON s.person_id = p.id WHERE s.event_date IN ($placeholders)");
mysqli_stmt_bind_param($slotStmt, $types, ...$sundays);
mysqli_stmt_execute($slotStmt);
$slotResult = mysqli_stmt_get_result($slotStmt);
$slotsByDate = [];
while ($row = mysqli_fetch_assoc($slotResult)) {
    $slotsByDate[$row['event_date']][$row['category']] = $row;
}
mysqli_stmt_close($slotStmt);

$myFutureClaims = ['Bible' => null, 'Theater' => null];
foreach ($slotsByDate as $dateKey => $cats) {
    foreach ($cats as $cat => $row) {
        if ((int)$row['person_id'] === $personId) {
            $myFutureClaims[$cat] = $dateKey;
        }
    }
}

$claimTarget = isset($_GET['claim']) ? $_GET['claim'] : '';
$editTarget = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stage Scheduler — Sonlight</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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
    --sun-red: #E0564F;
  }
  body {
    background: var(--sun-bg); color: var(--sun-text);
    font-family: 'Nunito', sans-serif; font-size: 16px; line-height: 1.6;
    min-height: 100vh; display: flex; flex-direction: column;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; }
  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: var(--sun-card); border-bottom: 2px solid var(--sun-border);
  }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 19px; color: var(--sun-text); text-decoration: none; }
  .nav-logo span { color: var(--sun-primary); }
  .nav-back { font-size: 14px; color: var(--sun-muted); text-decoration: none; font-weight: 600; }
  .nav-back:hover { color: var(--sun-primary); }
  main { flex: 1; max-width: 780px; margin: 0 auto; padding: 48px 20px 80px; width: 100%; }
  .page-header { text-align: center; margin-bottom: 36px; }
  .eyebrow { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 12px; letter-spacing: 0.15em; color: var(--sun-primary); text-transform: uppercase; margin-bottom: 10px; }
  .page-header h1 { font-weight: 800; font-size: clamp(28px, 5vw, 38px); color: var(--sun-text); margin-bottom: 10px; }
  .page-header p { color: var(--sun-muted); font-size: 15px; max-width: 520px; margin: 0 auto; }

  .status-message { margin-bottom: 28px; padding: 16px 20px; border-radius: 12px; font-weight: 600; font-size: 15px; }
  .status-message.success { background: rgba(63,166,107,0.12); border: 2px solid rgba(63,166,107,0.3); color: var(--sun-green); }
  .status-message.error { background: rgba(224,86,79,0.1); border: 2px solid rgba(224,86,79,0.3); color: var(--sun-red); }

  .gate-card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 18px;
    padding: 40px; text-align: center;
  }
  .gate-card p { color: var(--sun-muted); margin-bottom: 20px; }

  .week-card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 16px;
    padding: 22px 24px; margin-bottom: 18px;
  }
  .week-date { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 16px; margin-bottom: 16px; }
  .slot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .slot {
    border-radius: 12px; padding: 16px; border: 2px solid var(--sun-border);
  }
  .slot.bible { background: rgba(255,193,69,0.1); }
  .slot.theater { background: rgba(46,169,200,0.08); }
  .slot-label { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
  .slot-open { color: var(--sun-muted); font-size: 14px; margin-bottom: 10px; }
  .slot-claimed-by { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
  .slot-question { font-size: 13.5px; color: var(--sun-muted); font-style: italic; line-height: 1.5; }
  .btn {
    display: inline-block; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13px;
    padding: 9px 18px; border-radius: 30px; text-decoration: none; border: none; cursor: pointer;
    transition: transform 0.1s, opacity 0.15s;
  }
  .btn:hover { transform: translateY(-1px); }
  .btn-primary { background: var(--sun-primary); color: #fff; }
  .btn-primary:hover { background: var(--sun-primary-dk); }
  .btn-ghost { background: transparent; color: var(--sun-muted); border: 2px solid var(--sun-border); padding: 7px 16px; }
  .btn-danger { background: transparent; color: var(--sun-red); border: 2px solid rgba(224,86,79,0.3); padding: 7px 16px; }
  .btn-sm { font-size: 12px; padding: 7px 14px; }
  .mine { border-color: var(--sun-primary); }

  .claim-form textarea {
    width: 100%; border: 2px solid var(--sun-border); border-radius: 10px; padding: 10px 12px;
    font-family: 'Nunito', sans-serif; font-size: 14px; color: var(--sun-text); resize: vertical;
    min-height: 70px; margin-bottom: 10px; outline: none;
  }
  .claim-form textarea:focus { border-color: var(--sun-primary); }
  .action-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }

  footer { text-align: center; padding: 24px; color: var(--sun-muted); font-size: 13px; }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 600; }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .slot-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
  <a href="/sonlight/" class="nav-back">← Back</a>
</nav>

<main>
  <div class="page-header">
    <div class="eyebrow">Stage Scheduler</div>
    <h1>Who's Bringing What?</h1>
    <p>Sign up to bring the Bible question or the theater question for an upcoming Sunday. One upcoming date at a time per category — once your date passes, you're free to sign up again.</p>
  </div>

  <?php if ($statusMessage): ?>
    <div class="status-message <?php echo htmlspecialchars($statusType); ?>"><?php echo htmlspecialchars($statusMessage); ?></div>
  <?php endif; ?>

  <?php if (!$isSonlightMember): ?>
    <div class="gate-card">
      <h3 style="margin-bottom:10px;">This One's for Sonlight Members</h3>
      <p>Add "Sonlight Drama Team" to your roles on your Mythos Events profile to get access to the scheduler.</p>
      <a href="/join/edit-profile.php" class="btn btn-primary">Update My Profile</a>
    </div>
  <?php else: ?>

    <?php foreach ($sundays as $date):
      $bible = isset($slotsByDate[$date]['Bible']) ? $slotsByDate[$date]['Bible'] : null;
      $theater = isset($slotsByDate[$date]['Theater']) ? $slotsByDate[$date]['Theater'] : null;
    ?>
    <div class="week-card">
      <div class="week-date">🗓️ <?php echo date('l, F j', strtotime($date)); ?></div>
      <div class="slot-grid">

        <div class="slot bible">
          <div class="slot-label">📖 Bible Question</div>
          <?php if ($bible): ?>
            <div class="slot-claimed-by"><?php echo htmlspecialchars($bible['first'] . ' ' . $bible['last']); ?></div>
            <?php if ((int)$bible['person_id'] === $personId): ?>
              <?php if ($editTarget === (int)$bible['id']): ?>
                <form method="post" class="claim-form">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="signup_id" value="<?php echo (int)$bible['id']; ?>">
                  <textarea name="question" required><?php echo htmlspecialchars($bible['question']); ?></textarea>
                  <div class="action-row">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <a href="/sonlight/scheduler.php" class="btn btn-ghost btn-sm">Cancel Edit</a>
                  </div>
                </form>
              <?php else: ?>
                <div class="slot-question">"<?php echo htmlspecialchars($bible['question']); ?>"</div>
                <div class="action-row">
                  <a href="?edit=<?php echo (int)$bible['id']; ?>" class="btn btn-ghost btn-sm">Edit</a>
                  <form method="post" onsubmit="return confirm('Cancel your Bible question signup for this date?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="signup_id" value="<?php echo (int)$bible['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="slot-question">"<?php echo htmlspecialchars($bible['question']); ?>"</div>
            <?php endif; ?>
          <?php else: ?>
            <div class="slot-open">Open</div>
            <?php if ($claimTarget === $date . '|Bible'): ?>
              <form method="post" class="claim-form">
                <input type="hidden" name="action" value="signup">
                <input type="hidden" name="category" value="Bible">
                <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($date); ?>">
                <textarea name="question" placeholder="What's your question?" required></textarea>
                <div class="action-row">
                  <button type="submit" class="btn btn-primary btn-sm">Sign Me Up</button>
                  <a href="/sonlight/scheduler.php" class="btn btn-ghost btn-sm">Cancel</a>
                </div>
              </form>
            <?php elseif ($myFutureClaims['Bible']): ?>
              <p style="font-size:12.5px;color:var(--sun-muted);">You're already signed up for <?php echo date('M j', strtotime($myFutureClaims['Bible'])); ?></p>
            <?php else: ?>
              <a href="?claim=<?php echo urlencode($date . '|Bible'); ?>" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div class="slot theater">
          <div class="slot-label">🎭 Theater Question</div>
          <?php if ($theater): ?>
            <div class="slot-claimed-by"><?php echo htmlspecialchars($theater['first'] . ' ' . $theater['last']); ?></div>
            <?php if ((int)$theater['person_id'] === $personId): ?>
              <?php if ($editTarget === (int)$theater['id']): ?>
                <form method="post" class="claim-form">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="signup_id" value="<?php echo (int)$theater['id']; ?>">
                  <textarea name="question" required><?php echo htmlspecialchars($theater['question']); ?></textarea>
                  <div class="action-row">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <a href="/sonlight/scheduler.php" class="btn btn-ghost btn-sm">Cancel Edit</a>
                  </div>
                </form>
              <?php else: ?>
                <div class="slot-question">"<?php echo htmlspecialchars($theater['question']); ?>"</div>
                <div class="action-row">
                  <a href="?edit=<?php echo (int)$theater['id']; ?>" class="btn btn-ghost btn-sm">Edit</a>
                  <form method="post" onsubmit="return confirm('Cancel your theater question signup for this date?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="signup_id" value="<?php echo (int)$theater['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="slot-question">"<?php echo htmlspecialchars($theater['question']); ?>"</div>
            <?php endif; ?>
          <?php else: ?>
            <div class="slot-open">Open</div>
            <?php if ($claimTarget === $date . '|Theater'): ?>
              <form method="post" class="claim-form">
                <input type="hidden" name="action" value="signup">
                <input type="hidden" name="category" value="Theater">
                <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($date); ?>">
                <textarea name="question" placeholder="What's your question?" required></textarea>
                <div class="action-row">
                  <button type="submit" class="btn btn-primary btn-sm">Sign Me Up</button>
                  <a href="/sonlight/scheduler.php" class="btn btn-ghost btn-sm">Cancel</a>
                </div>
              </form>
            <?php elseif ($myFutureClaims['Theater']): ?>
              <p style="font-size:12.5px;color:var(--sun-muted);">You're already signed up for <?php echo date('M j', strtotime($myFutureClaims['Theater'])); ?></p>
            <?php else: ?>
              <a href="?claim=<?php echo urlencode($date . '|Theater'); ?>" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>

      </div>
    </div>
    <?php endforeach; ?>

  <?php endif; ?>
</main>

<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; a Mythos Events group &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">Questions?</a></p>
</footer>

</body>
</html>
