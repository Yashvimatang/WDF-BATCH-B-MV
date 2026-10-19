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
    header("Location: students.php");
    exit;
}
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if (!$id || $id === (int) $_SESSION["student_id"]) {
    header("Location: students.php?message=invalid");
    exit;
}
$stmt = $conn->prepare("DELETE FROM signupdetails WHERE id = ? AND role = 'student'"
);
$stmt->bind_param("i", $id);
if ($stmt->execute() && $stmt->affected_rows === 1) {
    $stmt->close();
    $conn->close();
    header("Location: students.php?message=deleted");
    exit;
}
$stmt->close();
$conn->close();
header("Location: students.php?message=failed");
exit;
?>