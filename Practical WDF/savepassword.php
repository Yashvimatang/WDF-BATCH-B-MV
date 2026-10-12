
<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Invalid email address.");
}

if (strlen($password) < 8) {
    exit("Password must contain at least 8 characters.");
}

if ($password !== $confirmPassword) {
    exit("Passwords do not match. Please go back and try again.");
}

// Hash the new password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update the password for the matching account
$stmt = $conn->prepare(
    "UPDATE signupdetails SET password = ? WHERE email = ?"
);
$stmt->bind_param("ss", $hashedPassword, $email);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    echo "Password updated successfully! ";
    echo "<a href='index.html'>Login now</a>";
} else {
    echo "Password was not changed. The account may not exist, ";
    echo "or the password may already be the same. ";
    echo "<a href='forgotpassword.html'>Try again</a>";
}

$stmt->close();
$conn->close();
?>