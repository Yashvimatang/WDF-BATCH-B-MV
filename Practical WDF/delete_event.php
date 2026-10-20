<?php
session_start();
if (!isset($_SESSION["student_id"])) {
    header("Location: index.html");
    exit;
}
if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}
require_once "db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: events.php");
    exit;
}
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    $conn->close();
    header("Location: events.php?message=invalid");
    exit;
}
$stmt = $conn->prepare(
    "SELECT poster FROM event_details WHERE event_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();
if (!$event) {
    $conn->close();
    header("Location: events.php?message=notfound");
    exit;
}
$stmt = $conn->prepare(
    "DELETE FROM event_details WHERE event_id = ?"
);
$stmt->bind_param("i", $id);
if ($stmt->execute() && $stmt->affected_rows === 1) {
    $stmt->close();
    if (!empty($event["poster"])) {
        $posterPath = __DIR__ . "/uploads/events/"
            . basename($event["poster"]);
        if (is_file($posterPath)) {
            unlink($posterPath);
        }
    }
    $conn->close();
    header("Location: events.php?message=deleted");
    exit;
}
$stmt->close();
$conn->close();
header("Location: events.php?message=failed");
exit;
?>
