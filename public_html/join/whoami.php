<?php
// Lightweight session-check endpoint for the nav account widget.
// Called via fetch() from any page (static .html or .php) to find out
// who's logged in without needing server-side rendering on that page.
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['person_id'])) {
    echo json_encode(['loggedIn' => false]);
    exit();
}

$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

$personId = intval($_SESSION['person_id']);
$stmt = mysqli_prepare($conn, "SELECT first FROM people WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $personId);
mysqli_stmt_execute($stmt);
$person = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$person) {
    // Session points at a person that no longer exists — treat as logged out
    echo json_encode(['loggedIn' => false]);
    exit();
}

echo json_encode(['loggedIn' => true, 'name' => $person['first']]);
