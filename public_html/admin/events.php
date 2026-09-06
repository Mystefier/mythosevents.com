<?php
session_start();
include(__DIR__ . '/../join/logintodatabase.php');

// Check if logged in as admin
if (!isset($_SESSION['user_id'])) {
    header('Location: /join/?next=' . urlencode('/admin/events.php'));
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$userStmt = $conn->prepare("SELECT id, first, email FROM people WHERE id = ? AND email LIKE '%@mythosevents.com'");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
    http_response_code(403);
    die('Admin access only.');
}

$message = '';
$action = $_GET['action'] ?? '';
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id) {
    $status = $_POST['status'] ?? '';
    $rejection_reason = trim($_POST['rejection_reason'] ?? '');

    if ($status === 'approved') {
        $updateStmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE id = ?");
        $updateStmt->bind_param("i", $event_id);
        if ($updateStmt->execute()) {
            $message = "✓ Event approved.";
        }
        $updateStmt->close();
    } elseif ($status === 'rejected') {
        $updateStmt = $conn->prepare("UPDATE events SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $updateStmt->bind_param("si", $rejection_reason, $event_id);
        if ($updateStmt->execute()) {
            $message = "✗ Event rejected.";
        }
        $updateStmt->close();
    }
}

// Get pending events
$pendingStmt = $conn->prepare("
    SELECT e.id, e.title, e.description, e.event_type, e.start_date, e.start_time, e.location,
           e.website, e.contact_email, e.created_at, p.first, p.last, p.email
    FROM events e
    JOIN people p ON e.organizer_id = p.id
    WHERE e.status = 'pending_approval'
    ORDER BY e.created_at DESC
");
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();
$pending = $pendingResult->fetch_all(MYSQLI_ASSOC);
$pendingStmt->close();

// Get approved events
$approvedStmt = $conn->prepare("
    SELECT e.id, e.title, e.event_type, e.start_date, e.start_time, e.location,
           p.first, p.last, e.updated_at
    FROM events e
    JOIN people p ON e.organizer_id = p.id
    WHERE e.status = 'approved'
    ORDER BY e.start_date ASC
");
$approvedStmt->execute();
$approvedResult = $approvedStmt->get_result();
$approved = $approvedResult->fetch_all(MYSQLI_ASSOC);
$approvedStmt->close();

// Get rejected events
$rejectedStmt = $conn->prepare("
    SELECT e.id, e.title, e.event_type, e.start_date, e.rejection_reason,
           p.first, p.last, e.updated_at
    FROM events e
    JOIN people p ON e.organizer_id = p.id
    WHERE e.status = 'rejected'
    ORDER BY e.updated_at DESC
");
$rejectedStmt->execute();
$rejectedResult = $rejectedStmt->get_result();
$rejected = $rejectedResult->fetch_all(MYSQLI_ASSOC);
$rejectedStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Moderation — Mythos Events Admin</title>
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
    --success:    #22c55e;
    --error:      #ef4444;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 15px; line-height: 1.6;
    min-height: 100vh; padding: 20px;
  }
  nav { display: flex; gap: 20px; margin-bottom: 30px; align-items: center; border-bottom: 1px solid var(--purple-dim); padding-bottom: 15px; }
  .nav-logo { font-family: 'Cinzel', serif; font-weight: 900; font-size: 18px; color: var(--white); text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-link { font-size: 13px; color: var(--muted); text-decoration: none; }
  .nav-link:hover { color: var(--white); }

  .container { max-width: 1200px; margin: 0 auto; }
  h1 { font-family: 'Cinzel', serif; font-size: 28px; font-weight: 900; color: var(--white); margin-bottom: 8px; }
  .subtitle { color: var(--muted); margin-bottom: 30px; font-size: 14px; }

  .message {
    padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;
    background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #86efac;
  }

  .tabs { display: flex; gap: 20px; border-bottom: 1px solid var(--purple-dim); margin-bottom: 30px; }
  .tab-btn {
    background: none; border: none; color: var(--muted); font-family: 'Cinzel', serif;
    font-size: 13px; letter-spacing: 0.1em; padding: 12px 0; cursor: pointer;
    border-bottom: 2px solid transparent; transition: color 0.2s, border-color 0.2s;
  }
  .tab-btn.active { color: var(--white); border-bottom-color: var(--gold); }

  .tab-content { display: none; }
  .tab-content.active { display: block; }

  .event-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 24px; margin-bottom: 20px;
  }
  .event-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; }
  .event-title { font-family: 'Cinzel', serif; font-size: 16px; font-weight: 700; color: var(--white); }
  .event-type { font-size: 12px; color: var(--gold); letter-spacing: 0.05em; }
  .event-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin: 16px 0; font-size: 14px; }
  .meta-item { color: var(--muted); }
  .meta-label { font-weight: 600; color: var(--purple-lt); }
  .event-description { color: var(--muted); line-height: 1.7; margin: 16px 0; }
  .event-organizer { font-size: 13px; color: var(--muted); margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--purple-dim); }

  .event-actions { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
  .btn {
    padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Cinzel', serif;
    font-size: 12px; letter-spacing: 0.1em; transition: background 0.2s, transform 0.15s;
  }
  .btn-approve {
    background: var(--success); color: var(--midnight);
  }
  .btn-approve:hover { background: #16a34a; transform: translateY(-1px); }
  .btn-reject {
    background: var(--error); color: var(--white);
  }
  .btn-reject:hover { background: #dc2626; transform: translateY(-1px); }

  .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 100; align-items: center; justify-content: center; }
  .modal.active { display: flex; }
  .modal-content {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 12px;
    padding: 28px; max-width: 400px; width: 90%;
  }
  .modal-title { font-family: 'Cinzel', serif; font-size: 18px; font-weight: 700; color: var(--white); margin-bottom: 16px; }
  .form-group { margin-bottom: 16px; }
  label { display: block; font-size: 12px; letter-spacing: 0.1em; color: var(--purple-lt); margin-bottom: 6px; }
  textarea, input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim); border-radius: 6px; padding: 10px; color: var(--white); font-family: 'Inter', sans-serif; }
  textarea:focus, input:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); outline: none; }
  .modal-actions { display: flex; gap: 12px; margin-top: 20px; }
  .modal-actions button { flex: 1; padding: 10px; border: none; border-radius: 6px; cursor: pointer; }
  .modal-actions .cancel { background: var(--purple-dim); color: var(--white); }
  .modal-actions .confirm { background: var(--error); color: var(--white); }

  .empty { text-align: center; padding: 40px; color: var(--muted); }
  .count-badge {
    display: inline-block; background: var(--purple); color: var(--white);
    font-size: 11px; padding: 4px 10px; border-radius: 12px; margin-left: 8px;
  }

  @media (max-width: 768px) {
    .event-meta { grid-template-columns: 1fr; }
    .event-actions { flex-direction: column; }
    .btn { width: 100%; }
  }
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/admin/events.php" class="nav-link">Events</a>
  <a href="/join/account" class="nav-link">Account</a>
