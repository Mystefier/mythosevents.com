<?php
session_start();

// Include code to open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Function to generate a random salt
function generateSalt() {
    return bin2hex(random_bytes(16));
}

$statusType = '';
$statusMessage = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $firstName = isset($_POST["firstName"]) ? $_POST["firstName"] : '';
    $lastName = isset($_POST["lastName"]) ? $_POST["lastName"] : '';
    $phoneNumber = isset($_POST["phoneNumber"]) ? $_POST["phoneNumber"] : '';
    $roles = isset($_POST["roles"]) ? implode(", ", $_POST["roles"]) : '';
    $description = isset($_POST["description"]) ? $_POST["description"] : '';
    $website = isset($_POST["website"]) ? $_POST["website"] : '';
    $recruiter = isset($_POST["recruiter"]) ? $_POST["recruiter"] : '';

    // Additional fields for password and DOB
    $dob = isset($_POST["dob"]) && $_POST["dob"] !== '' ? $_POST["dob"] : null;
    $password = isset($_POST["password"]) ? $_POST["password"] : '';
    $salt = generateSalt();
    $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

    // Message field
    $message = isset($_POST["message"]) ? $_POST["message"] : '';

    // Service area fields — no address means no restriction (worldwide),
    // so we don't invent a radius with nothing to center it on
    $serviceAreaAddress = isset($_POST["serviceAreaAddress"]) ? trim($_POST["serviceAreaAddress"]) : '';
    $serviceAreaLatitude = ($serviceAreaAddress !== '' && isset($_POST["serviceAreaLatitude"]) && $_POST["serviceAreaLatitude"] !== '') ? floatval($_POST["serviceAreaLatitude"]) : null;
    $serviceAreaLongitude = ($serviceAreaAddress !== '' && isset($_POST["serviceAreaLongitude"]) && $_POST["serviceAreaLongitude"] !== '') ? floatval($_POST["serviceAreaLongitude"]) : null;
    $serviceAreaRadius = $serviceAreaAddress !== '' ? (isset($_POST["serviceAreaRadius"]) ? intval($_POST["serviceAreaRadius"]) : 30) : null;

    // Involvement type — derived from the selected roles so we don't ask twice
    $roleToInvolvementType = [
        'Vendor' => 'Vendor',
        'Organizer' => 'Organizer',
        'volunteer' => 'Talent',
        'Sales' => 'Affiliate',
        'Performer' => 'Talent',
        'Artist' => 'Talent',
        'Operations' => 'Talent',
        'Venue Manager/Owner' => 'Venue',
        'Other' => 'Talent',
    ];
    $selectedRoles = isset($_POST["roles"]) ? $_POST["roles"] : [];
    $involvementTypes = [];
    foreach ($selectedRoles as $role) {
        if (isset($roleToInvolvementType[$role]) && !in_array($roleToInvolvementType[$role], $involvementTypes)) {
            $involvementTypes[] = $roleToInvolvementType[$role];
        }
    }
    $involvementType = $involvementTypes ? implode(", ", $involvementTypes) : 'Talent';

    // Validate and sanitize the email address
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // Check if the email is valid
    if ($email) {
        // Check for an existing passwordless row (e.g. a subscriber-only stub) —
        // upgrade it in place instead of creating a duplicate row for the same email
        $checkStmt = mysqli_prepare($conn, "SELECT id, password FROM people WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $existingResult = mysqli_stmt_get_result($checkStmt);
        $existingRow = mysqli_fetch_assoc($existingResult);
        mysqli_stmt_close($checkStmt);

        if ($existingRow && empty($existingRow['password'])) {
            $recentlyAddedId = intval($existingRow['id']);
            $updateSql = "UPDATE people SET first = ?, last = ?, phone = ?, roles = ?, description = ?, website = ?, recruiter = ?, dob = ?, password = ?, salt = ?, message = ?, service_area_address = ?, service_area_latitude = ?, service_area_longitude = ?, service_area_radius_miles = ?, involvement_type = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssssddisi",
                $firstName, $lastName, $phoneNumber, $roles, $description, $website, $recruiter,
                $dob, $hashedPassword, $salt, $message, $serviceAreaAddress,
                $serviceAreaLatitude, $serviceAreaLongitude, $serviceAreaRadius, $involvementType, $recentlyAddedId
            );
        } else {
            // Insert data into the "people" table
            $insertSql = "INSERT INTO people (email, first, last, phone, roles, description, website, recruiter, dob, password, salt, message, service_area_address, service_area_latitude, service_area_longitude, service_area_radius_miles, involvement_type)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertSql);
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssddis",
                $email, $firstName, $lastName, $phoneNumber, $roles, $description, $website, $recruiter,
                $dob, $hashedPassword, $salt, $message, $serviceAreaAddress,
                $serviceAreaLatitude, $serviceAreaLongitude, $serviceAreaRadius, $involvementType
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            // Retrieve the ID of the record (existing id if upgraded, new id if inserted)
            if (!isset($recentlyAddedId)) {
                $recentlyAddedId = mysqli_insert_id($conn);
            }
            mysqli_stmt_close($stmt);

            // Create the link with the ID variable
            $shareLink = "https://mythosevents.com/join/?id=" . $recentlyAddedId;
            $cardLink = "https://mythosevents.com/card/?id=" . $recentlyAddedId;

            // Send email to the submitted address
            $subject = "You're In! Welcome to the Mythos Events Team";
            $safeFirstName = htmlspecialchars($firstName ?: 'there');
            $messageBody = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light only">
<title>Welcome to Mythos Events</title></head>
<body style="margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0D0B1A;padding:40px 20px;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;">

        <!-- Header -->
        <tr>
          <td align="center" style="padding:40px 0 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#9B6FD0;text-transform:uppercase;">Welcome to the Team</p>
            <h1 style="margin:16px 0 0;font-family:Georgia,serif;font-size:42px;font-weight:900;color:#FFFFFF;letter-spacing:2px;text-shadow:none;">Mythos<span style="color:#E8C547;">✦</span>Events</h1>
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;">

            <h2 style="margin:0 0 16px;font-family:Georgia,serif;font-size:26px;font-weight:600;color:#FFFFFF;line-height:1.2;">Application Submitted, ' . $safeFirstName . '!</h2>
            <p style="margin:0 0 24px;font-size:16px;color:#C4A8E8;line-height:1.7;">Thank you for applying to join Mythos Events. Your information has been successfully submitted — we will reach out when there is an event or opportunity that is a good fit for you.</p>

            <!-- Divider -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>

            <p style="margin:0 0 16px;font-size:16px;color:#C4A8E8;line-height:1.7;">We need lots more people. Share your personal link below — if a friend joins or subscribes through it, you will be rewarded, and it will always be credited to you:</p>

            <table cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
              <tr>
                <td align="center" style="background-color:#6B3FA0;border-radius:8px;">
                  <a href="' . $shareLink . '" style="display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;">Your Referral Link ✦</a>
                </td>
              </tr>
            </table>
            <p style="margin:-16px 0 24px;font-size:13px;word-break:break-all;">
              <a href="' . $shareLink . '" style="color:#9B6FD0;text-decoration:underline;">' . $shareLink . '</a>
            </p>

            <!-- Divider -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr><td style="border-top:1px solid rgba(107,63,160,0.25);"></td></tr>
            </table>

            <p style="margin:0 0 16px;font-size:16px;color:#C4A8E8;line-height:1.7;">We also made you a digital business card you can print or save as a PDF — handy for sharing your referral link in person:</p>
            <p style="margin:0;font-size:13px;word-break:break-all;">
              <a href="' . $cardLink . '" style="color:#9B6FD0;text-decoration:underline;">' . $cardLink . '</a>
            </p>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td align="center" style="padding:32px 0 0;">
            <p style="margin:0 0 8px;font-size:12px;color:rgba(196,168,232,0.4);letter-spacing:2px;text-transform:uppercase;">Mythos Events &nbsp;·&nbsp; Glendale, Arizona</p>
            <p style="margin:0;font-size:12px;color:rgba(196,168,232,0.3);">Problems? Email <a href="mailto:wadehawkins@mythosevents.com" style="color:rgba(196,168,232,0.5);">wadehawkins@mythosevents.com</a></p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: wadehawkins@mythosevents.com" . "\r\n"; // Replace with your email address

            mail($email, $subject, $messageBody, $headers);

            mysqli_close($conn);

            // Auto-login: they just proved they own this account by setting its
            // password moments ago, so send them straight to their dashboard
            // instead of making them log in again right away.
            session_regenerate_id(true);
            $_SESSION['person_id'] = $recentlyAddedId;
            header("Location: dashboard.php?welcome=1");
            exit();
        } else {
            mysqli_stmt_close($stmt);
            $statusType = 'error';
            $statusMessage = 'Oops! Something went wrong while adding your information. Please try again later.';
        }
    } else {
        $statusType = 'error';
        $statusMessage = 'Invalid email address. Please enter a valid email.';
    }

    mysqli_close($conn);
} else {
    // Redirect to the main page if accessed directly without form submission
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submission Error — Mythos Events</title>
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
    --green:      #52C87A;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.75;
    min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 40px 20px; overflow-x: hidden; position: relative;
  }
  #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: #fff; animation: twinkle var(--dur) ease-in-out infinite var(--delay); }
  @keyframes twinkle { 0%,100% { opacity: 0.1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(1.5); } }
  .content { position: relative; z-index: 1; width: 100%; max-width: 560px; text-align: center; }
  .status-icon { font-size: 52px; margin-bottom: 20px; }
  h1 {
    font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(26px, 5vw, 36px);
    color: var(--white); margin-bottom: 12px;
    text-shadow: 0 0 40px rgba(107,63,160,0.7);
  }
  .container {
    background: var(--card); border: 1px solid var(--purple-dim); border-radius: 14px;
    padding: 32px 36px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); text-align: left;
  }
  p { font-size: 15px; color: var(--lilac); line-height: 1.7; margin-bottom: 16px; }
  p:last-child { margin-bottom: 0; }
  a { color: var(--purple-lt); text-decoration: none; }
  a:hover { text-decoration: underline; }
  .error-container { border: 1px solid rgba(220,80,80,0.4); }
</style>
</head>
<body>

<div id="stars"></div>

<div class="content">
  <div class="status-icon">✗</div>
  <h1><?php echo $statusMessage && strpos($statusMessage, 'Invalid email') === 0 ? 'Invalid Email' : 'Submission Failed'; ?></h1>
  <div class="container error-container">
    <p><?php echo htmlspecialchars($statusMessage); ?></p>
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
</script>
</body>
</html>
