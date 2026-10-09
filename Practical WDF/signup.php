<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "Invalid request!";
    exit;
}
$fullname = trim($_POST["fullname"] ?? "");
$country = trim($_POST["country"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$terms = isset($_POST["terms"]);
if ($fullname == "") {
    exit("Please enter your full name.");
}
if ($country == "") {
    exit("Please select your country.");
}
$cleanPhone = preg_replace("/\s+/", "", $phone);
if (!preg_match("/^[0-9]{5,15}$/", $cleanPhone)) {
    exit("Please enter a valid phone number.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Please enter a valid email address.");
}
if (strlen($password) < 8) {
    exit("Password must contain at least 8 characters.");
}
if (!preg_match("/[A-Z]/", $password)) {
    exit("Password must contain an uppercase letter.");
}
if (!preg_match("/[a-z]/", $password)) {
    exit("Password must contain a lowercase letter.");
}
if (!preg_match("/[0-9]/", $password)) {
    exit("Password must contain a number.");
}
if (!preg_match("/[!@#$%^&*]/", $password)) {
    exit("Password must contain a special character.");
}
if ($password !== $confirmPassword) {
    exit("Passwords do not match.");
}
if (!$terms) {
    exit("Please accept the Terms & Conditions.");
}
$file = __DIR__ . "/signup.json";
if (!file_exists($file)) {
    $users = [];
} else {
    $jsonData = file_get_contents($file);
    $users = json_decode($jsonData, true);
    if (!is_array($users)) {
        exit("Unable to read signup data. Please check signup.json.");
    }
}
foreach ($users as $user) {
    if (
        isset($user["email"]) &&
        strtolower($user["email"]) === strtolower($email)
    ) {
        exit("This email is already registered.");
    }
}
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$newUser = [
    "id" => count($users) + 1,
    "fullname" => $fullname,
    "country" => $country,
    "phone" => $cleanPhone,
    "email" => $email,
    "password" => $hashedPassword,
    "created_at" => date("Y-m-d H:i:s")
];
$users[] = $newUser;
$jsonData = json_encode( $users,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
if (file_put_contents($file, $jsonData, LOCK_EX) === false) {
    exit("Unable to save signup details.");
}
echo "Sign up successful!";
?>