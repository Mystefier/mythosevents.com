<?php
include(__DIR__ . '/../join/logintodatabase.php');

$occurrence_id = isset($_GET['occurrence_id']) ? (int)$_GET['occurrence_id'] : 0;

$occurrence = null;
if ($occurrence_id) {
    $stmt = $conn->prepare("
        SELECT o.id AS occurrence_id, o.start_date, o.start_time, o.end_date, o.end_time,
               o.location, o.directions, o.website, o.peatix_url,
               e.title, e.description, e.event_type, e.cover_image_url, e.contact_email,
               p.first, p.last
        FROM event_occurrences o
        JOIN events e ON o.event_id = e.id
        JOIN people p ON e.organizer_id = p.id
        WHERE o.id = ? AND e.status = 'approved' AND o.status = 'scheduled'
    ");
    $stmt->bind_param("i", $occurrence_id);
    $stmt->execute();
    $occurrence = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$occurrence) {
    http_response_code(404);
}

if ($occurrence) {
    $dateObj = new DateTime($occurrence['start_date']);
    $dateDisplay = $dateObj->format('l, F j, Y');
    $timeDisplay = $occurrence['start_time'] ? date('g:ia', strtotime($occurrence['start_time'])) : '';
    if ($occurrence['end_date'] && $occurrence['end_date'] !== $occurrence['start_date']) {
        $endObj = new DateTime($occurrence['end_date']);
        $dateDisplay .= ' – ' . $endObj->format('F j, Y');
    }

    $qrTarget = $occurrence['peatix_url'] ?: ($occurrence['website'] ?: 'https://mythosevents.com/events/');
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=6&data=' . urlencode($qrTarget);
    $ticketLabel = $occurrence['peatix_url'] ? 'Scan for Tickets (Peatix)' : ($occurrence['website'] ? 'Scan for Tickets' : 'Scan for Event Info');
    $organizerName = trim($occurrence['first'] . ' ' . $occurrence['last']);
    $teaser = $occurrence['description'] ? substr($occurrence['description'], 0, 120) . (strlen($occurrence['description']) > 120 ? '…' : '') : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $occurrence ? htmlspecialchars($occurrence['title']) . ' — Printable Materials' : 'Not Found'; ?> — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style id="pageSizeStyle">
  @page { size: letter; margin: 0.4in; }
</style>
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
    --ink:        #231B33;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.6;
    min-height: 100vh;
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

  .toolbar {
    position: relative; z-index: 5; display: flex; gap: 12px; align-items: center; justify-content: center;
    flex-wrap: wrap; padding: 24px 20px; background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--purple-dim);
  }
  .mode-btn {
    background: var(--card); border: 1px solid var(--purple-dim); color: var(--muted);
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em;
    padding: 10px 20px; border-radius: 8px; cursor: pointer; transition: all 0.2s;
  }
  .mode-btn.active { background: var(--purple); color: var(--white); border-color: var(--purple); }
  .print-btn {
    background: var(--gold); color: var(--midnight); border: none;
    font-family: 'Cinzel', serif; font-size: 13px; letter-spacing: 0.1em; font-weight: 700;
    padding: 10px 24px; border-radius: 8px; cursor: pointer;
  }
  .print-hint { width: 100%; text-align: center; font-size: 12px; color: var(--muted); margin-top: 4px; }

  .preview-wrap { position: relative; z-index: 1; padding: 40px 20px 80px; display: flex; justify-content: center; }
  .sheet {
    background: #fff; color: var(--ink); box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    width: 8.1in;
  }
  body[data-mode="poster"] .sheet { width: 10.4in; }
  body[data-mode="brochure"] .sheet { width: 10.1in; }

  .not-found { text-align: center; padding: 100px 20px; position: relative; z-index: 1; }
  .not-found h1 { font-family: 'Cinzel', serif; color: var(--white); font-size: 28px; margin-bottom: 16px; }
  .not-found a { color: var(--purple-lt); text-decoration: none; }

  /* Shared content styling */
  .eyebrow-print { font-family: 'Cinzel Decorative', serif; font-size: 11px; letter-spacing: 0.35em; color: var(--purple); margin-bottom: 10px; }
  .title-print { font-family: 'Cinzel', serif; font-weight: 900; color: var(--ink); line-height: 1.1; }
  .cover-img { width: 100%; object-fit: cover; display: block; }
  .qr-box { background: #fff; border: 1px solid #ddd; padding: 6px; display: inline-block; }
  .qr-box img { display: block; }

  /* FLYER */
  .flyer-content { display: none; padding: 0.5in 0.55in; min-height: 10.2in; }
  body[data-mode="flyer"] .flyer-content { display: block; }
  .flyer-content .cover-img { height: 3in; border-radius: 6px; margin-bottom: 24px; }
  .flyer-content .title-print { font-size: 34px; margin-bottom: 14px; }
  .flyer-content .meta-line { font-size: 17px; color: var(--purple); font-weight: 600; margin-bottom: 6px; }
  .flyer-content .meta-line.location { color: var(--ink); }
  .flyer-content .directions { font-size: 13px; color: #555; font-style: italic; margin: 14px 0 20px; line-height: 1.6; }
  .flyer-content .description { font-size: 15px; color: #333; line-height: 1.7; margin-bottom: 28px; }
  .flyer-content .bottom-row { display: flex; align-items: center; justify-content: space-between; border-top: 2px solid var(--ink); padding-top: 20px; margin-top: auto; }
  .flyer-content .ticket-text { font-family: 'Cinzel', serif; font-size: 14px; color: var(--ink); max-width: 4in; }
  .flyer-content .organizer-line { font-size: 12px; color: #777; margin-top: 16px; }

  /* POSTER */
  .poster-content { display: none; padding: 0.5in; text-align: center; }
  body[data-mode="poster"] .poster-content { display: block; }
  .poster-content .cover-img { height: 5.5in; border-radius: 10px; margin-bottom: 36px; }
  .poster-content .title-print { font-size: 64px; margin-bottom: 20px; }
  .poster-content .meta-line { font-size: 28px; color: var(--purple); font-weight: 700; margin-bottom: 10px; }
  .poster-content .meta-line.location { color: var(--ink); }
  .poster-content .teaser { font-size: 18px; color: #444; max-width: 7in; margin: 20px auto 40px; }
  .poster-content .qr-box { transform: scale(1.3); margin-top: 20px; }
  .poster-content .ticket-text { font-family: 'Cinzel', serif; font-size: 18px; margin-top: 16px; }

  /* BROCHURE (trifold, landscape) */
  .brochure-content { display: none; }
  body[data-mode="brochure"] .brochure-content { display: flex; min-height: 7.7in; }
  .panel { flex: 1; padding: 0.45in 0.4in; display: flex; flex-direction: column; }
  .panel + .panel { border-left: 1px dashed #bbb; }
  .panel-back { background: #f7f5fa; justify-content: space-between; }
  .panel-inside { justify-content: flex-start; }
  .panel-front { justify-content: flex-end; text-align: center; background: linear-gradient(160deg, #f1ecfa, #ffffff); }
  .panel-front .cover-img { height: 2.6in; border-radius: 6px; margin-bottom: 20px; }
  .panel-front .title-print { font-size: 26px; margin-bottom: 10px; }
  .panel-front .meta-line { font-size: 14px; color: var(--purple); font-weight: 600; }
  .panel-inside .section-title { font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.15em; color: var(--purple); margin: 18px 0 6px; text-transform: uppercase; }
  .panel-inside .section-title:first-child { margin-top: 0; }
  .panel-inside p { font-size: 13px; color: #333; line-height: 1.6; }
  .panel-back .brand { font-family: 'Cinzel', serif; font-size: 14px; color: var(--ink); }
  .panel-back .brand span { color: var(--purple); }
  .panel-back .qr-box { align-self: center; margin: 16px 0; }
  .panel-back .ticket-text { font-size: 12px; text-align: center; color: #444; }
  .panel-back .organizer-line { font-size: 11px; color: #777; text-align: center; }
  .fold-note { text-align: center; font-size: 11px; color: var(--muted); padding: 10px; }

  @media print {
    body { background: #fff; }
    #stars, nav, .toolbar, .not-found, .fold-note { display: none !important; }
    .preview-wrap { padding: 0; }
    .sheet { box-shadow: none; width: auto; }
    .flyer-content, .poster-content { min-height: 0; }
    body[data-mode="brochure"] .brochure-content { min-height: 0; }
  }
</style>
</head>
<body data-mode="flyer">

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/events/" class="nav-back">← Back to Events</a>
</nav>

<?php if (!$occurrence): ?>

  <div class="not-found">
    <h1>Materials Not Available</h1>
    <p style="color:var(--muted);margin-bottom:20px;">This event isn't approved and scheduled, or the link is wrong.</p>
    <a href="/events/">View upcoming events →</a>
  </div>

<?php else: ?>

  <div class="toolbar">
    <button class="mode-btn active" data-mode="flyer" onclick="setMode('flyer')">FLYER</button>
    <button class="mode-btn" data-mode="poster" onclick="setMode('poster')">POSTER</button>
    <button class="mode-btn" data-mode="brochure" onclick="setMode('brochure')">TRIFOLD BROCHURE</button>
    <button class="print-btn" onclick="window.print()">🖨 PRINT / SAVE AS PDF</button>
    <div class="print-hint">Flyer and Poster print on one sheet (Poster suggests 11×17 if your printer supports it). Brochure prints on one landscape sheet — fold into three panels, cover facing out.</div>
  </div>

  <div class="preview-wrap">
    <div class="sheet">

      <!-- FLYER -->
      <div class="flyer-content">
        <?php if ($occurrence['cover_image_url']): ?>
          <img class="cover-img" src="<?php echo htmlspecialchars($occurrence['cover_image_url']); ?>" alt="">
        <?php endif; ?>
        <?php if ($occurrence['event_type']): ?><div class="eyebrow-print"><?php echo htmlspecialchars(strtoupper($occurrence['event_type'])); ?></div><?php endif; ?>
        <div class="title-print"><?php echo htmlspecialchars($occurrence['title']); ?></div>
        <div class="meta-line">✦ <?php echo htmlspecialchars($dateDisplay); ?><?php echo $timeDisplay ? ' · ' . $timeDisplay : ''; ?></div>
        <?php if ($occurrence['location']): ?><div class="meta-line location">📍 <?php echo htmlspecialchars($occurrence['location']); ?></div><?php endif; ?>
        <?php if ($occurrence['directions']): ?><div class="directions">🧭 <?php echo htmlspecialchars($occurrence['directions']); ?></div><?php endif; ?>
        <?php if ($occurrence['description']): ?><div class="description"><?php echo htmlspecialchars($occurrence['description']); ?></div><?php endif; ?>
        <div class="bottom-row">
          <div class="ticket-text">
            <?php echo htmlspecialchars($ticketLabel); ?><br>
            <?php if ($occurrence['contact_email']): ?><span style="font-size:11px;color:#777;">Contact: <?php echo htmlspecialchars($occurrence['contact_email']); ?></span><?php endif; ?>
          </div>
          <div class="qr-box"><img src="<?php echo htmlspecialchars($qrUrl); ?>" width="110" height="110" alt="QR code"></div>
        </div>
        <div class="organizer-line">Hosted by <?php echo htmlspecialchars($organizerName); ?> · Mythos Events Network</div>
      </div>

      <!-- POSTER -->
      <div class="poster-content">
        <?php if ($occurrence['cover_image_url']): ?>
          <img class="cover-img" src="<?php echo htmlspecialchars($occurrence['cover_image_url']); ?>" alt="">
        <?php endif; ?>
        <?php if ($occurrence['event_type']): ?><div class="eyebrow-print"><?php echo htmlspecialchars(strtoupper($occurrence['event_type'])); ?></div><?php endif; ?>
        <div class="title-print"><?php echo htmlspecialchars($occurrence['title']); ?></div>
        <div class="meta-line">✦ <?php echo htmlspecialchars($dateDisplay); ?><?php echo $timeDisplay ? ' · ' . $timeDisplay : ''; ?></div>
        <?php if ($occurrence['location']): ?><div class="meta-line location">📍 <?php echo htmlspecialchars($occurrence['location']); ?></div><?php endif; ?>
        <?php if ($teaser): ?><div class="teaser"><?php echo htmlspecialchars($teaser); ?></div><?php endif; ?>
        <div class="qr-box"><img src="<?php echo htmlspecialchars($qrUrl); ?>" width="180" height="180" alt="QR code"></div>
        <div class="ticket-text"><?php echo htmlspecialchars($ticketLabel); ?></div>
      </div>

      <!-- BROCHURE (trifold) -->
      <div class="brochure-content">
        <div class="panel panel-back">
          <div>
            <div class="brand">Mythos<span>✦</span>Events</div>
            <?php if ($occurrence['directions']): ?>
              <div class="section-title">How to Get There</div>
              <p><?php echo htmlspecialchars($occurrence['directions']); ?></p>
            <?php endif; ?>
          </div>
          <div>
            <div class="qr-box"><img src="<?php echo htmlspecialchars($qrUrl); ?>" width="120" height="120" alt="QR code"></div>
            <div class="ticket-text"><?php echo htmlspecialchars($ticketLabel); ?></div>
            <div class="organizer-line">Hosted by <?php echo htmlspecialchars($organizerName); ?><?php echo $occurrence['contact_email'] ? ' · ' . htmlspecialchars($occurrence['contact_email']) : ''; ?></div>
          </div>
        </div>
        <div class="panel panel-inside">
          <div class="section-title">About This Event</div>
          <p><?php echo htmlspecialchars($occurrence['description'] ?: 'Join us for this Mythos Events experience.'); ?></p>
          <div class="section-title">When</div>
          <p>✦ <?php echo htmlspecialchars($dateDisplay); ?><?php echo $timeDisplay ? ' · ' . $timeDisplay : ''; ?></p>
          <?php if ($occurrence['location']): ?>
            <div class="section-title">Where</div>
            <p>📍 <?php echo htmlspecialchars($occurrence['location']); ?></p>
          <?php endif; ?>
        </div>
        <div class="panel panel-front">
          <?php if ($occurrence['cover_image_url']): ?>
            <img class="cover-img" src="<?php echo htmlspecialchars($occurrence['cover_image_url']); ?>" alt="">
          <?php endif; ?>
          <?php if ($occurrence['event_type']): ?><div class="eyebrow-print"><?php echo htmlspecialchars(strtoupper($occurrence['event_type'])); ?></div><?php endif; ?>
          <div class="title-print"><?php echo htmlspecialchars($occurrence['title']); ?></div>
          <div class="meta-line"><?php echo htmlspecialchars($dateDisplay); ?></div>
        </div>
      </div>

    </div>
  </div>
  <div class="fold-note">Trifold: print landscape, fold the left panel in first, then the right (cover) panel over it.</div>

<?php endif; ?>

<script>
  const container = document.getElementById('stars');
  for (let i = 0; i < 60; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }

  const pageSizes = {
    flyer: '@page { size: letter; margin: 0.4in; }',
    poster: '@page { size: 11in 17in; margin: 0.4in; }',
    brochure: '@page { size: letter landscape; margin: 0.3in; }',
  };

  function setMode(mode) {
    document.body.setAttribute('data-mode', mode);
    document.getElementById('pageSizeStyle').textContent = pageSizes[mode];
    document.querySelectorAll('.mode-btn').forEach(function(btn) {
      btn.classList.toggle('active', btn.getAttribute('data-mode') === mode);
    });
  }
</script>

</body>
</html>
