<?php
// Open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Retrieve the email address from the link parameter
$email = isset($_GET["email"]) ? filter_var($_GET["email"], FILTER_SANITIZE_EMAIL) : '';

// Check if the email is valid
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Check if the email already exists in the "volunteers2" table
    $checkSql = "SELECT * FROM people WHERE email = '$email'";
    $result = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($result) > 0) {
        echo "<p>You have already confirmed your email address.</p>";
    } else {
       $recruiterId = $_GET['id'] ?? '1';
if ($recruiterId === '') {
    $recruiterId = '1';
}
        // Provide the form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Volunteer Form</title>

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

    label {
        display: block;
        margin-bottom: 8px;
        color: #1E90FF; /* Dodger Blue text color */
    }
    input[type="url"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #87CEEB; /* Sky Blue border color */
        border-radius: 4px;
        box-sizing: border-box;
    }

    .checkbox-group {
        color: #1E90FF; /* Dodger Blue text color */
    }

    textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #87CEEB; /* Sky Blue border color */
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        background-color: #4CAF50; /* Green button color */
        color: #ffffff; /* White button text color */
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
</style>
        </head>
        <body>
            <div class="container">
                <form action="submit.php" method="post" onsubmit="return validateForm()">
                    <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
                    <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
                    <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Phone Number"><br>
                    date of birth<br>
                     <input type="date" id="dob" name="dob" required><br>
        <input type="password" id="password" name="password" placeholder="Password" required><br>
        <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm Password" oninput="checkPasswordMatch()">
        <span id="passwordMatchMessage"></span>
                    <div class="checkbox-group">
                        <br><br>What roles are you interested? Choose all that apply:<br>
                        <input type="checkbox" id="Vendor" name="roles[]" value="Vendor">Vendor
                        <input type="checkbox" id="organizer" name="roles[]" value="Organizer">Organizer
                         <input type="checkbox" id="volunteer" name="roles[]" value="volunteer">General Help, set up, take down
                        <input type="checkbox" id="sales" name="roles[]" value="Sales">Sales
                        <input type="checkbox" id="performer" name="roles[]" value="Performer">Performer
                        <input type="checkbox" id="artist" name="roles[]" value="Artist">Artist
                        <input type="checkbox" id="operations" name="roles[]" value="Operations">Operations
                        <input type="checkbox" id="other" name="roles[]" value="Other">Other<br>
                        <input type="hidden" name="email" value="<?php echo $email; ?>">
                        <input type="hidden" name="recruiter" value="<?php echo $recruiterId; ?>">
                    </div>
                    <textarea id="message" name="message" rows="6" placeholder="Use this optional space to tell us anything you want to say about why you want to join our team, how you hope to be involved or what you want to get out of it.  This section is not required"></textarea><br>


                    <br><textarea id="description" name="description" rows="6" placeholder="Describe yourself in a third person perspective promoting the talents and/or skills you will bring to our events.  For example - 'He/She is an amazing dancer/singer/artist'."></textarea><br>
                    <br><input type="url" id="website" name="website" placeholder="http://Your Website url - Optional">

                    <br><br><button type="submit" id="submitButton" disabled>Submit</button>
                </form>
            </div>

            <script>
    function checkPasswordMatch() {
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirmPassword").value;
        var message = document.getElementById("passwordMatchMessage");

        if (password == confirmPassword) {
            message.innerHTML = "Passwords match!";
            document.getElementById("submitButton").disabled = false;
        } else {
            message.innerHTML = "Passwords do not match!";
            document.getElementById("submitButton").disabled = true;
        }
    }

    function validateForm() {
        // You can add additional validation here if needed
        return true;
    }
</script>

        </body>
        </html>
        <?php
    }
} else {
    echo "<p>Invalid email address. Please enter a valid email.</p>";
}

// Close the database connection
mysqli_close($conn);
?>