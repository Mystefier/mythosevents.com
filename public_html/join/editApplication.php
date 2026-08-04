<?php
// Retired — this endpoint used to trust a client-supplied email with no
// authentication check. Replaced by edit-profile.php, which requires a
// logged-in session.
header("Location: edit-profile.php");
exit();
?>
