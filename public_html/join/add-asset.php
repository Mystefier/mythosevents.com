<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$statusType = '';
$statusMessage = '';

$assetTypes = [
    'Venue' => '🏛️',
    'Show' => '🎭',
    'Equipment' => '🎚️',
    'Costumes/Props' => '👗',
    'Business' => '🏢',
    'Game/Activity' => '🎲',
    'Workshop' => '🛠️',
    'Instrument' => '🎻',
    'Set Piece/Structure' => '🏗️',
    'Vehicle' => '🚚',
    'Art/Decor' => '🎨',
    'Other' => '✨',
];

$presetType = isset($_GET['type']) && isset($assetTypes[$_GET['type']]) ? $_GET['type'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = isset($_POST["type"]) && isset($assetTypes[$_POST["type"]]) ? $_POST["type"] : null;
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $description = isset($_POST["description"]) ? $_POST["description"] : '';

    if ($type && $name) {
        $insertStmt = mysqli_prepare($conn, "INSERT INTO assets (owner_person_id, type, name, description) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($insertStmt, "isss", $personId, $type, $name, $description);

        if (mysqli_stmt_execute($insertStmt)) {
            $assetId = mysqli_insert_id($conn);
            mysqli_stmt_close($insertStmt);

            if ($type === 'Venue') {
                $address = isset($_POST["venueAddress"]) ? $_POST["venueAddress"] : '';
                $latitude = isset($_POST["venueLatitude"]) && $_POST["venueLatitude"] !== '' ? floatval($_POST["venueLatitude"]) : null;
                $longitude = isset($_POST["venueLongitude"]) && $_POST["venueLongitude"] !== '' ? floatval($_POST["venueLongitude"]) : null;
                $capacity = isset($_POST["venueCapacity"]) && $_POST["venueCapacity"] !== '' ? intval($_POST["venueCapacity"]) : null;
                $indoorOutdoor = isset($_POST["venueIndoorOutdoor"]) ? $_POST["venueIndoorOutdoor"] : '';
                $venueType = isset($_POST["venueType"]) ? $_POST["venueType"] : '';
                $hasStage = isset($_POST["venueHasStage"]) ? 1 : 0;
                $parkingAvailable = isset($_POST["venueParking"]) ? 1 : 0;
                $typicalHours = isset($_POST["venueTypicalHours"]) ? trim($_POST["venueTypicalHours"]) : '';

                $venueStmt = mysqli_prepare($conn, "INSERT INTO asset_venues (asset_id, address, latitude, longitude, capacity, indoor_outdoor, venue_type, has_stage, parking_available, typical_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($venueStmt, "isddissiis", $assetId, $address, $latitude, $longitude, $capacity, $indoorOutdoor, $venueType, $hasStage, $parkingAvailable, $typicalHours);
                mysqli_stmt_execute($venueStmt);
                mysqli_stmt_close($venueStmt);
            }

            $statusType = 'success';
            $statusMessage = "Added! \"$name\" is now on your profile.";
        } else {
            mysqli_stmt_close($insertStmt);
            $statusType = 'error';
            $statusMessage = 'Something went wrong. Please try again.';
        }
    } else {
        $statusType = 'error';
        $statusMessage = 'Please pick a type and enter a name.';
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Asset — Mythos Events</title>
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
    --red:        #E05555;
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
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.15em; color: var(--purple-lt); margin-bottom: 8px; }
  .label-optional { font-family: 'Inter', sans-serif; font-size: 10px; color: var(--muted); letter-spacing: 0; margin-left: 6px; font-style: italic; }
  input[type="text"], input[type="number"], select, textarea {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 13px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s; resize: vertical;
    -webkit-appearance: none;
  }
  select { color-scheme: dark; }
  input:focus, textarea:focus, select:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { min-height: 100px; }

  .type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .type-item {
    display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03);
    border: 1px solid var(--purple-dim); border-radius: 8px; padding: 12px 16px;
    cursor: pointer; transition: background 0.15s, border-color 0.15s;
  }
  .type-item:hover { background: rgba(107,63,160,0.1); border-color: var(--purple); }
  .type-item input[type="radio"] { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--purple); cursor: pointer; padding: 0; margin: 0; }
  .type-item span { font-size: 14px; color: var(--lilac); line-height: 1.3; }

  #venueFields { display: none; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--purple-dim); }

  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 32px;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
  .status-message { margin-top: 24px; padding: 16px; border-radius: 8px; font-size: 15px; text-align: center; }
  .status-message.success { background: rgba(82,200,122,0.15); border: 1px solid rgba(82,200,122,0.4); color: var(--green); }
  .status-message.error { background: rgba(224,85,85,0.15); border: 1px solid rgba(224,85,85,0.4); color: var(--red); }

  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }

  @media (max-width: 600px) {
    nav { padding: 0 20px; }
    .form-card { padding: 28px 20px; }
    .type-grid, .field-row { grid-template-columns: 1fr; }
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
    <h1><?php echo $presetType ? 'Add Your ' . htmlspecialchars($presetType) : 'Add an Asset'; ?></h1>
  </div>

  <div class="form-card">
    <?php if ($statusType === 'success'): ?>
      <div class="status-message success"><?php echo htmlspecialchars($statusMessage); ?></div>
      <div style="text-align:center;margin-top:20px;display:flex;gap:12px;justify-content:center;">
        <a href="add-asset.php" style="color:var(--purple-lt);text-decoration:none;">Add Another</a>
        <a href="dashboard.php" style="color:var(--muted);text-decoration:none;">Back to Dashboard</a>
      </div>
    <?php else: ?>
      <?php if ($statusType === 'error'): ?>
        <div class="status-message error" style="margin-bottom:24px;"><?php echo htmlspecialchars($statusMessage); ?></div>
      <?php endif; ?>
      <form action="add-asset.php" method="post" onsubmit="return validateAssetForm()">

        <div class="form-section">
          <?php if ($presetType): ?>
            <div class="section-label">Adding a <?php echo htmlspecialchars($presetType); ?></div>
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($presetType); ?>">
            <p style="font-size:13px;color:var(--muted);">Not what you meant? <a href="add-asset.php" style="color:var(--purple-lt);">Choose a different asset type</a>.</p>
          <?php else: ?>
            <div class="section-label">What Kind of Asset?</div>
            <div class="type-grid">
              <?php foreach ($assetTypes as $t => $icon): ?>
                <label class="type-item">
                  <input type="radio" name="type" value="<?php echo htmlspecialchars($t); ?>" required>
                  <span><?php echo $icon; ?> <?php echo htmlspecialchars($t); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-section">
          <div class="section-label">Details</div>
          <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="e.g. 'The Grand Hall' or 'Fog Machine'" required>
          </div>
          <div class="field">
            <label for="description">Description <span class="label-optional">optional</span></label>
            <textarea id="description" name="description" placeholder="Any details worth knowing about it"></textarea>
          </div>

          <div id="venueFields" <?php echo $presetType === 'Venue' ? 'style="display:block;"' : ''; ?>>
            <div class="field">
              <label for="venueAddress">Venue Address</label>
              <input type="text" id="venueAddress" name="venueAddress" placeholder="Street, City, State">
              <div id="geocodeStatus" style="font-size:13px;margin-top:8px;min-height:18px;"></div>
            </div>
            <div class="field">
              <label for="venueType">Venue Type <span class="label-optional">optional</span></label>
              <select id="venueType" name="venueType">
                <option value="">Select...</option>
                <option value="Bar/Restaurant">Bar / Restaurant</option>
                <option value="Event Space">Event Space</option>
                <option value="Community Venue">Community Venue</option>
                <option value="Outdoor/Park">Outdoor / Park</option>
                <option value="Retail/Shop">Retail / Shop</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="field-row">
              <div class="field">
                <label for="venueCapacity">Capacity <span class="label-optional">optional</span></label>
                <input type="number" id="venueCapacity" name="venueCapacity" placeholder="e.g. 150" min="0">
              </div>
              <div class="field">
                <label for="venueIndoorOutdoor">Indoor / Outdoor <span class="label-optional">optional</span></label>
                <select id="venueIndoorOutdoor" name="venueIndoorOutdoor">
                  <option value="">Select...</option>
                  <option value="Indoor">Indoor</option>
                  <option value="Outdoor">Outdoor</option>
                  <option value="Both">Both</option>
                </select>
              </div>
            </div>
            <div class="field">
              <label for="venueTypicalHours">Typical Days / Hours <span class="label-optional">optional</span></label>
              <input type="text" id="venueTypicalHours" name="venueTypicalHours" placeholder="e.g. Fri–Sat, 6pm–2am">
            </div>
            <div class="field-row">
              <label class="type-item" style="margin: 0;">
                <input type="checkbox" id="venueHasStage" name="venueHasStage" style="width:18px;height:18px;flex-shrink:0;accent-color:var(--purple);cursor:pointer;padding:0;margin:0;">
                <span>Has a stage / performance area</span>
              </label>
              <label class="type-item" style="margin: 0;">
                <input type="checkbox" id="venueParking" name="venueParking" style="width:18px;height:18px;flex-shrink:0;accent-color:var(--purple);cursor:pointer;padding:0;margin:0;">
                <span>Parking available</span>
              </label>
            </div>
            <input type="hidden" id="venueLatitude" name="venueLatitude" value="">
            <input type="hidden" id="venueLongitude" name="venueLongitude" value="">
          </div>
        </div>

        <button type="submit" class="submit-btn">Add Asset ✦</button>
      </form>
    <?php endif; ?>
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

  // Show/hide venue-specific fields based on selected type
  const typeRadios = document.querySelectorAll('input[name="type"]');
  const venueFields = document.getElementById('venueFields');
  const venueAddressInput = document.getElementById('venueAddress');
  typeRadios.forEach(r => {
    r.addEventListener('change', () => {
      const isVenue = r.value === 'Venue' && r.checked;
      if (r.checked) {
        venueFields.style.display = r.value === 'Venue' ? 'block' : 'none';
      }
    });
  });

  // Geocode venue address
  const geocoder = new google.maps.Geocoder();
  const statusDiv = document.getElementById('geocodeStatus');
  const latInput = document.getElementById('venueLatitude');
  const lngInput = document.getElementById('venueLongitude');

  venueAddressInput.addEventListener('blur', () => {
    const address = venueAddressInput.value.trim();
    if (!address) { statusDiv.textContent = ''; latInput.value = ''; lngInput.value = ''; return; }
    geocoder.geocode({ address: address }, (results, status) => {
      if (status === 'OK') {
        const location = results[0].geometry.location;
        latInput.value = location.lat();
        lngInput.value = location.lng();
        statusDiv.innerHTML = `<span style="color:var(--green);">✓ Found: ${results[0].formatted_address}</span>`;
      } else {
        statusDiv.innerHTML = `<span style="color:#E05555;">✗ Couldn't find that address.</span>`;
      }
    });
  });

  function validateAssetForm() {
    const selectedType = document.querySelector('input[name="type"]:checked, input[type="hidden"][name="type"]');
    if (selectedType && selectedType.value === 'Venue') {
      if (!document.getElementById('venueLatitude').value) {
        alert('Please enter and confirm a venue address.');
        return false;
      }
    }
    return true;
  }
</script>
</body>
</html>
