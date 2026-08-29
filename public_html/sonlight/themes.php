<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Theme Picker — Sonlight Drama Team</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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
  }
  body {
    background: var(--sun-bg); color: var(--sun-text);
    font-family: 'Nunito', sans-serif; font-size: 17px; line-height: 1.7;
    min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden;
  }
  h1, h2, h3 { font-family: 'Poppins', sans-serif; }

  nav {
    padding: 0 40px; height: 68px; display: flex; align-items: center; justify-content: space-between;
    background: var(--sun-card); border-bottom: 2px solid var(--sun-border); position: sticky; top: 0; z-index: 10;
  }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 20px; color: var(--sun-text); text-decoration: none; }
  .nav-logo span { color: var(--sun-primary); }
  .nav-links { display: flex; gap: 24px; align-items: center; }
  .nav-links a { color: var(--sun-muted); text-decoration: none; font-weight: 700; font-size: 14px; }
  .nav-links a:hover { color: var(--sun-primary); }
  .nav-links a.active { color: var(--sun-primary); }
  .nav-account { display: flex; align-items: center; gap: 20px; }
  .nav-logged-out { display: flex; align-items: center; gap: 20px; }
  .nav-logged-in { display: none; position: relative; }
  .nav-clock { font-family: 'Poppins', sans-serif; font-size: 13px; letter-spacing: 0.06em; color: var(--sun-muted); min-width: 58px; text-align: right; white-space: nowrap; }
  .nav-cta { background: var(--sun-primary); color: #fff !important; padding: 10px 22px; border-radius: 30px; }
  .nav-cta:hover { background: var(--sun-primary-dk) !important; }
  .nav-dropdown { position: relative; }
  .nav-dropdown > a { color: var(--sun-text); text-decoration: none; font-weight: 700; font-size: 14px; }
  .nav-dropdown > a:hover { color: var(--sun-primary); }
  .nav-dropdown-panel { display: none; position: absolute; right: 0; top: calc(100% + 14px); background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 12px; padding: 8px; min-width: 160px; z-index: 100; box-shadow: 0 8px 24px rgba(58,46,42,0.1); }
  .nav-dropdown:hover .nav-dropdown-panel { display: block; }
  .nav-dropdown-panel a { display: block; padding: 10px 14px; font-weight: 700; font-size: 14px; color: var(--sun-muted); text-decoration: none; border-radius: 8px; }
  .nav-dropdown-panel a:hover { background: #FFF3E8; color: var(--sun-primary); }
  .nav-hover { position: relative; }
  .nav-hover-panel { display: none; position: absolute; top: 100%; right: 0; padding-top: 14px; z-index: 50; }
  .nav-hover:hover .nav-hover-panel,
  .nav-hover:focus-within .nav-hover-panel { display: block; }
  .nav-hover-panel-inner { background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 12px; padding: 20px; width: 240px; box-shadow: 0 12px 40px rgba(58,46,42,0.12); }
  .nav-hover-panel-inner input { width: 100%; margin-bottom: 10px; padding: 10px 12px; border-radius: 8px; border: 2px solid var(--sun-border); background: var(--sun-bg); color: var(--sun-text); font-size: 13px; font-family: 'Nunito', sans-serif; }
  .nav-hover-panel-inner input:focus { outline: none; border-color: var(--sun-primary); }
  .nav-hover-panel-inner button { width: 100%; padding: 10px; border: none; border-radius: 8px; cursor: pointer; background: var(--sun-primary); color: #fff; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13px; transition: background 0.2s; }
  .nav-hover-panel-inner button:hover { background: var(--sun-primary-dk); }
  .nav-hover-panel-inner .form-footer { text-align: center; margin-top: 10px; font-size: 12px; color: var(--sun-muted); }
  .nav-hover-panel-inner .form-footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }

  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; }

  .card {
    background: var(--sun-card); border: 2px solid var(--sun-border); border-radius: 20px;
    padding: 48px 44px; max-width: 440px; width: 100%;
    box-shadow: 0 12px 40px rgba(255,123,79,0.08); text-align: center;
  }

  .card-icon { font-size: 40px; margin-bottom: 16px; }
  .eyebrow {
    font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11px; letter-spacing: 0.2em;
    color: var(--sun-primary); text-transform: uppercase; margin-bottom: 10px;
  }
  h1 { font-weight: 900; font-size: 26px; margin-bottom: 8px; line-height: 1.2; }
  .subtitle { font-size: 14px; color: var(--sun-muted); margin-bottom: 28px; }

  label {
    display: block; text-align: left; font-weight: 700; font-size: 12px; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--sun-primary); margin-bottom: 8px; margin-top: 20px;
  }
  label:first-of-type { margin-top: 0; }

  select {
    width: 100%; padding: 12px 14px; border-radius: 10px;
    border: 2px solid var(--sun-border); font-size: 15px;
    background: var(--sun-bg); color: var(--sun-text); font-family: 'Nunito', sans-serif;
    transition: border-color 0.2s; cursor: pointer;
  }
  select:focus { outline: none; border-color: var(--sun-primary); }

  button {
    background: var(--sun-primary); color: #fff; border: none;
    padding: 15px 20px; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 15px;
    margin-top: 24px; border-radius: 30px; cursor: pointer; width: 100%;
    transition: background 0.2s, transform 0.15s;
  }
  button:hover { background: var(--sun-primary-dk); transform: translateY(-2px); }

  #result {
    margin-top: 24px; min-height: 28px;
    font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 22px; color: var(--sun-text);
    background: #FFF3E8; border: 2px solid var(--sun-gold);
    border-radius: 12px; padding: 18px 20px; line-height: 1.3;
    display: none;
  }
  #result.show { display: block; }

  footer {
    text-align: center; padding: 24px; border-top: 2px solid var(--sun-border);
    font-size: 13px; color: var(--sun-muted);
  }
  footer a { color: var(--sun-primary); text-decoration: none; font-weight: 700; }

  @media (max-width: 560px) {
    nav { padding: 0 20px; }
    .card { padding: 32px 24px; }
  }
