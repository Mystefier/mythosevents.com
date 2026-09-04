<?php
include(__DIR__ . '/../join/logintodatabase.php');

// Get approved events, sorted by start_date
$eventsStmt = $conn->prepare("
    SELECT e.id, e.title, e.description, e.event_type, e.start_date, e.start_time, e.end_date, e.end_time,
           e.location, e.website, e.contact_email, p.first, p.last
    FROM events e
    JOIN people p ON e.organizer_id = p.id
    WHERE e.status = 'approved'
    ORDER BY e.start_date ASC
");
$eventsStmt->execute();
$eventsResult = $eventsStmt->get_result();
$events = $eventsResult->fetch_all(MYSQLI_ASSOC);
$eventsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upcoming Events — Mythos Events</title>
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

  .hero {
    text-align: center; padding: 70px 20px 50px; max-width: 760px; margin: 0 auto;
  }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px;
  }
  .hero h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(30px, 5vw, 50px); color: var(--white);
    line-height: 1.15; margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .hero p { font-size: 16px; color: var(--muted); max-width: 600px; margin: 0 auto; }

  .events-section { padding: 40px 20px 70px; }
  .events-wrap { max-width: 960px; margin: 0 auto; }

  .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

  .event-card {
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; padding: 24px; transition: transform 0.2s, border-color 0.2s;
  }
  .event-card:hover { transform: translateY(-4px); border-color: var(--purple-lt); }

  .event-type { font-family: 'Cinzel Decorative', serif; font-size: 9px; letter-spacing: 0.3em; color: var(--gold); margin-bottom: 8px; }
  .event-title { font-family: 'Cinzel', serif; font-size: 18px; font-weight: 700; color: var(--white); margin-bottom: 12px; }
  .event-date { font-size: 14px; color: var(--purple-lt); margin-bottom: 8px; }
  .event-location { font-size: 14px; color: var(--muted); margin-bottom: 12px; }
  .event-description { font-size: 15px; color: var(--muted); line-height: 1.6; margin-bottom: 16px; }
  .event-organizer { font-size: 13px; color: var(--muted); font-style: italic; margin-bottom: 16px; }

  .event-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .event-link {
    display: inline-block; font-size: 13px; font-family: 'Cinzel', serif;
    padding: 8px 14px; border-radius: 6px; text-decoration: none;
    transition: background 0.2s, transform 0.15s;
  }
  .event-link-primary {
    background: var(--purple); color: var(--white); letter-spacing: 0.1em;
  }
  .event-link-primary:hover { background: var(--purple-lt); transform: translateY(-1px); }
  .event-link-secondary {
    background: rgba(107,63,160,0.3); color: var(--lilac); letter-spacing: 0.05em;
  }
  .event-link-secondary:hover { background: rgba(107,63,160,0.5); }

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
  <a href="/organizers/" class="nav-back">← Organize</a>
</nav>

<main>
  <div class="hero">
    <div class="eyebrow">UPCOMING ADVENTURES</div>
    <h1>Events</h1>
    <p>Discover immersive experiences from Mythos Events organizers in your network.</p>
  </div>

  <div class="events-section">
    <div class="events-wrap">
      <?php if (count($events) > 0): ?>
        <div class="events-grid">
          <?php foreach ($events as $event): ?>
            <?php
            $start_date = new DateTime($event['start_date']);
            $date_display = $start_date->format('M j');
            if ($event['end_date']) {
              $end_date = new DateTime($event['end_date']);
              if ($event['start_date'] !== $event['end_date']) {
                $date_display .= ' – ' . $end_date->format('M j');
              }
            }
            if ($event['start_time']) {
              $date_display .= ' ' . date('g:ia', strtotime($event['start_time']));
            }
            ?>
            <div class="event-card">
              <?php if ($event['event_type']): ?>
                <div class="event-type"><?php echo htmlspecialchars($event['event_type']); ?></div>
              <?php endif; ?>
              <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
              <div class="event-date">✦ <?php echo htmlspecialchars($date_display); ?></div>
              <?php if ($event['location']): ?>
                <div class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></div>
              <?php endif; ?>
              <?php if ($event['description']): ?>
                <div class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?><?php echo strlen($event['description']) > 150 ? '…' : ''; ?></div>
              <?php endif; ?>
              <div class="event-organizer">By <?php echo htmlspecialchars($event['first'] . ' ' . $event['last']); ?></div>
              <div class="event-links">
                <?php if ($event['website']): ?>
                  <a href="<?php echo htmlspecialchars($event['website']); ?>" target="_blank" class="event-link event-link-primary">TICKETS</a>
                <?php endif; ?>
                <?php if ($event['contact_email']): ?>
                  <a href="mailto:<?php echo htmlspecialchars($event['contact_email']); ?>" class="event-link event-link-secondary">CONTACT</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty">
          <p>No events scheduled yet.</p>
          <p><a href="/organizers/">Become an organizer →</a></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
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
