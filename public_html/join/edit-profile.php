<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$stmt = mysqli_prepare($conn, "SELECT * FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$person = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Load group memberships from the dedicated table
$grpStmt = mysqli_prepare($conn, "SELECT g.name, g.icon, g.join_url FROM group_memberships gm JOIN `groups` g ON gm.group_id = g.id WHERE gm.person_id = ?");
mysqli_stmt_bind_param($grpStmt, "i", $personId);
mysqli_stmt_execute($grpStmt);
$grpResult = mysqli_stmt_get_result($grpStmt);
$userGroups = [];
while ($row = mysqli_fetch_assoc($grpResult)) { $userGroups[] = $row; }
mysqli_stmt_close($grpStmt);

mysqli_close($conn);

$selectedRoles = $person['roles'] ? array_map('trim', explode(',', $person['roles'])) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Your Profile — Mythos Events</title>
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
    --green:      #52C87A;
  }
  html { scroll-behavior: smooth; }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.7;
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
  .nav-back { font-size: 13px; color: var(--muted); text-decoration: none; letter-spacing: 0.08em; }
  .nav-back:hover { color: var(--white); }
  main { flex: 1; position: relative; z-index: 1; max-width: 640px; margin: 0 auto; padding: 60px 20px 80px; width: 100%; }
  .page-header { text-align: center; margin-bottom: 36px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  .page-header h1 { font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 38px); color: var(--white); text-shadow: 0 0 40px rgba(107,63,160,0.7); }
  .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .form-section { margin-bottom: 32px; }
  .form-section:last-of-type { margin-bottom: 0; }
  .section-label {
    font-family: 'Cinzel Decorative', serif; font-size: 9px; letter-spacing: 0.4em; color: var(--purple-lt);
    text-transform: uppercase; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--purple-dim);
  }
  .field { margin-bottom: 20px; }
  .field:last-child { margin-bottom: 0; }
  .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.15em; color: var(--purple-lt); margin-bottom: 8px; }
  .label-optional { font-family: 'Inter', sans-serif; font-size: 10px; color: var(--muted); letter-spacing: 0; margin-left: 6px; font-style: italic; }
  input[type="text"], input[type="tel"], input[type="date"], input[type="url"], textarea {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 13px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s; resize: vertical;
    -webkit-appearance: none;
  }
  input[type="date"] { color-scheme: dark; }
  input:focus, textarea:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder, textarea::placeholder { color: var(--muted); }

  .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .checkbox-item {
    display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03);
    border: 1px solid var(--purple-dim); border-radius: 8px; padding: 12px 16px;
    cursor: pointer; transition: background 0.15s, border-color 0.15s;
  }
  .checkbox-item:hover { background: rgba(107,63,160,0.1); border-color: var(--purple); }
  .checkbox-item input[type="checkbox"] { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--purple); cursor: pointer; padding: 0; margin: 0; }
  .checkbox-item span { font-size: 14px; color: var(--lilac); line-height: 1.3; }

  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 32px;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .cancel-link { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: var(--muted); text-decoration: none; }
  .cancel-link:hover { color: var(--white); }

  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 28px 20px; }
    .row-2, .checkbox-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>
</nav>

