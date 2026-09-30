<?php
// Handles the "didn't get the confirmation email" note box on email.php.
// Saves the note onto the person's already-captured record (from email.php's
// immediate capture-on-submit) and emails Wade directly so he sees it right away.

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');
require_once(__DIR__ . '/spamcheck.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !is_spam_submission($_POST)) {
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) : false;
    $noteMessage = isset($_POST["noteMessage"]) ? trim($_POST["noteMessage"]) : '';

    if ($email) {
        $tag = "[Did not get confirmation email]" . ($noteMessage !== '' ? " $noteMessage" : '');

        $checkStmt = mysqli_prepare($conn, "SELECT id, message FROM people WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $existing = mysqli_stmt_get_result($checkStmt)->fetch_assoc();
        mysqli_stmt_close($checkStmt);

        if ($existing) {
            $newMessage = trim(($existing['message'] ? $existing['message'] . "\n" : '') . $tag);
            $updStmt = mysqli_prepare($conn, "UPDATE people SET message = ? WHERE id = ?");
            mysqli_stmt_bind_param($updStmt, "si", $newMessage, $existing['id']);
            mysqli_stmt_execute($updStmt);
            mysqli_stmt_close($updStmt);
        } else {
            // Shouldn't normally happen (email.php captures the row first), but
            // don't lose the note if for some reason there's no row yet.
            $insStmt = mysqli_prepare($conn, "INSERT INTO people (email, involvement_type, message) VALUES (?, 'Started Joining', ?)");
            mysqli_stmt_bind_param($insStmt, "ss", $email, $tag);
            mysqli_stmt_execute($insStmt);
            mysqli_stmt_close($insStmt);
        }

        $teamSubject = "Someone did not get their Mythos confirmation email";
        $safeEmailForBody = $email;
        $teamBody = "Email: $safeEmailForBody\n\nMessage:\n" . ($noteMessage !== '' ? $noteMessage : "(no message, just flagged the missing email)");
        $teamHeaders = "From: wadehawkins@mythosevents.com\r\n";
        mail("wadehawkins@mythosevents.com", $teamSubject, $teamBody, $teamHeaders);
    }
}

mysqli_close($conn);
header("Location: email.php?noted=1");
exit();