</nav>

<div class="container">
  <h1>Event Moderation</h1>
  <p class="subtitle">Review and approve events from organizers</p>

  <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('pending')">
      PENDING <span class="count-badge"><?php echo count($pending); ?></span>
    </button>
    <button class="tab-btn" onclick="switchTab('approved')">
      APPROVED <span class="count-badge"><?php echo count($approved); ?></span>
    </button>
    <button class="tab-btn" onclick="switchTab('rejected')">
      REJECTED <span class="count-badge"><?php echo count($rejected); ?></span>
    </button>
  </div>

  <!-- PENDING TAB -->
  <div id="pending" class="tab-content active">
    <?php if (count($pending) === 0): ?>
      <div class="empty">✓ No pending events. All caught up!</div>
    <?php else: ?>
      <?php foreach ($pending as $event): ?>
        <div class="event-card">
          <div class="event-header">
            <div>
              <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
              <?php if ($event['event_type']): ?>
                <div class="event-type"><?php echo htmlspecialchars($event['event_type']); ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align: right; font-size: 12px; color: var(--muted);">
              <?php echo (new DateTime($event['created_at']))->format('M j, g:ia'); ?>
            </div>
          </div>

          <div class="event-meta">
            <div class="meta-item">
              <div class="meta-label">DATE</div>
              <?php
              $start = new DateTime($event['start_date']);
              echo $start->format('M j, Y');
              if ($event['start_time']) echo ' ' . date('g:ia', strtotime($event['start_time']));
              ?>
            </div>
            <div class="meta-item">
              <div class="meta-label">LOCATION</div>
              <?php echo htmlspecialchars($event['location'] ?? 'Not specified'); ?>
            </div>
          </div>

          <?php if ($event['description']): ?>
            <div class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 300)); ?><?php echo strlen($event['description']) > 300 ? '…' : ''; ?></div>
          <?php endif; ?>

          <div class="event-organizer">
            <strong><?php echo htmlspecialchars($event['first'] . ' ' . $event['last']); ?></strong> (<?php echo htmlspecialchars($event['email']); ?>)
            <?php if ($event['contact_email']): ?>
              | Contact: <?php echo htmlspecialchars($event['contact_email']); ?>
            <?php endif; ?>
            <?php if ($event['website']): ?>
              | <a href="<?php echo htmlspecialchars($event['website']); ?>" target="_blank" style="color: var(--purple-lt); text-decoration: none;">Ticketing</a>
            <?php endif; ?>
          </div>

          <div class="event-actions">
            <button class="btn btn-approve" onclick="approveEvent(<?php echo $event['id']; ?>)">✓ APPROVE</button>
            <button class="btn btn-reject" onclick="openRejectModal(<?php echo $event['id']; ?>)">✗ REJECT</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- APPROVED TAB -->
  <div id="approved" class="tab-content">
    <?php if (count($approved) === 0): ?>
      <div class="empty">No approved events yet.</div>
    <?php else: ?>
      <?php foreach ($approved as $event): ?>
        <div class="event-card" style="opacity: 0.8;">
          <div class="event-header">
            <div>
              <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
              <?php if ($event['event_type']): ?>
                <div class="event-type"><?php echo htmlspecialchars($event['event_type']); ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align: right; font-size: 12px; color: var(--muted);">
              Live
            </div>
          </div>

          <div class="event-meta">
            <div class="meta-item">
              <div class="meta-label">DATE</div>
              <?php
              $start = new DateTime($event['start_date']);
              echo $start->format('M j, Y');
              if ($event['start_time']) echo ' ' . date('g:ia', strtotime($event['start_time']));
              ?>
            </div>
            <div class="meta-item">
              <div class="meta-label">ORGANIZER</div>
              <?php echo htmlspecialchars($event['first'] . ' ' . $event['last']); ?>
            </div>
          </div>

          <div class="event-organizer">
            Location: <?php echo htmlspecialchars($event['location'] ?? 'Not specified'); ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- REJECTED TAB -->
  <div id="rejected" class="tab-content">
    <?php if (count($rejected) === 0): ?>
      <div class="empty">No rejected events yet.</div>
    <?php else: ?>
      <?php foreach ($rejected as $event): ?>
        <div class="event-card" style="opacity: 0.6; border-color: var(--error);">
          <div class="event-header">
            <div>
              <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
              <?php if ($event['event_type']): ?>
                <div class="event-type"><?php echo htmlspecialchars($event['event_type']); ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align: right; font-size: 12px; color: var(--error);">
              Rejected
            </div>
          </div>

          <div class="event-meta">
            <div class="meta-item">
              <div class="meta-label">DATE</div>
              <?php
              $start = new DateTime($event['start_date']);
              echo $start->format('M j, Y');
              if ($event['start_time']) echo ' ' . date('g:ia', strtotime($event['start_time']));
              ?>
            </div>
            <div class="meta-item">
              <div class="meta-label">ORGANIZER</div>
              <?php echo htmlspecialchars($event['first'] . ' ' . $event['last']); ?>
            </div>
          </div>

          <?php if ($event['rejection_reason']): ?>
            <div class="event-description" style="color: var(--error);">
              <strong>Reason:</strong> <?php echo htmlspecialchars($event['rejection_reason']); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- REJECT MODAL -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <div class="modal-title">Reject Event</div>
    <form method="post">
      <input type="hidden" id="eventId" name="event_id" value="">
      <input type="hidden" name="status" value="rejected">
      <div class="form-group">
        <label>REASON (optional)</label>
        <textarea name="rejection_reason" placeholder="Why are you rejecting this event?" rows="4"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="cancel" onclick="closeRejectModal()">Cancel</button>
        <button type="submit" class="confirm">Reject</button>
      </div>
    </form>
  </div>
</div>

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  document.getElementById(tab).classList.add('active');
  event.target.closest('.tab-btn').classList.add('active');
}

function approveEvent(eventId) {
  if (confirm('Approve this event?')) {
    const form = document.createElement('form');
    form.method = 'post';
    form.innerHTML = '<input type="hidden" name="status" value="approved"><input type="hidden" name="event_id" value="' + eventId + '">';
    document.body.appendChild(form);
    form.submit();
  }
}

function openRejectModal(eventId) {
  document.getElementById('eventId').value = eventId;
  document.getElementById('rejectModal').classList.add('active');
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.remove('active');
}

window.addEventListener('click', function(e) {
  if (e.target === document.getElementById('rejectModal')) {
    closeRejectModal();
  }
});
</script>

</body>
</html>
