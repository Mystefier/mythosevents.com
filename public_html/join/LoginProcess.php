<?php
// Include code to open the database
$dbname = "db9dh4gg0yfw3q";
include('logintodatabase.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';
    // Validate and sanitize the email address
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    // Check if the email is valid
    if ($email) {
        // Retrieve user data from the database using prepared statement
        $selectSql = "SELECT * FROM people WHERE email = ?";
        
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $selectSql);

        // Bind the parameter
        mysqli_stmt_bind_param($stmt, "s", $email);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get the result
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password . $user['salt'], $user['password'])) {
              include("LoginSuccess.php");
        exit();
            } else {
                 include("LoginFailure.php");
        exit();
            }
        } else {
            echo "<p>User with email '$email' not found.</p>";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>Invalid email address. Please enter a valid email.</p>";
    }
} else {
    // Redirect to the main page if accessed directly without form submission
   header("Location: LoginFailure.php");
    exit();
}

// Close the database connection
mysqli_close($conn);
?>



