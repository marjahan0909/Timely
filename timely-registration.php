<?php
session_start();
require_once "database.php"; // Assuming database.php handles DB connection

// Ensure the user isn't logged in already
if (isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// Get the role from the URL (Vice Chancellor or Visitor)
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Validate that the role is either 'vice_chancellor' or 'visitor'
$valid_roles = ['vice_chancellor', 'visitor'];
if (!in_array($role, $valid_roles)) {
    // Redirect if the role is invalid
    header('Location: index.php');
    exit();
}

// Handle the form submission
if (isset($_POST["submit"])) {
    // Validate and sanitize form data
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $username = htmlspecialchars(trim($_POST['username']));
    $phone = htmlspecialchars(trim($_POST['number']));
    $uvName = htmlspecialchars(trim($_POST['UVName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $repeatPassword = $_POST['repeat_password'];

    // Check if the passwords match
    if ($password !== $repeatPassword) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if the email already exists
        $sqlCheckEmail = "SELECT * FROM users WHERE email = :email";
        $stmtCheckEmail = $pdo->prepare($sqlCheckEmail);
        $stmtCheckEmail->bindParam(':email', $email);
        $stmtCheckEmail->execute();

        if ($stmtCheckEmail->rowCount() > 0) {
            $error_message = "This email is already registered. Please use a different email.";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Handle file uploads
            $uploadsDir = 'uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true); // Create the directory if it doesn't exist
            }

            $nidCard = $_FILES['nidCard']['name'];
            $profilePhoto = $_FILES['profilePhoto']['name'];

            $nidCardPath = $uploadsDir . '/' . basename($nidCard);
            $profilePhotoPath = $uploadsDir . '/' . basename($profilePhoto);

            // Check for successful file uploads and handle errors
            $nidUploadSuccess = move_uploaded_file($_FILES['nidCard']['tmp_name'], $nidCardPath);
            $photoUploadSuccess = move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $profilePhotoPath);

            if ($nidUploadSuccess && $photoUploadSuccess) {
                // Prepare SQL query to insert the data into the database
                $sql = "INSERT INTO users (first_name, last_name, username, phone, uv_name, email, password, role, nid_card, profile_photo) 
                        VALUES (:firstName, :lastName, :username, :phone, :uvName, :email, :password, :role, :nidCard, :profilePhoto)";

                $stmt = $pdo->prepare($sql);

                // Bind parameters to the SQL query
                $stmt->bindParam(':firstName', $firstName);
                $stmt->bindParam(':lastName', $lastName);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':uvName', $uvName);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':nidCard', $nidCardPath);
                $stmt->bindParam(':profilePhoto', $profilePhotoPath);

                // Execute the query and check for successful insertion
                if ($stmt->execute()) {
                    $success_message = "Registration successful! You can now log in.";
                    header("Location: login.php"); // Redirect to login page
                    exit();
                } else {
                    $error_message = "There was an error during registration. Please try again.";
                }
            } else {
                // Display specific error message if file upload failed
                if (!$nidUploadSuccess) {
                    $error_message = "There was an error uploading the NID card file.";
                }
                if (!$photoUploadSuccess) {
                    $error_message .= " There was an error uploading the profile photo file.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Timely</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Timely</h2>

        <?php
        // Display error or success message
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>$error_message</div>";
        }
        if (isset($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" class="form-control" id="firstName" name="firstName" required />
            </div>
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="lastName" required />
            </div>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" class="form-control" id="username" name="username" required />
            </div>
            <div class="form-group">
                <label for="number">Phone No</label>
                <input type="text" class="form-control" id="number" name="number" required />
            </div>
            <div class="form-group">
                <label for="UVName">UV Name</label>
                <input type="text" class="form-control" id="UVName" name="UVName" required />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required />
            </div>
            <div class="form-group">
                <label for="nidCard">NID Card Photo</label>
                <input type="file" class="form-control" id="nidCard" name="nidCard" accept="image/*" required />
            </div>
            <div class="form-group">
                <label for="profilePhoto">Profile Photo</label>
                <input type="file" class="form-control" id="profilePhoto" name="profilePhoto" accept="image/*" required />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required />
            </div>
            <div class="form-group">
                <label for="repeat_password">Repeat Password</label>
                <input type="password" class="form-control" id="repeat_password" name="repeat_password" required />
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Register</button>
        </form>

        <div>
            <p>Already Have An Account? <a href="login.php">Log in Here</a></p>
        </div>
    </div>
</body>
</html>
