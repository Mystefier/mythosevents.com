<?php
$statusType = 'error';
$status = 'Invalid request.';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Email Subject
        $subject = "Easter Egg Hunt - Get Ready to Start!";

        // HTML Email Message
        $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Easter Egg Hunt - Start Your Game</title></head>
        <body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
          <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
            <tr><td align='center'>
              <table width='500' cellpadding='0' cellspacing='0' style='max-width:500px;width:100%;'>
                <tr>
                  <td align='center' style='padding:32px 0 24px;'>
                    <p style='margin:0;font-family:Georgia,serif;font-size:11px;letter-spacing:6px;color:#9B6FD0;text-transform:uppercase;'>Easter Egg Hunt</p>
                    <h1 style='margin:16px 0 0;font-family:Georgia,serif;font-size:34px;font-weight:900;color:#FFFFFF;letter-spacing:1px;'>Mythos<span style='color:#E8C547;'>&#10022;</span>Events</h1>
                  </td>
                </tr>
                <tr>
                  <td style='background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:40px 40px 36px;'>
                    <h2 style='margin:0 0 16px;font-family:Georgia,serif;font-size:24px;font-weight:600;color:#FFFFFF;line-height:1.2;'>Welcome to the Easter Egg Hunt!</h2>
                    <p style='margin:0 0 24px;font-size:15px;color:#C4A8E8;line-height:1.7;'>Before you begin, enter your player name below and click the button when you're ready.</p>
                    <form action='https://Mythosevents.com/StartGame.php' method='post'>
                      <input type='hidden' name='email' value='" . htmlspecialchars($email) . "'>
                      <input type='text' name='player_name' placeholder='Enter Your Player Name' required
                        style='width:100%;box-sizing:border-box;padding:14px 16px;margin:0 0 16px;border:1px solid rgba(107,63,160,0.4);border-radius:8px;font-size:15px;background-color:#1A1628;color:#FFFFFF;'>
                      <table cellpadding='0' cellspacing='0' style='width:100%;'>
                        <tr>
                          <td align='center' style='background-color:#6B3FA0;border-radius:8px;'>
                            <button type='submit' style='display:inline-block;width:100%;padding:14px 20px;font-family:Georgia,serif;font-size:14px;font-weight:700;letter-spacing:2px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;background:transparent;border:none;cursor:pointer;'>Start Game</button>
                          </td>
                        </tr>
                      </table>
                    </form>
                  </td>
                </tr>
                <tr>
                  <td align='center' style='padding:28px 0 0;'>
                    <p style='margin:0;font-size:12px;color:rgba(196,168,232,0.4);letter-spacing:2px;text-transform:uppercase;'>Mythos Events &nbsp;&middot;&nbsp; Glendale, Arizona</p>
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>";

        // Email Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@mythosevents.com" . "\r\n";
        $headers .= "Reply-To: contact@mythosevents.com" . "\r\n";

        // Send Email
        if (mail($email, $subject, $message, $headers)) {
            $statusType = 'success';
            $status = "A confirmation email has been sent to: " . htmlspecialchars($email);
        } else {
            $statusType = 'error';
            $status = "Error: Email could not be sent.";
        }
    } else {
        $statusType = 'error';
        $status = "Invalid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Egg Hunt Signup — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --card:       #201C32;
    --purple-dim: rgba(107,63,160,0.25);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
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
  <h1><?php echo $statusType === 'success' ? 'Email Sent!' : 'Signup Failed'; ?></h1>
  <div class="status-card <?php echo $statusType; ?>">
    <p><?php echo $status; ?></p>
  </div>
</body>
</html>
