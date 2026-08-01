<?php
// Database connection parameters
$dbname = "db9dh4gg0yfw3q"; // Your database name
 
// Include the file to connect to the database
include('logintodatabase.php');

// Fetching data from the database
$query = "SELECT email, first, last FROM people";
$result = mysqli_query($conn, $query);

// Email content
$emailSubject = "Welcome to Mythos Events Team";
$emailBody = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mythos Events Team Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2, p {
            margin: 0 0 20px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to Mythos Events Team</h1>

    <h2>Dear [First Name] [Last Name],</h2>
    <p>Thank you for your interest in joining the Mythos Events team. We\'re thrilled to have you on board!</p>
    
    <h2>You can now update your entries</h2>
    <p>We wanted to inform you that you now have the ability to edit the information you provided in your application form. Simply log in to your account at <a href="http://mythosevents.com/join/login.php">Mythos Events Login Page</a> and select the option to edit your profile.</p>
    
    <h2>Lunch Meeting At El Encantor in Cave Creek on Sunday</h2>
    <p>In addition, we would like to invite you to a lunch meeting scheduled for Sunday, March 24th, at 1:00 PM. The meeting will take place at El Encantor, located in Cave Creek. This gathering presents an opportunity for us to discuss our plans, vision, and strategy for the events team in more detail. Your input and ideas are invaluable as we work towards creating unforgettable experiences for our audience.</p>
    
    <h2>Phoenix Puppetry Expo</h2>
    <p>Following the lunch meeting, we will be attending the Phoenix Puppetry Expo, an event that promises to be both inspiring and informative. This expo will be held from 11:00 AM to 4:00 PM at 302 W. Latham in Phoenix. It\'s a chance for us to seek inspiration and potentially forge new connections within the industry. Notably, we already have a connection with the president of the Phoenix Guild of Puppetry, which may prove to be advantageous.</p>
    
    <h2>We Look Forward to Seeing You</h2>
    <p>We look forward to seeing you at the lunch meeting and exploring the Phoenix Puppetry Expo together.</p>
    
    <p>If you have any questions or concerns, please don\'t hesitate to reach out to us.</p>
    
    <p>Best regards,</p>
    <p>Wade Hawkins</p>
    <!-- Delete [Your Position] -->
    <p>Mythos Events</p>
</div>

</body>
</html>
';

// Loop through the result set
while ($row = mysqli_fetch_assoc($result)) {
    $to = $row['email'];
    $subject = $emailSubject;
    $message = $emailBody;
    // Replace placeholders with actual values
    $message = str_replace("[First Name]", $row['first'], $message);
    $message = str_replace("[Last Name]", $row['last'], $message);
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Mythos Events <your_email@example.com>" . "\r\n"; // Update with your email address
    // Send email
    mail($to, $subject, $message, $headers);
}

// Close the database connection
mysqli_close($conn);
?>