<main>
  <div class="page-header">
    <div class="eyebrow">Your Profile</div>
    <h1>Edit Your Info</h1>
  </div>

  <div class="form-card">
    <form action="Update.php" method="post">

      <div class="form-section">
        <div class="section-label">About You</div>
        <div class="row-2">
          <div class="field">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($person['first']); ?>" required>
          </div>
          <div class="field">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($person['last']); ?>" required>
          </div>
        </div>
        <div class="field">
          <label for="phoneNumber">Phone</label>
          <input type="tel" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($person['phone']); ?>">
        </div>
        <div class="field">
          <label for="dob">Date of Birth <span class="label-optional">optional</span></label>
          <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($person['dob']); ?>">
          <p style="font-size:12px;color:var(--muted);margin-top:6px;">Helps us verify age requirements for venues and stay compliant with minor-related regulations.</p>
        </div>
      </div>

      <div class="form-section">
        <div class="section-label">How Are You Involved?</div>
        <div class="checkbox-grid">
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Vendor" <?php echo in_array('Vendor', $selectedRoles) ? 'checked' : ''; ?>><span>🛖 Vendor</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Organizer" <?php echo in_array('Organizer', $selectedRoles) ? 'checked' : ''; ?>><span>📋 Organizer</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="volunteer" <?php echo in_array('volunteer', $selectedRoles) ? 'checked' : ''; ?>><span>🌟 General Help / Setup / Takedown</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Sales" <?php echo in_array('Sales', $selectedRoles) ? 'checked' : ''; ?>><span>🎟️ Sales</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Performer" <?php echo in_array('Performer', $selectedRoles) ? 'checked' : ''; ?>><span>🎭 Performer</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Artist" <?php echo in_array('Artist', $selectedRoles) ? 'checked' : ''; ?>><span>🎨 Artist</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Operations" <?php echo in_array('Operations', $selectedRoles) ? 'checked' : ''; ?>><span>⚙️ Operations</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Venue Manager/Owner" <?php echo in_array('Venue Manager/Owner', $selectedRoles) ? 'checked' : ''; ?>><span>🏛️ Venue Manager/Owner</span></label>
          <label class="checkbox-item"><input type="checkbox" name="roles[]" value="Other" <?php echo in_array('Other', $selectedRoles) ? 'checked' : ''; ?>><span>✨ Something Else</span></label>
        </div>
      </div>

      <?php if ($userGroups): ?>
      <div class="form-section">
        <div class="section-label">Your Groups</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach ($userGroups as $grp): ?>
          <div style="display:flex;align-items:center;gap:12px;background:var(--gold-dim);border:1px solid rgba(232,197,71,0.3);border-radius:10px;padding:14px 18px;">
            <span style="font-size:22px;"><?php echo htmlspecialchars($grp['icon']); ?></span>
            <span style="font-size:15px;color:var(--white);font-weight:600;"><?php echo htmlspecialchars($grp['name']); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-top:12px;">Group memberships are managed separately. Visit a group's page to join or leave.</p>
      </div>
      <?php endif; ?>

      <div class="form-section">
        <div class="section-label">Where Are You Available to Work?</div>
        <div class="field">
          <label for="serviceAreaAddress">Your Location or Work Area</label>
          <input type="text" id="serviceAreaAddress" name="serviceAreaAddress" value="<?php echo htmlspecialchars($person['service_area_address']); ?>" placeholder="e.g., 'Phoenix, AZ'">
          <div id="geocodeStatus" style="font-size:13px;margin-top:8px;min-height:18px;"></div>
        </div>
        <div class="field">
          <label for="serviceAreaRadius">Willing to Travel Up To: <span id="radiusDisplay"><?php echo $person['service_area_radius_miles'] ? intval($person['service_area_radius_miles']) : 30; ?></span> miles</label>
          <input type="range" id="serviceAreaRadius" name="serviceAreaRadius" min="1" max="500" value="<?php echo $person['service_area_radius_miles'] ? intval($person['service_area_radius_miles']) : 30; ?>" style="width:100%;cursor:pointer;height:6px;">
        </div>
        <input type="hidden" id="serviceAreaLatitude" name="serviceAreaLatitude" value="<?php echo htmlspecialchars($person['service_area_latitude']); ?>">
        <input type="hidden" id="serviceAreaLongitude" name="serviceAreaLongitude" value="<?php echo htmlspecialchars($person['service_area_longitude']); ?>">
      </div>

      <div class="form-section">
        <div class="section-label">Tell Us More</div>
        <div class="field">
          <label for="message">Why Do You Want to Join?</label>
          <textarea id="message" name="message" rows="4"><?php echo htmlspecialchars($person['message']); ?></textarea>
        </div>
        <div class="field">
          <label for="description">Introduce Yourself in Third Person</label>
          <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($person['description']); ?></textarea>
        </div>
        <div class="field">
          <label for="website">Your Website or Social Link</label>
          <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($person['website']); ?>">
        </div>
      </div>

      <button type="submit" class="submit-btn">Save Changes ✦</button>
      <a href="dashboard.php" class="cancel-link">Cancel</a>
    </form>
  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
</footer>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAv7ZlQy5ZaooKBRw64ZsbQN6W6rgoshPo&libraries=places"></script>
<script>
  const container = document.getElementById('stars');
  for (let i = 0; i < 120; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }

  const geocoder = new google.maps.Geocoder();
  const addressInput = document.getElementById('serviceAreaAddress');
  const statusDiv = document.getElementById('geocodeStatus');
  const latInput = document.getElementById('serviceAreaLatitude');
  const lngInput = document.getElementById('serviceAreaLongitude');
  const radiusSlider = document.getElementById('serviceAreaRadius');
  const radiusDisplay = document.getElementById('radiusDisplay');

  radiusSlider.addEventListener('input', () => { radiusDisplay.textContent = radiusSlider.value; });
  addressInput.addEventListener('blur', geocodeAddress);

  function geocodeAddress() {
    const address = addressInput.value.trim();
    if (!address) { statusDiv.textContent = ''; latInput.value = ''; lngInput.value = ''; return; }
    geocoder.geocode({ address: address }, (results, status) => {
      if (status === 'OK') {
        const location = results[0].geometry.location;
        latInput.value = location.lat();
        lngInput.value = location.lng();
        statusDiv.innerHTML = `<span style="color:var(--green);">✓ Found: ${results[0].formatted_address}</span>`;
      } else {
        statusDiv.innerHTML = `<span style="color:#E05555;">✗ Couldn't find that address. Try being more specific.</span>`;
      }
    });
  }
</script>
</body>
</html>
