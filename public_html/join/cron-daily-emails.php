<?php
// Runs once a day via a SiteGround cron job hitting this URL with the correct
// token. Not linked from anywhere on the site — protected by a shared secret
// so random web traffic can't trigger it.
//
// 1. Subscriber invite — 7 days after signing up as Subscriber-only, invite
//    them to join as talent/vendor/venue/etc if that fits.
// 2. Venue reminder — 7 days after signup, anyone who picked "Venue
//    Manager/Owner" as a role but never added a Venue asset gets a nudge.
//    (The dashboard already nudges them the moment they log in — this is
//    just the fallback for people who haven't logged back in.)

$CRON_SECRET = 'xNSVe0OljIJW-ncWaLxap9WxAQ4trpZa';

if (!isset($_GET['token']) || !hash_equals($CRON_SECRET, $_GET['token'])) {
    http_response_code(403);
    echo "Forbidden";
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$results = ['subscriber_invites_sent' => 0, 'venue_reminders_sent' => 0];

// ── 1. Subscriber invites ──
$subStmt = mysqli_prepare($conn, "SELECT id, email, first FROM people WHERE involvement_type = 'Subscriber' AND subscriber_invite_sent_at IS NULL AND date <= (NOW() - INTERVAL 7 DAY)");
mysqli_stmt_execute($subStmt);
$subResult = mysqli_stmt_get_result($subStmt);
$subscribers = [];
while ($row = mysqli_fetch_assoc($subResult)) {
    $subscribers[] = $row;
}
mysqli_stmt_close($subStmt);

foreach ($subscribers as $sub) {
    $safeFirstName = htmlspecialchars($sub['first'] ?: 'there');
    $joinLink = "https://mythosevents.com/join/?id=" . $sub['id'];

    $subject = "Want to Do More Than Just Watch?";
    $body = "
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='max-width:560px;width:100%;background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;'>
<tr><td>
<h1 style='margin:0 0 16px;font-family:Georgia,serif;font-size:26px;color:#FFFFFF;'>Hey $safeFirstName,</h1>
<p style='margin:0 0 20px;font-size:15px;color:#C4A8E8;line-height:1.7;'>You've been getting our updates for about a week now — thanks for sticking around. We wanted to ask: is there more you'd want to do with Mythos Events than just watch from the sidelines?</p>
<p style='margin:0 0 16px;font-size:15px;color:#C4A8E8;line-height:1.7;'>If you have a performance, an art piece, a business that could use vendor space, a venue, or anything else you'd want to bring to an event, we'd love to have you on the team:</p>
<ul style='margin:0 0 24px;padding-left:20px;font-size:14px;color:#C4A8E8;line-height:1.9;'>
<li style='color:#C4A8E8;'>Perform, or share your art</li>
<li style='color:#C4A8E8;'>Set up a vendor booth</li>
<li style='color:#C4A8E8;'>Bring your venue into the network</li>
<li style='color:#C4A8E8;'>Help organize or spread the word as an affiliate</li>
</ul>
<table cellpadding='0' cellspacing='0' style='margin:0 auto;'>
<tr><td align='center' style='background-color:#6B3FA0;border-radius:8px;'>
<a href='$joinLink' style='display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;'>Join the Team ✦</a>
</td></tr>
</table>
<p style='margin:24px 0 0;font-size:13px;color:rgba(196,168,232,0.5);'>If you're happy just getting updates, no action needed — you'll keep hearing from us either way.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";

    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Events@Mythos.com\r\n";
    if (mail($sub['email'], $subject, $body, $headers)) {
        $markStmt = mysqli_prepare($conn, "UPDATE people SET subscriber_invite_sent_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($markStmt, "i", $sub['id']);
        mysqli_stmt_execute($markStmt);
        mysqli_stmt_close($markStmt);
        $results['subscriber_invites_sent']++;
    }
}

// ── 2. Venue reminders ──
$venueStmt = mysqli_prepare($conn, "
    SELECT p.id, p.email, p.first FROM people p
    WHERE p.roles LIKE '%Venue Manager/Owner%'
      AND p.venue_reminder_sent_at IS NULL
      AND p.date <= (NOW() - INTERVAL 7 DAY)
      AND NOT EXISTS (SELECT 1 FROM assets a WHERE a.owner_person_id = p.id AND a.type = 'Venue')
");
mysqli_stmt_execute($venueStmt);
$venueResult = mysqli_stmt_get_result($venueStmt);
$venueFolks = [];
while ($row = mysqli_fetch_assoc($venueResult)) {
    $venueFolks[] = $row;
}
mysqli_stmt_close($venueStmt);

foreach ($venueFolks as $person) {
    $safeFirstName = htmlspecialchars($person['first'] ?: 'there');
    $addAssetLink = "https://mythosevents.com/join/add-asset.php";
    $loginLink = "https://mythosevents.com/join/login.php";

    $subject = "Add Your Venue to Mythos Events";
    $body = "
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background-color:#0D0B1A;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0D0B1A;padding:40px 20px;'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='max-width:560px;width:100%;background-color:#201C32;border:1px solid rgba(107,63,160,0.3);border-radius:14px;padding:48px 48px 40px;'>
<tr><td>
<h1 style='margin:0 0 16px;font-family:Georgia,serif;font-size:26px;color:#FFFFFF;'>Hey $safeFirstName,</h1>
<p style='margin:0 0 20px;font-size:15px;color:#C4A8E8;line-height:1.7;'>When you joined, you mentioned you manage or own a venue — but we don't have its details yet. Adding it takes about a minute and helps us match it with the right talent and events.</p>
<table cellpadding='0' cellspacing='0' style='margin:0 auto 16px;'>
<tr><td align='center' style='background-color:#6B3FA0;border-radius:8px;'>
<a href='$loginLink' style='display:inline-block;padding:16px 40px;font-family:Georgia,serif;font-size:15px;font-weight:700;letter-spacing:3px;color:#FFFFFF;text-decoration:none;text-transform:uppercase;'>Log In &amp; Add Venue ✦</a>
</td></tr>
</table>
<p style='margin:0;font-size:13px;color:rgba(196,168,232,0.5);'>Log in, then look for \"Add Asset\" on your dashboard.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>";

    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Events@Mythos.com\r\n";
    if (mail($person['email'], $subject, $body, $headers)) {
        $markStmt = mysqli_prepare($conn, "UPDATE people SET venue_reminder_sent_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($markStmt, "i", $person['id']);
        mysqli_stmt_execute($markStmt);
        mysqli_stmt_close($markStmt);
        $results['venue_reminders_sent']++;
    }
}

mysqli_close($conn);

header('Content-Type: text/plain');
echo "Subscriber invites sent: " . $results['subscriber_invites_sent'] . "\n";
echo "Venue reminders sent: " . $results['venue_reminders_sent'] . "\n";
?>
