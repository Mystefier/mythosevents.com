<?php
session_start();
if (!isset($_SESSION['person_id'])) {
    header("Location: login.php");
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    $stmt = mysqli_prepare($conn, "SELECT password, salt FROM people WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $personId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && $user['password'] && password_verify($password . $user['salt'], $user['password'])) {
        // Auto-adoption: before deleting, grab this person's own recruiter,
        // then reassign everyone THEY recruited to that same recruiter —
        // so referral credit passes up the chain instead of disappearing.
        $recruiterStmt = mysqli_prepare($conn, "SELECT recruiter FROM people WHERE id = ?");
        mysqli_stmt_bind_param($recruiterStmt, "i", $personId);
        mysqli_stmt_execute($recruiterStmt);
        $recruiterResult = mysqli_stmt_get_result($recruiterStmt);
        $recruiterRow = mysqli_fetch_assoc($recruiterResult);
        $newRecruiter = $recruiterRow ? intval($recruiterRow['recruiter']) : 1;
        mysqli_stmt_close($recruiterStmt);

        $adoptStmt = mysqli_prepare($conn, "UPDATE people SET recruiter = ? WHERE recruiter = ?");
        mysqli_stmt_bind_param($adoptStmt, "ii", $newRecruiter, $personId);
        mysqli_stmt_execute($adoptStmt);
        mysqli_stmt_close($adoptStmt);

        $delStmt = mysqli_prepare($conn, "DELETE FROM people WHERE id = ?");
        mysqli_stmt_bind_param($delStmt, "i", $personId);
        mysqli_stmt_execute($delStmt);
        mysqli_stmt_close($delStmt);
        mysqli_close($conn);

        session_unset();
        session_destroy();
        header("Location: /?deleted=1");
        exit();
    } else {
        $errorMsg = 'That password is incorrect. Please try again.';
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delete Account — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Cinzel+Decorative:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight: #0D0B1A; --card: #201C32; --purple: #6B3FA0; --purple-lt: #9B6FD0;
    --purple-dim: rgba(107,63,160,0.25); --gold: #E8C547; --lilac: #C4A8E8;
    --white: #FFFFFF; --muted: rgba(196,168,232,0.6); --red: #E05555;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
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
  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 20px; position: relative; z-index: 1; }
  .wrap { width: 100%; max-width: 440px; }
  .page-header { text-align: center; margin-bottom: 32px; }
  .eyebrow { font-family: 'Cinzel Decorative', serif; font-size: 10px; letter-spacing: 0.4em; color: var(--red); margin-bottom: 16px; }
  .page-header h1 { font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 38px); color: var(--white); margin-bottom: 16px; }
  .page-header p { font-size: 14px; color: var(--muted); }
  .form-card { background: var(--card); border: 1px solid rgba(220,80,80,0.35); border-radius: 14px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .field { margin-bottom: 20px; }
  label { display: block; font-family: 'Cinzel', serif; font-size: 11px; letter-spacing: 0.2em; color: var(--purple-lt); margin-bottom: 10px; }
  input[type="password"] {
    width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--purple-dim);
    border-radius: 8px; padding: 16px 18px; font-size: 16px; font-family: 'Inter', sans-serif;
    color: var(--white); outline: none;
  }
  input:focus { border-color: var(--red); }
  .error-msg { color: var(--red); font-size: 13px; margin-bottom: 16px; }
  .submit-btn {
    width: 100%; background: var(--red); color: var(--white);
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    padding: 16px 32px; border: none; border-radius: 8px; cursor: pointer;
    transition: background 0.2s, transform 0.15s;
  }
  .submit-btn:hover { background: #c94444; transform: translateY(-2px); }
  .cancel-link { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: var(--muted); text-decoration: none; }
  .cancel-link:hover { color: var(--white); }
  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>
</nav>

<main>
  <div class="wrap">
    <div class="page-header">
      <div class="eyebrow">This Can't Be Undone</div>
      <h1>Delete Your Account</h1>
      <p>Enter your password to confirm. This permanently removes your profile and application from our system.</p>
    </div>

    <div class="form-card">
      <?php if ($errorMsg): ?><p class="error-msg"><?php echo htmlspecialchars($errorMsg); ?></p><?php endif; ?>
      <form action="delete-account.php" method="post">
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Confirm your password" required autofocus>
        </div>
        <button type="submit" class="submit-btn">Permanently Delete My Account</button>
      </form>
      <a href="dashboard.php" class="cancel-link">Cancel</a>
    </div>
  </div>
</main>

<footer>
  <p>&copy; 2026 Mythos Events &nbsp;·&nbsp; Glendale, Arizona &nbsp;·&nbsp; <a href="mailto:wadehawkins@mythosevents.com">wadehawkins@mythosevents.com</a></p>
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
