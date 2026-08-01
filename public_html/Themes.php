<?php
// Prevent SiteGround or browser caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Random Theme Generator — Mythos Events</title>
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
    --gold-dim:   rgba(232,197,71,0.15);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif;
    display: flex; justify-content: center; align-items: center;
    min-height: 100vh; overflow-x: hidden;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: #fff; animation: twinkle var(--dur) ease-in-out infinite var(--delay); }
  @keyframes twinkle { 0%,100% { opacity: 0.1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.5); } }
  .container {
    position: relative; z-index: 1;
    background: var(--card); border: 1px solid var(--purple-dim);
    padding: 40px; border-radius: 14px; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    max-width: 400px; width: 100%; text-align: center;
  }
  h2 { font-family: 'Cinzel', serif; font-weight: 900; font-size: 24px; color: var(--white); margin-bottom: 8px; text-shadow: 0 0 40px rgba(107,63,160,0.7); }
  label { display: block; margin-top: 20px; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.15em; color: var(--purple-lt); text-align: left; margin-bottom: 8px; }
  select {
    width: 100%; padding: 12px 14px; border-radius: 8px;
    border: 1px solid var(--purple-dim); font-size: 15px;
    background-color: rgba(255,255,255,0.05); color: var(--white); font-family: 'Inter', sans-serif;
  }
  select:focus { outline: none; border-color: var(--purple-lt); }
  button {
    background: var(--purple); color: var(--white); border: none;
    padding: 14px 20px; font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    margin-top: 24px; border-radius: 8px; cursor: pointer;
    transition: background 0.2s, transform 0.15s; width: 100%;
  }
  button:hover { background: var(--purple-lt); transform: translateY(-2px); }
  #result {
    margin-top: 24px; font-size: 20px; font-weight: 600;
    font-family: 'Cinzel', serif; color: var(--white);
    background: var(--gold-dim); border: 1px solid rgba(232,197,71,0.3);
    padding: 16px; border-radius: 10px; min-height: 24px;
  }
</style>
</head>
<body>

<div id="stars"></div>

<div class="container">
    <h2>Random Theme Generator</h2>

    <!-- Dropdowns for selecting categories -->
    <label for="cat1">Category 1:</label>
    <select id="cat1">
        <option value="general">General</option>
        <option value="halloween">Halloween</option>
        <option value="christmas">Christmas</option>
    </select>

    <label for="cat2">Category 2:</label>
    <select id="cat2">
        <option value="general">General</option>
        <option value="halloween">Halloween</option>
        <option value="christmas">Christmas</option>
    </select>

    <button onclick="generatePrompt()">Generate Prompt</button>

    <div id="result"></div>
</div>

<script>
  const container = document.getElementById('stars');
  for (let i = 0; i < 80; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
  }
</script>

<script>
// Theme data
const themes = {
    general: [
        "Cyberpunk","Sci-fi","Masquerade","Carnival","Harlequin","Rococo","Middle Ages","Renaissance","Baroque",
        "Ancient Egypt","Ancient Greece","Ancient Rome","Ancient Persia","Ancient China","Ancient Japan","India",
        "Hawaii","Wild West","Victorian","Nautical","Aquatic","Forest","Glow in the dark","Wonderland","Pirate",
        "Africa","Arabia","Toyland","Candyland","Anime","Mountain","Adventure","Music","Fantasy","Birds","Mystery",
        "Future","Dragons","Castle","Rag Time","20’s","30’s","40’s","50’s","60’s","70’s","80’s","90’s","Disco",
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

// Random prompt generator
function generatePrompt() {
    const cat1 = document.getElementById("cat1").value;
    const cat2 = document.getElementById("cat2").value;

    const theme1 = themes[cat1][Math.floor(Math.random() * themes[cat1].length)];
    const theme2 = themes[cat2][Math.floor(Math.random() * themes[cat2].length)];

    document.getElementById("result").textContent = `${theme1} + ${theme2}`;
}
</script>

</body>
</html>
