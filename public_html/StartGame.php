<?php
$statusType = 'error';
$status = 'Invalid request.';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['player_name']) && isset($_POST['email'])) {
    $player_name = htmlspecialchars($_POST['player_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Open database connection
        $dbname = "db9dh4gg0yfw3q";
        include('logintodatabase.php');

        // Get IP Address
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // Prepare and execute the query
        $stmt = $conn->prepare("INSERT INTO EggHunters (name, email, IP) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $player_name, $email, $ip_address);

        if ($stmt->execute()) {
            $statusType = 'success';
            $status = "Player Registered: $player_name (Email: $email)";
        } else {
            $statusType = 'error';
            $status = 'Error: Could not save player data.';
        }

        $stmt->close();
        $conn->close();
    } else {
        $statusType = 'error';
        $status = 'Invalid email address.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Egg Hunt Registration — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --card:       #201C32;
    --purple:     #6B3FA0;
    --purple-lt:  #9B6FD0;
    --purple-dim: rgba(107,63,160,0.25);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --muted:      rgba(196,168,232,0.6);
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 40px 20px;
  }
  .status-icon { font-size: 52px; margin-bottom: 20px; text-align: center; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(26px, 5vw, 34px);
    color: var(--white); margin-bottom: 20px; text-align: center;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .status-card { background: var(--card); border-radius: 14px; padding: 28px 32px; max-width: 480px; text-align: center; }
  .status-card.success { border: 1px solid rgba(82,200,122,0.4); }
  .status-card.error   { border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin: 0; }
</style>
</head>
<body>
  <div class="status-icon"><?php echo $statusType === 'success' ? '✦' : '✗'; ?></div>
  <h1><?php echo $statusType === 'success' ? 'Registered!' : 'Registration Failed'; ?></h1>
  <div class="status-card <?php echo $statusType; ?>">
    <p><?php echo $status; ?></p>
  </div>
</body>
</html>
