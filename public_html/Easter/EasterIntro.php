<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Protect the Dragon Eggs — Mythos Events</title>
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
    font-family: 'Inter', sans-serif; text-align: center;
    min-height: 100vh; padding: 40px 20px; overflow-x: hidden; position: relative;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: #fff; animation: twinkle var(--dur) ease-in-out infinite var(--delay); }
  @keyframes twinkle { 0%,100% { opacity: 0.1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.5); } }
  .content { position: relative; z-index: 1; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
  h2 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(26px, 5vw, 38px);
    color: var(--white); margin-bottom: 32px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .video-wrapper { position: relative; max-width: 600px; margin: auto; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  video { width: 100%; display: block; }
  #unmuteButton {
    position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
    padding: 16px 28px; font-size: 16px; font-family: 'Cinzel', serif; letter-spacing: 0.1em;
    border: none; border-radius: 8px; background: var(--purple); color: var(--white); cursor: pointer;
    transition: background 0.2s, transform 0.15s;
  }
  #unmuteButton:hover { background: var(--purple-lt); transform: translateX(-50%) translateY(-2px); }
  .signup-container {
    background: var(--card); padding: 32px; max-width: 420px; margin: 32px auto;
    border-radius: 14px; border: 1px solid var(--purple-dim); box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .signup-container h3 { font-family: 'Cinzel', serif; font-size: 20px; color: var(--white); margin-bottom: 12px; }
  .signup-container p { font-size: 14px; color: var(--muted); margin-bottom: 20px; }
  input {
    width: 100%; padding: 14px 16px; margin: 8px 0; box-sizing: border-box;
    border: 1px solid var(--purple-dim); border-radius: 8px;
    background: rgba(255,255,255,0.05); color: var(--white); font-size: 15px; font-family: 'Inter', sans-serif;
  }
  input::placeholder { color: var(--muted); }
  input:focus { outline: none; border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
  button[type="submit"] {
    width: 100%; background: var(--purple); color: var(--white);
    padding: 14px 20px; border: none; border-radius: 8px;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em; cursor: pointer; margin-top: 8px;
    transition: background 0.2s, transform 0.15s;
  }
  button[type="submit"]:hover { background: var(--purple-lt); transform: translateY(-2px); }
</style>
</head>

<body>

<div id="stars"></div>

<div class="content">

<div class="eyebrow">Easter Egg Hunt</div>
<h2>The Easter Bunny Has a Message</h2>

<div class="video-wrapper">
<video id="introVideo" autoplay muted playsinline>
    <source src="Intro.mp4" type="video/mp4">
</video>
<button id="unmuteButton">🔊 Tap to Hear the Bunny</button>
</div>

<div class="signup-container">
<h3>Create Your Basket</h3>
<p>Enter your name and email to begin protecting dragon eggs.</p>

<form action="EggHuntsignup.php" method="post">
<input type="text" name="name" placeholder="Your Name" required>
<input type="email" name="email" placeholder="Your Email" required>
<input type="hidden" name="code" value="<?php echo htmlspecialchars($egg_code); ?>">
<button type="submit">Begin Protecting Eggs ✦</button>
</form>

</div>

</div>

<script>
const container = document.getElementById('stars');
for (let i = 0; i < 120; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    const sz = Math.random() * 2.5 + 0.4;
    s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
    container.appendChild(s);
}

const video = document.getElementById("introVideo");
const button = document.getElementById("unmuteButton");

button.addEventListener("click", function(){
    video.muted = false;
    video.play();
    button.style.display = "none";
});
</script>

</body>
</html>
