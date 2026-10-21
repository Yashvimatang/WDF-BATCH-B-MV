<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
if (!isset($_SESSION["student_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false,"message" => "Please log in first."]);
    exit;
}
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    echo json_encode(["success" => false,"message" => "Admin access required."]);
    exit;
}
require_once "../db.php";
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([ "success" => false,"message" => "Only GET requests are allowed."]);
    exit;
}
$sql = "SELECT event_id, title, description,event_date, event_time, venue, poster FROM event_details ORDER BY event_date ASC, event_time ASC";
$result = $conn->query($sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false,"message" => "Unable to fetch events."]);
    exit;
}
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
echo json_encode(["success" => true,"message" => "Events fetched successfully.","data" => $events]);
$conn->close();
?>