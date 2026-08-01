<?php
// Include the database connection
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Initialize variables for error messages and success
$errors = [];
$successMessage = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize inputs
    $first = htmlspecialchars(trim($_POST['first']));
    $last = htmlspecialchars(trim($_POST['last']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $business = htmlspecialchars(trim($_POST['business']));
    $description = htmlspecialchars(trim($_POST['description']));
    $affiliate = isset($_POST['workshop']) && $_POST['workshop'] === "yes" ? "yes" : "no";

    // Basic validation
    if (empty($first)) $errors[] = "First name is required.";
    if (empty($last)) $errors[] = "Last name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($business)) $errors[] = "Business name is required.";
    if (empty($description)) $errors[] = "Business description is required.";

    if (empty($errors)) {
        // Combine first and last name for allitems
        $name = $first . " " . $last;

        // Step 1: Insert into allitems
        $insertAllitemsQuery = "INSERT INTO allitems (name) VALUES (?)";
        $stmtAllitems = $conn->prepare($insertAllitemsQuery);

        if ($stmtAllitems) {
            $stmtAllitems->bind_param("s", $name);
            if ($stmtAllitems->execute()) {
                // Retrieve the auto-incremented ID from allitems
                $newId = $conn->insert_id;
                $stmtAllitems->close();

                // Step 2: Insert into people
                $insertPeopleQuery = "INSERT INTO people (id, first, last, roles) VALUES (?, ?, ?, 'vendor')";
                $stmtPeople = $conn->prepare($insertPeopleQuery);

                if ($stmtPeople) {
                    $stmtPeople->bind_param("iss", $newId, $first, $last);
                    if ($stmtPeople->execute()) {
                        $stmtPeople->close();

                        // Step 3: Insert into vendors
                        $insertVendorsQuery = "INSERT INTO vendors (id, first, last, email, phone, business, description, affiliate) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmtVendors = $conn->prepare($insertVendorsQuery);

                        if ($stmtVendors) {
                            $stmtVendors->bind_param("isssssss", $newId, $first, $last, $email, $phone, $business, $description, $affiliate);
                            if ($stmtVendors->execute()) {
                                $successMessage = "Vendor successfully added!";
                            } else {
                                $errors[] = "Failed to save vendor information: " . $stmtVendors->error;
                            }
                            $stmtVendors->close();
                        } else {
                            $errors[] = "Database error: Could not prepare the insertion statement for vendors.";
                        }
                    } else {
                        $errors[] = "Failed to save $name to people table: " . $stmtPeople->error;
                    }
                } else {
                    $errors[] = "Database error: Could not prepare the insertion statement for people.";
                }
            } else {
                $errors[] = "Failed to save $name to allitems: " . $stmtAllitems->error;
            }
        } else {
            $errors[] = "Database error: Could not prepare the insertion statement for allitems.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vendor Submission — Mythos Events</title>
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
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 17px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column;
    overflow-x: hidden;
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
  .wrap { width: 100%; max-width: 480px; text-align: center; }
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(28px, 5vw, 38px);
    color: var(--white); margin-bottom: 20px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .status-card { background: var(--card); border-radius: 14px; padding: 32px 36px; margin-bottom: 28px; text-align: left; }
  .status-card.success { border: 1px solid rgba(82,200,122,0.4); text-align: center; }
  .status-card.error   { border: 1px solid rgba(220,80,80,0.4); }
  .status-card p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin: 0; }
  .status-card ul { padding-left: 20px; font-size: 14px; color: var(--red); }
  .status-card ul li { margin-bottom: 6px; }
  .btn-primary {
    display: inline-block; background: var(--purple); color: var(--white);
    padding: 14px 32px; border-radius: 8px; text-decoration: none;
    font-family: 'Cinzel', serif; font-size: 14px; letter-spacing: 0.15em;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary:hover { background: var(--purple-lt); transform: translateY(-2px); }
  footer { position: relative; z-index: 1; text-align: center; padding: 24px; border-top: 1px solid var(--purple-dim); font-size: 12px; color: rgba(196,168,232,0.35); }
  footer a { color: var(--muted); text-decoration: none; }
</style>
</head>
<body>

<div id="stars"></div>

<nav>
  <a href="/" class="nav-logo">Mythos<span>✦</span>Events</a>
  <a href="/" class="nav-back">← Back to Home</a>
</nav>

<main>
  <div class="wrap">
    <div class="status-icon"><?php echo !empty($errors) ? '✗' : '✦'; ?></div>
    <h1>Noelfest Vendor Submission</h1>

    <?php if (!empty($errors)): ?>
      <div class="status-card error">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
      <div class="status-card success">
        <p><?= htmlspecialchars($successMessage) ?></p>
      </div>
    <?php endif; ?>

    <a href="vendor.html" class="btn-primary">Back to Signup Form ✦</a>
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