</style>
</head>
<body>

<nav>
  <a href="/sonlight/" class="nav-logo">Son<span>light</span></a>
  <div class="nav-links">
    <a href="/sonlight/scheduler.php">Scheduler</a>
    <a href="/sonlight/themes.php" class="active">Theme Picker</a>
    <div class="nav-account" id="navAccount">
      <div class="nav-logged-out" id="navLoggedOut">
        <a href="/sonlight/join.php" class="nav-cta">Join the Team</a>
        <div class="nav-hover">
          <a href="/join/login.php">Log In</a>
          <div class="nav-hover-panel">
            <div class="nav-hover-panel-inner">
              <form action="/join/LoginProcess.php" method="post">
                <input type="hidden" name="return_url" value="/sonlight/dashboard.php">
                <input type="email" name="email" placeholder="Email" required autocomplete="email">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In ☀️</button>
                <div class="form-footer"><a href="/join/ForgotPassword.php">Forgot password?</a></div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="nav-dropdown nav-logged-in" id="navLoggedIn">
        <a href="#" id="navUserName">Account ▾</a>
        <div class="nav-dropdown-panel">
          <a href="/sonlight/dashboard.php">📋 Dashboard</a>
          <a href="/sonlight/scheduler.php">☀️ Scheduler</a>
          <a href="/join/logout.php">🚪 Log Out</a>
        </div>
      </div>
      <div class="nav-clock" id="navClock">--:--</div>
    </div>
  </div>
</nav>

