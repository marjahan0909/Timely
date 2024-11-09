<?php
session_start();
require_once "database.php"; // Assuming database.php handles DB connection with PDO

// Check if user is already logged in
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit();
}

// Handle the login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $error_message = '';

    // Validate that all fields are filled out
    if (empty($email) || empty($username) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        // Prepared statement to check for email and username match
        $sql = "SELECT * FROM users WHERE email = :email AND username = :username";
        $stmt = $pdo->prepare($sql);

        // Bind parameters and execute
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password if user exists
        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user"] = "yes";
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid email, username, or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Login - Timely</title>
</head>
<body>
    <div class="container">
        <h2 class="text-center">TIMELY</h2>
        <form action="login.php" method="POST">
            <!-- Email Field -->
            <div class="form-group mb-3">
                <input type="email" name="email" placeholder="Enter Email" class="form-control" required>
            </div>
            
            <!-- Username Field -->
            <div class="form-group mb-3">
                <input type="text" name="username" placeholder="Enter Username" class="form-control" required>
            </div>
            
            <!-- Password Field -->
            <div class="form-group mb-3">
                <input type="password" name="password" placeholder="Enter Password" class="form-control" required>
            </div>
            
            <!-- Error Message Display -->
            <?php if (!empty($error_message)) : ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Submit Button -->
            <div class="form-group text-center">
                <input type="submit" value="Login" class="btn btn-primary btn-sm">
            </div>
        </form>

        <!-- Register and Forgot Password Links -->
        <div class="text-center mt-3">
            <p>Not registered yet? <a href="timely-registration.php">Register Here</a></p>
            <p>Forgot password? <a href="#">Reset Here</a></p>
        </div>
    </div>
</body>
</html>
