<?php
// Open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Retrieve the email address from the form
$email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';

// Check if the email is valid
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Retrieve existing information from the database
    $selectSql = "SELECT * FROM people WHERE email = '$email'";
    $result = mysqli_query($conn, $selectSql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Application — Mythos Events</title>
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
            font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.7;
            min-height: 100vh; overflow-x: hidden;
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
          main { position: relative; z-index: 1; max-width: 640px; margin: 0 auto; padding: 60px 20px; }
          .page-header { text-align: center; margin-bottom: 36px; }
          .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--purple-lt); margin-bottom: 16px; }
          .page-header h1 { font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 38px); color: var(--white); text-shadow: 0 0 40px rgba(107,63,160,0.7); }
          .form-card { background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
          .field { margin-bottom: 22px; }
          .field:last-of-type { margin-bottom: 0; }
          .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
          label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.15em; color: var(--purple-lt); margin-bottom: 8px; }
          input, textarea {
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
            border-radius: 8px; padding: 14px 16px; font-size: 15px; font-family: 'Inter', sans-serif;
            color: var(--white); outline: none; transition: border-color 0.2s, background 0.2s; resize: vertical;
          }
          input:focus, textarea:focus { border-color: var(--purple-lt); background: rgba(107,63,160,0.1); }
          input::placeholder, textarea::placeholder { color: var(--muted); }
          .submit-btn {
            width: 100%; background: var(--purple); color: var(--white);
            font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
            padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer; margin-top: 8px;
            transition: background 0.2s, transform 0.15s;
          }
          .submit-btn:hover { background: var(--purple-lt); transform: translateY(-2px); }
          footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
          footer a { color: var(--muted); text-decoration: none; }
          @media (max-width: 600px) { nav { padding: 0 20px; } .form-card { padding: 28px 20px; } .row-2 { grid-template-columns: 1fr; } }
        </style>
        </head>
        <body>

        <div id="stars"></div>

        <nav>
          <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
          <a href="/" class="nav-back">← Back to Home</a>
        </nav>

        <main>
          <div class="page-header">
            <div class="eyebrow">Your Profile</div>
            <h1>Edit Application</h1>
          </div>

          <div class="form-card">
            <form action="Update.php" method="post">
              <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">

              <div class="row-2">
                <div class="field">
                  <label for="firstName">First Name</label>
                  <input type="text" id="firstName" name="firstName" placeholder="First Name" value="<?php echo htmlspecialchars($row['first']); ?>" required>
                </div>
                <div class="field">
                  <label for="lastName">Last Name</label>
                  <input type="text" id="lastName" name="lastName" placeholder="Last Name" value="<?php echo htmlspecialchars($row['last']); ?>" required>
                </div>
              </div>

              <div class="field">
                <label for="phoneNumber">Phone Number</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Phone Number" value="<?php echo htmlspecialchars($row['phone']); ?>">
              </div>

              <div class="field">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($row['dob']); ?>" required>
              </div>

              <div class="field">
                <label for="message">Why do you want to join the events team?</label>
                <textarea id="message" name="message" rows="5"><?php echo htmlspecialchars($row['message']); ?></textarea>
              </div>

              <div class="field">
                <label for="roles">Roles You're Interested In</label>
                <textarea id="roles" name="roles" rows="3"><?php echo htmlspecialchars($row['roles']); ?></textarea>
              </div>

              <input type="hidden" name="recruiter" value="<?php echo htmlspecialchars($row['recruiter']); ?>">

              <div class="field">
                <label for="description">Describe Yourself</label>
                <textarea id="description" name="description" rows="5" placeholder="Describe yourself..."><?php echo htmlspecialchars($row['description']); ?></textarea>
              </div>

              <div class="field">
                <label for="website">Website</label>
                <input type="url" id="website" name="website" placeholder="https://your-website.com" value="<?php echo htmlspecialchars($row['website']); ?>">
              </div>

              <button type="submit" class="submit-btn">Update ✦</button>
            </form>
          </div>
        </main>

        <footer>
          <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wade@mythosevents.com">wade@mythosevents.com</a></p>
        </footer>

        <script>
          const container = document.getElementById('stars');
          for (let i = 0; i < 120; i++) {
            const s = document.createElement('div');
            s.className = 'star';
            const sz = Math.random() * 2.5 + 0.4;
            s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${2+Math.random()*5}s;--delay:${Math.random()*6}s`;
            container.appendChild(s);
          }
        </script>
        </body>
        </html>
        <?php
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Not Found — Mythos Events</title>
        <style>
          body { background:#0D0B1A; color:#C4A8E8; font-family:'Inter',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
          a { color:#9B6FD0; }
        </style>
        </head>
        <body>
          <p>No information found for the provided email. <a href="login.php">Back to Login</a></p>
        </body>
        </html>
        <?php
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Email — Mythos Events</title>
    <style>
      body { background:#0D0B1A; color:#C4A8E8; font-family:'Inter',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
      a { color:#9B6FD0; }
    </style>
    </head>
    <body>
      <p>Invalid email address. Please enter a valid email. <a href="login.php">Back to Login</a></p>
    </body>
    </html>
    <?php
}

// Close the database connection
mysqli_close($conn);
?>
