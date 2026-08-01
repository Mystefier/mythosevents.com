<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
<?php
// Include code to open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $id = $_POST["id"];
    echo $id . "<br>";

    // Validate and sanitize the email address
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Check if the email is valid
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the email already exists in the "volunteers" table
        $checkSql = "SELECT * FROM people WHERE email = '$email'";
        $result = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($result) > 0) {
            echo "<h2 style='color: #009688; text-align: center;'>The email address '$email' is already in the system.<br> Please use a different email address or try logging in below.</h2><br><br>"; /* Teal color */
            include("login.php");
            
        } else {
            // Subject for the confirmation email
            $subject = "Confirmation Email for Events Team";

            // Message for the confirmation email with enhanced inline styles
            $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 0;
                        padding: 20px;
                        background-color: #f0f7f4; /* Light green background */
                    }

                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #ffffff; /* White container background */
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }

                    h1 {
                        color: #009688; /* Teal heading color */
                    }

                    p {
                        color: #555555; /* Dark gray text color */
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Confirmation Email</h1>
                    <p>Thank you for applying to our events team! You're almost done. Please click on the following link to confirm your email address. After clicking the link, we have a short form for you to fill out.</p>
                    <p><a href='http://mythosevents.com/join/addapplicant.php?email=" . urlencode($email) . "&id=" . urlencode($id) . "' style='color: #009688; text-decoration: none;'>Confirm Your Email</a></p>
                </div>
            </body>
            </html>
            ";

            // Additional headers
            $headers = "From: Confrim@MythosEvents.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            // Send the confirmation email
            if (mail($email, $subject, $message, $headers)) {
                echo "<p style='color: #009688; text-align: center;'>Thank you for applying to our events team! You're almost there.<br> A confirmation email has been sent to $email.<br> Please check your inbox and click on the confirmation link and then you'll be taken to a short form to fill out.</p><p>If you don't recieve an email in the next ten minutes send an email to Mystefier@gmail.com letting us know about the problem."; /* Teal color */
            } else {
                echo "<p style='color: #009688; text-align: center;'>Oops! Something went wrong while sending the confirmation email. Please try again later.</p>"; /* Teal color */
                include("index.php");
            }
        }
    } else {
        echo "<p style='color: #009688; text-align: center;'>Invalid email address. Please enter a valid email.</p>"; /* Teal color */
        include("index.php");
    }
} else {
    // Redirect to the main page if accessed directly without form submission
    //header("Location: index.php");
    //exit();
}

// Close the database connection
mysqli_close($conn);
?>
</div>