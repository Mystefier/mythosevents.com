<?php
// Shared "you're approved" email, sent from admin.php when someone is
// approved via the Approve button (and reused for one-off batch sends).

function send_approval_email($email, $firstName) {
    $safeFirstName = htmlspecialchars($firstName ?: 'there');
    $subject = "You're Approved — Welcome to Mythos Events!";
    $body = "
<html>
<head><meta charset='UTF-8'><meta name='color-scheme' content='light only'><meta name='supported-color-schemes' content='light only'></head>
<body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='max-width:560px;width:100%;background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:40px;'>
<tr><td>
<h1 style='margin:0 0 16px;font-family:Georgia,serif;font-size:26px;color:#FFFFFF;'>You're Approved, $safeFirstName!</h1>
<p style='margin:0 0 20px;font-size:15px;color:#C4A8E8;line-height:1.7;'>Great news \xe2\x80\x94 your application to Mythos Events has been reviewed and approved. You're officially part of the team!</p>
<p style='margin:0 0 24px;font-size:15px;color:#C4A8E8;line-height:1.7;'>One quick thing: if you haven't already, log in and update your profile so we have an accurate record of what you're interested in doing \xe2\x80\x94 performing, organizing, vending, helping with a venue, whatever fits. It only takes a minute and it's how we match you to the right opportunities.</p>
<table cellpadding='0' cellspacing='0' style='margin:0 auto;'>
<tr><td align='center' style='background-color:#6B3FA0;border-radius:8px;'>
<a href='https://mythosevents.com/join/login.php' style='display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;'>Log In &amp; Update Profile \xe2\x9c\xa6</a>
</td></tr>
</table>
<p style='margin:24px 0 0;font-size:13px;color:rgba(196,168,232,0.5);'>Questions in the meantime? Just reply to this email or reach us at <a href='mailto:wadehawkins@mythosevents.com' style='color:#9B6FD0;'>wadehawkins@mythosevents.com</a>.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";
    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: wadehawkins@mythosevents.com\r\n";
    return mail($email, $subject, $body, $headers);
}
