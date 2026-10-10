
<?php

$host = "localhost";
$port = 3306;
$dbname = "studenthubportal";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    echo "PDO database connection successful!";

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
