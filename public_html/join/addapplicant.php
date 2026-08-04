<?php
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$email = isset($_GET["email"]) ? filter_var($_GET["email"], FILTER_SANITIZE_EMAIL) : '';
$recruiterId = isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : '1';

$errorState = '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorState = 'invalid';
} else {
    $checkSql = "SELECT * FROM people WHERE email = '$email'";
    $result = mysqli_query($conn, $checkSql);
    if (mysqli_num_rows($result) > 0) {
        $errorState = 'already';
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complete Your Profile — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --deep:       #1A1628;
    --card:       #201C32;
    --purple:     #6B3FA0;
    --purple-lt:  #9B6FD0;
    --purple-dim: rgba(107,63,160,0.25);
    --gold:       #E8C547;
    --gold-dim:   rgba(232,197,71,0.15);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
    --green:      #52C87A;
    --red:        #E05555;
  }
  html { scroll-behavior: smooth; }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column;
    overflow-x: hidden;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star {
    position: absolute; border-radius: 50%; background: #fff;
    animation: twinkle var(--dur) ease-in-out infinite var(--delay);
  }
  @keyframes twinkle {
    0%,100% { opacity: 0.1; transform: scale(1); }
    50%      { opacity: 0.9; transform: scale(1.5); }
  }
  nav {
    position: relative; z-index: 10; padding: 0 40px; height: 68px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(13,11,26,0.95); border-bottom: 1px solid var(--purple-dim);
  }
  .nav-logo {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: 20px;
    color: var(--white); letter-spacing: 0.05em; text-decoration: none;
  }
  .nav-logo span { color: var(--gold); }
  .nav-back {
    font-size: 13px; color: var(--muted); text-decoration: none;
    letter-spacing: 0.08em; transition: color 0.2s;
  }
  .nav-back:hover { color: var(--white); }

  main {
    flex: 1; padding: 60px 20px 80px;
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
  }

  /* ── PAGE HEADER ── */
  .page-header { text-align: center; max-width: 560px; margin-bottom: 48px; }
  .eyebrow {
    font-family: 'Cinzel Decorative', serif; font-size: 10px;
    letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px;
  }
  .page-header h1 {
    font-family: 'Cinzel', serif; font-weight: 900;
    font-size: clamp(30px, 4vw, 46px); color: var(--white);
    line-height: 1.15; margin-bottom: 14px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .page-header p { font-size: 15px; color: var(--muted); }

  /* ── FORM CARD ── */
  .form-card {
    width: 100%; max-width: 620px;
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 16px; padding: 52px 52px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }

  /* ── SECTIONS ── */
  .form-section { margin-bottom: 40px; }
  .form-section:last-of-type { margin-bottom: 0; }
  .section-label {
    font-family: 'Cinzel Decorative', serif; font-size: 9px;
    letter-spacing: 0.4em; color: var(--purple-lt);
    text-transform: uppercase; margin-bottom: 20px;
    padding-bottom: 10px; border-bottom: 1px solid var(--purple-dim);
  }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .field { margin-bottom: 20px; }
  .field:last-child { margin-bottom: 0; }

  label {
    display: block; font-family: 'Cinzel', serif; font-size: 10px;
    letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 8px;
  }
  .label-optional {
    font-family: 'Inter', sans-serif; font-size: 10px;
    color: var(--muted); letter-spacing: 0; margin-left: 6px; font-style: italic;
  }

  input[type="text"],
  input[type="tel"],
  input[type="date"],
  input[type="password"],
  input[type="url"],
  textarea {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 13px 16px;
    font-size: 15px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none;
    transition: border-color 0.2s, background 0.2s;
    -webkit-appearance: none;
  }
  input[type="date"] { color-scheme: dark; }
  input:focus, textarea:focus {
    border-color: var(--purple-lt);
    background: rgba(107,63,160,0.1);
  }
  input::placeholder, textarea::placeholder { color: var(--muted); }
  textarea { resize: vertical; min-height: 110px; }

  /* ── PASSWORD ── */
  .password-match {
    font-size: 13px; margin-top: 8px; height: 18px;
    transition: color 0.2s;
  }
  .password-match.ok  { color: var(--green); }
  .password-match.bad { color: var(--red); }

  /* ── CHECKBOXES ── */
  .checkbox-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; margin-top: 4px;
  }
  .checkbox-item {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 12px 16px;
    cursor: pointer; transition: background 0.15s, border-color 0.15s;
  }
  .checkbox-item:hover { background: rgba(107,63,160,0.1); border-color: var(--purple); }
  .checkbox-item input[type="checkbox"] {
    width: 18px; height: 18px; flex-shrink: 0;
    accent-color: var(--purple); cursor: pointer;
    padding: 0; margin: 0;
  }
  .checkbox-item span { font-size: 14px; color: var(--lilac); line-height: 1.3; }

  /* ── SUBMIT ── */
  .submit-btn {
    width: 100%; background: var(--purple); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px;
    cursor: pointer; margin-top: 32px;
    transition: background 0.2s, transform 0.15s, opacity 0.2s;
  }
  .submit-btn:disabled {
    opacity: 0.4; cursor: not-allowed; transform: none;
  }
  .submit-btn:not(:disabled):hover {
    background: var(--purple-lt); transform: translateY(-2px);
  }

  /* ── ERROR STATES ── */
  .error-wrap {
    width: 100%; max-width: 520px; text-align: center;
  }
  .error-icon { font-size: 52px; margin-bottom: 20px; }
  .error-wrap h1 {
    font-family: 'Cinzel', serif; font-size: clamp(26px,4vw,38px);
    color: var(--white); margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .error-card {
    background: var(--card); border: 1px solid rgba(224,85,85,0.35);
    border-radius: 14px; padding: 36px 40px; margin-bottom: 32px;
  }
  .error-card.warning { border-color: rgba(232,197,71,0.35); }
  .error-card p { font-size: 16px; color: var(--lilac); margin: 0; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-size: 14px; font-weight: 600; letter-spacing: 0.05em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }

  footer {
    position: relative; z-index: 1; text-align: center; padding: 24px;
    border-top: 1px solid var(--purple-dim);
    font-size: 12px; color: rgba(196,168,232,0.35);
  }
  footer a { color: var(--muted); text-decoration: none; }

  @media (max-width: 640px) {
    .form-card { padding: 32px 24px; }
    .field-row { grid-template-columns: 1fr; }
    .checkbox-grid { grid-template-columns: 1fr; }
    nav { padding: 0 20px; }
  }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/" class="nav-back">← Back to Home</a>
</nav>

<main>

<?php if ($errorState === 'already'): ?>
  <div class="error-wrap">
    <div class="error-icon">✦</div>
    <h1>Already Registered</h1>
    <div class="error-card warning">
      <p>This email address has already been confirmed. You're already part of the Mythos community!</p>
    </div>
    <a href="/" class="btn-primary">Back to Mythos Events</a>
  </div>

<?php elseif ($errorState === 'invalid'): ?>
  <div class="error-wrap">
    <div class="error-icon">⚠️</div>
    <h1>Invalid Link</h1>
    <div class="error-card">
      <p>This confirmation link doesn't look right. Please check your email and try clicking the link again, or <a href="mailto:wade@mythosevents.com" style="color:var(--purple-lt)">contact us</a> for help.</p>
    </div>
    <a href="/join/" class="btn-primary">Start Over</a>
  </div>

<?php else: ?>

  <div class="page-header">
    <div class="eyebrow">You're Almost In</div>
    <h1>Complete Your Profile</h1>
    <p>Tell us a little about yourself so we know how to work together. This takes about two minutes.</p>
  </div>

  <div class="form-card">
    <form action="submit.php" method="post" onsubmit="return validateForm()">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
      <input type="hidden" name="recruiter" value="<?php echo htmlspecialchars($recruiterId); ?>">

      <!-- NAME & CONTACT -->
      <div class="form-section">
        <div class="section-label">About You</div>

        <div class="field-row">
          <div class="field">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" placeholder="First name" required>
          </div>
          <div class="field">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" placeholder="Last name" required>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="phoneNumber">Phone <span class="label-optional">optional</span></label>
            <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="(555) 000-0000">
          </div>
          <div class="field">
            <label for="dob">Date of Birth <span class="label-optional">optional</span></label>
            <input type="date" id="dob" name="dob">
            <p style="font-size:12px;color:var(--muted);margin-top:6px;">Helps us verify age requirements for venues and stay compliant with minor-related regulations.</p>
          </div>
        </div>
      </div>

      <!-- PASSWORD -->
      <div class="form-section">
        <div class="section-label">Create a Password</div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
            placeholder="Choose a password" required oninput="checkPasswordMatch()">
        </div>
        <div class="field">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword"
            placeholder="Confirm your password" required oninput="checkPasswordMatch()">
          <div class="password-match" id="passwordMatchMessage"></div>
        </div>
      </div>

      <!-- ROLES -->
      <div class="form-section">
        <div class="section-label">How Do You Want to Be Involved?</div>
        <p style="font-size:14px;color:var(--muted);margin-bottom:16px;">Choose all that apply — you can always update this later.</p>
        <div class="checkbox-grid">
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Vendor">
            <span>🛖 Vendor</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Organizer">
            <span>📋 Organizer</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="volunteer">
            <span>🌟 General Help / Setup / Takedown</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Sales">
            <span>🎟️ Sales</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Performer">
            <span>🎭 Performer</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Artist">
            <span>🎨 Artist</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Operations">
            <span>⚙️ Operations</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Venue Manager/Owner">
            <span>🏛️ Venue Manager/Owner</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="roles[]" value="Other">
            <span>✨ Something Else</span>
          </label>
        </div>
      </div>

      <!-- SERVICE AREAS -->
      <div class="form-section">
        <div class="section-label">Where Are You Available to Work?</div>
        <div class="field">
          <label for="serviceAreaAddress">Your Location or Work Area</label>
          <input type="text" id="serviceAreaAddress" name="serviceAreaAddress"
            placeholder="e.g., 'Phoenix, AZ' or '123 Main St, Los Angeles, CA'" required>
          <div id="geocodeStatus" style="font-size:13px;margin-top:8px;min-height:18px;"></div>
        </div>
        <div class="field">
          <label for="serviceAreaRadius">Willing to Travel Up To: <span id="radiusDisplay">30</span> miles</label>
          <input type="range" id="serviceAreaRadius" name="serviceAreaRadius"
            min="1" max="500" value="30"
            style="width:100%;cursor:pointer;height:6px;">
          <div style="font-size:12px;color:var(--muted);margin-top:6px;display:flex;justify-content:space-between;">
            <span>1 mile</span>
            <span>500 miles</span>
          </div>
        </div>
        <input type="hidden" id="serviceAreaLatitude" name="serviceAreaLatitude" value="">
        <input type="hidden" id="serviceAreaLongitude" name="serviceAreaLongitude" value="">
      </div>

      <!-- ABOUT -->
      <div class="form-section">
        <div class="section-label">Tell Us More</div>
        <div class="field">
          <label for="message">Why Do You Want to Join? <span class="label-optional">optional</span></label>
          <textarea id="message" name="message"
            placeholder="Why do you want to be part of Mythos Events? How do you hope to be involved, or what do you want to get out of it?"></textarea>
        </div>
        <div class="field">
          <label for="description">Introduce Yourself in Third Person <span class="label-optional">optional</span></label>
          <textarea id="description" name="description"
            placeholder="Describe your talents and skills in the third person — e.g. 'She is an incredible improv dancer who has performed at festivals across Arizona...'"></textarea>
        </div>
        <div class="field">
          <label for="website">Your Website or Social Link <span class="label-optional">optional</span></label>
          <input type="url" id="website" name="website" placeholder="https://yourwebsite.com">
        </div>
      </div>

      <button type="submit" id="submitButton" disabled class="submit-btn">
        Complete My Profile ✦
      </button>
    </form>
  </div>

<?php endif; ?>

</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wade@mythosevents.com">wade@mythosevents.com</a></p>
</footer>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAv7ZlQy5ZaooKBRw64ZsbQN6W6rgoshPo&libraries=places"></script>
<script>
  // Stars
  const container = document.getElementById('stars');
  for (let i = 0; i < 120; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }

  // Geocoding
  const geocoder = new google.maps.Geocoder();
  const addressInput = document.getElementById('serviceAreaAddress');
  const statusDiv = document.getElementById('geocodeStatus');
  const latInput = document.getElementById('serviceAreaLatitude');
  const lngInput = document.getElementById('serviceAreaLongitude');
  const radiusSlider = document.getElementById('serviceAreaRadius');
  const radiusDisplay = document.getElementById('radiusDisplay');

  // Update radius display
  radiusSlider.addEventListener('input', () => {
    radiusDisplay.textContent = radiusSlider.value;
  });

  // Geocode address on blur
  addressInput.addEventListener('blur', geocodeAddress);

  function geocodeAddress() {
    const address = addressInput.value.trim();
    if (!address) {
      statusDiv.textContent = '';
      latInput.value = '';
      lngInput.value = '';
      return;
    }

    geocoder.geocode({ address: address }, (results, status) => {
      if (status === 'OK') {
        const location = results[0].geometry.location;
        const lat = location.lat();
        const lng = location.lng();
        const formattedAddress = results[0].formatted_address;

        latInput.value = lat;
        lngInput.value = lng;

        statusDiv.innerHTML = `<span style="color:var(--green);">✓ Found: ${formattedAddress}</span>`;
      } else if (status === 'ZERO_RESULTS') {
        statusDiv.innerHTML = `<span style="color:var(--red);">✗ Address not found. Try being more specific.</span>`;
        latInput.value = '';
        lngInput.value = '';
      } else {
        statusDiv.innerHTML = `<span style="color:var(--red);">✗ Geocoding error: ${status}</span>`;
        latInput.value = '';
        lngInput.value = '';
      }
    });
  }

  // Password match
  function checkPasswordMatch() {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirmPassword').value;
    const msg = document.getElementById('passwordMatchMessage');
    const btn = document.getElementById('submitButton');
    if (cpw.length === 0) {
      msg.textContent = ''; msg.className = 'password-match';
      btn.disabled = true; return;
    }
    if (pw === cpw) {
      msg.textContent = '✓ Passwords match';
      msg.className = 'password-match ok';
      btn.disabled = false;
    } else {
      msg.textContent = '✗ Passwords do not match';
      msg.className = 'password-match bad';
      btn.disabled = true;
    }
  }

  function validateForm() {
    const lat = document.getElementById('serviceAreaLatitude').value;
    const lng = document.getElementById('serviceAreaLongitude').value;
    if (!lat || !lng) {
      alert('Please enter a valid location and wait for it to be geocoded.');
      return false;
    }
    return true;
  }
</script>
</body>
</html>