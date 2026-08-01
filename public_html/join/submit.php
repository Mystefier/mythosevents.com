<?php
// Include code to open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Submitted — Mythos Events</title>
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
  .success-container { border: 1px solid rgba(82,200,122,0.4); }
  .error-container { border: 1px solid rgba(220,80,80,0.4); }
</style>
</head>
<body>

<div id="stars"></div>

<div class="content">

<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $firstName = isset($_POST["firstName"]) ? mysqli_real_escape_string($conn, $_POST["firstName"]) : '';
    $lastName = isset($_POST["lastName"]) ? mysqli_real_escape_string($conn, $_POST["lastName"]) : '';
    $phoneNumber = isset($_POST["phoneNumber"]) ? mysqli_real_escape_string($conn, $_POST["phoneNumber"]) : '';
    $roles = isset($_POST["roles"]) ? implode(", ", $_POST["roles"]) : '';
    $description = isset($_POST["description"]) ? mysqli_real_escape_string($conn, $_POST["description"]) : '';
    $website = isset($_POST["website"]) ? mysqli_real_escape_string($conn, $_POST["website"]) : '';
    $recruiter = isset($_POST["recruiter"]) ? $_POST["recruiter"] : '';

    // Additional fields for password and DOB
    $dob = isset($_POST["dob"]) ? $_POST["dob"] : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';
    $salt = generateSalt();
    $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

    // Message field
    $message = isset($_POST["message"]) ? mysqli_real_escape_string($conn, $_POST["message"]) : '';

    // Service area fields
    $serviceAreaAddress = isset($_POST["serviceAreaAddress"]) ? mysqli_real_escape_string($conn, $_POST["serviceAreaAddress"]) : '';
    $serviceAreaLatitude = isset($_POST["serviceAreaLatitude"]) ? floatval($_POST["serviceAreaLatitude"]) : null;
    $serviceAreaLongitude = isset($_POST["serviceAreaLongitude"]) ? floatval($_POST["serviceAreaLongitude"]) : null;
    $serviceAreaRadius = isset($_POST["serviceAreaRadius"]) ? intval($_POST["serviceAreaRadius"]) : 30;

    // Involvement type
    $involvementType = isset($_POST["involvementType"]) ? mysqli_real_escape_string($conn, $_POST["involvementType"]) : 'Talent';

    // Validate and sanitize the email address
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // Check if the email is valid
    if ($email) {
        // Insert data into the "people" table
        $insertSql = "INSERT INTO people (email, first, last, phone, roles, description, website, recruiter, dob, password, salt, message, service_area_address, service_area_latitude, service_area_longitude, service_area_radius_miles, involvement_type)
                      VALUES ('$email', '$firstName', '$lastName', '$phoneNumber', '$roles', '$description', '$website', '$recruiter', '$dob', '$hashedPassword', '$salt', '$message', '$serviceAreaAddress', $serviceAreaLatitude, $serviceAreaLongitude, $serviceAreaRadius, '$involvementType')";

        if (mysqli_query($conn, $insertSql)) {
            // Retrieve the ID of the recently added record
            $recentlyAddedId = mysqli_insert_id($conn);

            // Create the link with the ID variable
            $shareLink = "https://mythosevents.com/join/?id=" . $recentlyAddedId;

            // Send email to the submitted address
            $subject = "Confirmation of Submission";
            $messageBody = "
                <html>
                <head>
                    <style>
                        body {
                            font-family: 'Arial', sans-serif;
                            margin: 20px;
                            padding: 20px;
                            background-color: #f5f5f5;
                        }
                        .container {
                            max-width: 600px;
                            margin: 0 auto;
                            background-color: #fff;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        p {
                            color: #333;
                            margin-bottom: 15px;
                        }
                        a {
                            color: #4CAF50; /* Green link color */
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <p>Dear $firstName,</p>
                        <p>Thank you for applying to our events team! Your information has been successfully submitted. We will reach out to you when we have an event that is a good fit for you.</p>
                        <p>Please share the following link with your friends to invite them to apply and help build our team. If your friends use this link to apply and participate, you will be rewarded:</p>
                        <p><a href='$shareLink'>$shareLink</a></p>
                        <p>Best regards,<br>Event Team</p>
                    </div>
                </body>
                </html>
            ";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Events@Mythos.com" . "\r\n"; // Replace with your email address

            mail($email, $subject, $messageBody, $headers);

            echo "<div class='status-icon'>✦</div>";
            echo "<h1>Application Submitted</h1>";
            echo "<div class='container success-container'>";
            echo "<p>Your information has been successfully submitted. Thank you for applying to our events team!</p>";
            echo "<p>We need lots more people. Please help us spread the word. Share this link with your friends and we will know if anyone joins from it. We reward people for helping us find talent:</p>";
            echo "<p><a href=\"" . htmlspecialchars($shareLink) . "\">" . htmlspecialchars($shareLink) . "</a></p>";
            echo "</div>";
        } else {
            echo "<div class='status-icon'>✗</div>";
            echo "<h1>Submission Failed</h1>";
            echo "<div class='container error-container'>";
            echo "<p>Oops! Something went wrong while adding your information. Please try again later.</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='status-icon'>✗</div>";
        echo "<h1>Invalid Email</h1>";
        echo "<div class='container error-container'>";
        echo "<p>Invalid email address. Please enter a valid email.</p>";
        echo "</div>";
    }
} else {
    // Redirect to the main page if accessed directly without form submission
    header("Location: index.php");
    exit();
}

// Close the database connection
mysqli_close($conn);

// Function to generate a random salt
function generateSalt() {
    return bin2hex(random_bytes(16));
}
?>

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
