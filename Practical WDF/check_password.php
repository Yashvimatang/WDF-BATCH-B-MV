
<?php
require_once "db.php";

$email = "abc@gmail.com"; // Change to the account that cannot log in

$stmt = $conn->prepare(
    "SELECT id, email, password
     FROM signupdetails
     WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $storedEmail, $hash);
    $stmt->fetch();

    echo "Account found.<br>";
    echo "Student ID: " . (int)$id . "<br>";
    echo "Hash length: " . strlen($hash) . "<br>";
    echo "Hash algorithm: " .
        password_get_info($hash)["algoName"];
} else {
    echo "Account not found.";
}

$stmt->close();
$conn->close();
?>