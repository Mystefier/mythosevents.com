<?php
// Include the database connection
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Initialize variables for feedback
$successMessage = "";
$errors = [];

// Fetch all vendor data
$fetchQuery = "SELECT id, first, last FROM vendors";
$result = $conn->query($fetchQuery);

if ($result) {
    // Loop through each vendor
    while ($row = $result->fetch_assoc()) {
        $vendorId = $row['id'];
        $name = $row['first'] . " " . $row['last']; // Combine first and last name

        // Insert into `allitems` and get the auto-incremented ID
        $insertAllitemsQuery = "INSERT INTO allitems (name) VALUES (?)";
        $stmtAllitems = $conn->prepare($insertAllitemsQuery);

        if ($stmtAllitems) {
            $stmtAllitems->bind_param("s", $name);
            if ($stmtAllitems->execute()) {
                // Retrieve the auto-incremented ID
                $newId = $conn->insert_id;

                // Update the `vendors` table with the new ID
                $updateVendorsQuery = "UPDATE vendors SET id = ? WHERE id = ?";
                $stmtUpdateVendors = $conn->prepare($updateVendorsQuery);

                if ($stmtUpdateVendors) {
                    $stmtUpdateVendors->bind_param("ii", $newId, $vendorId);
                    if (!$stmtUpdateVendors->execute()) {
                        $errors[] = "Failed to update vendors ID for $name: " . $stmtUpdateVendors->error;
                    }
                    $stmtUpdateVendors->close();
                } else {
                    $errors[] = "Database error: Could not prepare the update statement for vendors.";
                }

                // Insert into `people` with the same ID and "vendor" role
                $insertPeopleQuery = "INSERT INTO people (id, first, last, roles) VALUES (?, ?, ?, 'vendor')";
                $stmtPeople = $conn->prepare($insertPeopleQuery);

                if ($stmtPeople) {
                    $stmtPeople->bind_param("iss", $newId, $row['first'], $row['last']);
                    if (!$stmtPeople->execute()) {
                        $errors[] = "Failed to add $name to people table: " . $stmtPeople->error;
                    }
                    $stmtPeople->close();
                } else {
                    $errors[] = "Database error: Could not prepare the insertion statement for people.";
                }
            } else {
                $errors[] = "Failed to insert $name into allitems: " . $stmtAllitems->error;
            }
            $stmtAllitems->close();
        } else {
            $errors[] = "Database error: Could not prepare the insertion statement for allitems.";
        }
    }
    $result->free(); // Free the result set
} else {
    $errors[] = "Database error: Could not fetch data from vendors: " . $conn->error;
}

// Feedback message
if (empty($errors)) {
    $successMessage = "Vendors successfully added to allitems, updated in vendors, and inserted into people.";
} else {
    $successMessage = "Some or all records could not be processed due to errors.";
}

// Output result
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Transfer Status — Mythos Events</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --midnight:   #0D0B1A;
    --card:       #201C32;
    --purple-dim: rgba(107,63,160,0.25);
    --lilac:      #C4A8E8;
    --white:      #FFFFFF;
    --green:      #52C87A;
    --red:        #E05555;
  }
  body {
    background: var(--midnight); color: var(--lilac);
    font-family: 'Inter', sans-serif; margin: 0; padding: 40px 20px;
  }
  .container {
    max-width: 600px; margin: 20px auto; padding: 32px;
    background: var(--card); border: 1px solid var(--purple-dim);
    border-radius: 14px; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  h1 { font-family: 'Cinzel', serif; font-size: 22px; color: var(--white); margin-bottom: 20px; }
  .success { color: var(--green); font-size: 14px; margin-bottom: 15px; }
  .error { color: var(--red); font-size: 14px; }
  .error ul { padding-left: 20px; }
</style>
</head>
<body>
    <div class="container">
        <h1>Data Transfer Status</h1>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="success"><?= htmlspecialchars($successMessage) ?></div>
    </div>
</body>
</html>
