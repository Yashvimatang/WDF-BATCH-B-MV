
<?php
require_once "db.php";
$jsonFile = __DIR__ . "/signup.json";
if (!file_exists($jsonFile)) {
    die("Error: signup.json file not found.");
}
$jsonData = file_get_contents($jsonFile);
$users = json_decode($jsonData, true);
if (!is_array($users)) {
    die("Error: Invalid JSON data.");
}
$imported = 0;
$skipped = 0;
$sql = "INSERT INTO signupdetails
        (fullname, country, phone, email, password, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
foreach ($users as $user) {
    $fullname = $user["fullname"] ?? "";
    $country = $user["country"] ?? "";
    $phone = $user["phone"] ?? "";
    $email = $user["email"] ?? "";
    $hashedPassword = $user["password"] ?? "";
    $createdAt = $user["created_at"] ?? date("Y-m-d H:i:s");
    if (
        $fullname === "" ||
        $email === "" ||
        $hashedPassword === ""
    ) {
        $skipped++;
        continue;
    }
    $stmt->bind_param(
        "ssssss",
        $fullname,
        $country,
        $phone,
        $email,
        $hashedPassword,
        $createdAt
    );
    try {
        if ($stmt->execute()) {
            $imported++;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $skipped++;
        } else {
            $stmt->close();
            $conn->close();
            die("Import failed: " . $e->getMessage());
        }
    }
}
$stmt->close();
$conn->close();
echo "<h2>JSON Import Completed!</h2>";
echo "<p>Records imported: " . $imported . "</p>";
echo "<p>Records skipped: " . $skipped . "</p>";

?>