<main>
  <div class="card">
    <div class="card-icon">🎭</div>
    <div class="eyebrow">Sonlight Drama Team</div>
    <h1>Random Theme Generator</h1>
    <p class="subtitle">Spin up two random categories and see what theme lands — great for picking a scene, a skit premise, or just sparking creativity.</p>

    <label for="cat1">Category 1</label>
    <select id="cat1">
      <option value="general">General</option>
      <option value="halloween">Halloween</option>
      <option value="christmas">Christmas</option>
    </select>

    <label for="cat2">Category 2</label>
    <select id="cat2">
      <option value="general">General</option>
      <option value="halloween">Halloween</option>
      <option value="christmas">Christmas</option>
    </select>

    <button onclick="generatePrompt()">Generate Theme ✦</button>

    <div id="result"></div>
  </div>
</main>

<footer>
  <p>Sonlight Drama Team &nbsp;·&nbsp; part of <a href="/">Mythos Events</a></p>
</footer>

<script>
const themes = {
  general: [
    "Cyberpunk","Sci-fi","Masquerade","Carnival","Harlequin","Rococo","Middle Ages","Renaissance","Baroque",
    "Ancient Egypt","Ancient Greece","Ancient Rome","Ancient Persia","Ancient China","Ancient Japan","India",
    "Hawaii","Wild West","Victorian","Nautical","Aquatic","Forest","Glow in the dark","Wonderland","Pirate",
    "Africa","Arabia","Toyland","Candyland","Anime","Mountain","Adventure","Music","Fantasy","Birds","Mystery",
    "Future","Dragons","Castle","Rag Time","20's","30's","40's","50's","60's","70's","80's","90's","Disco",
    "Time Machine","Carousel","Jazz","Dinosaurs","Greek gods","Norse gods","Wizards","Aliens","Ancient Aliens",
    "Elves","Angels","City","Flowers","Steampunk","Fairies","Crystals","Butterflies","Magic","Barbarian","Brazil",
    "Tango","Stars","Angel","Spy","Super Hero","Volcano","Nepal","France","Ireland","Mayan","Amazon","Fairies",
    "Inca","Aztec","Animals","Garden","British","Psychedelic","Austrian","Circus","Jungle","Labyrinth","Monster",
    "Thailand","Island","Desert","Mexico","Beach","Post-Apocalyptic","Clown","Gothic"
  ],
  halloween: [
    "Vampire","Witches","Hell","Scarecrow","Werewolf","Jack-O-Lantern","Torture Chamber","Mad Scientist Lab",
    "Grave yard","Nightmare","Voodoo","Spiders","Mummy","Skeletons","Ghost","Devil","Hypnotist","Wizard",
    "Dark Woods","Aliens"
  ],
  christmas: [
    "Toyland","Candy Land","Snow Queen","Nativity","Jack Frost","Nutcracker Suit","North Pole","Angels"
  ]
};

function generatePrompt() {
  const cat1 = document.getElementById("cat1").value;
  const cat2 = document.getElementById("cat2").value;
  const theme1 = themes[cat1][Math.floor(Math.random() * themes[cat1].length)];
  const theme2 = themes[cat2][Math.floor(Math.random() * themes[cat2].length)];
  const result = document.getElementById("result");
  result.textContent = theme1 + " + " + theme2;
  result.classList.add("show");
}
</script>

<script>
(function() {
  const clockEl = document.getElementById('navClock');
  if (!clockEl) return;
  function tick() {
    const now = new Date(), m = String(now.getMinutes()).padStart(2,'0');
    let h = now.getHours(); const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
    clockEl.textContent = h + ':' + m + ' ' + ampm;
  }
  tick(); setInterval(tick, 1000);
})();
(function() {
  const lo = document.getElementById('navLoggedOut'), li = document.getElementById('navLoggedIn'), nm = document.getElementById('navUserName');
  if (!lo || !li) return;
  fetch('/join/whoami.php', { credentials: 'same-origin' }).then(r => r.json()).then(d => {
    if (d && d.loggedIn) { lo.style.display = 'none'; li.style.display = 'block'; if (nm) nm.textContent = d.name + ' ▾'; }
  }).catch(() => {});
})();
</script>
</body>
</html>
