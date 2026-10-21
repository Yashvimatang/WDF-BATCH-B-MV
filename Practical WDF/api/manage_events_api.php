<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

function respond($status, $message, $data = null) {
    http_response_code($status);
    echo json_encode([
        "success" => $status >= 200 && $status < 300,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

if (!isset($_SESSION["student_id"])) {
    respond(401, "Please log in first.");
}

if (($_SESSION["role"] ?? "") !== "admin") {
    respond(403, "Admin access required.");
}

require_once "../db.php";

$uploadDir = "../uploads/events/";

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    respond(500, "Unable to create poster upload folder.");
}

function removePoster($poster) {
    if (!$poster) {
        return;
    }

    $path = "../uploads/events/" . basename($poster);

    if (is_file($path)) {
        unlink($path);
    }
}

function savePoster($file) {
    if (!$file || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        respond(400, "Poster upload failed.");
    }

    if ($file["size"] > 2 * 1024 * 1024) {
        respond(400, "Poster must be 2 MB or smaller.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        respond(400, "Only JPG, PNG and WebP posters are allowed.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    $destination = "../uploads/events/" . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        respond(500, "Unable to save poster.");
    }

    return $filename;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Use POST for event management.");
}

$action = $_POST["action"] ?? "";

if ($action === "add" || $action === "update") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? "";
    $venue = trim($_POST["venue"] ?? "");

    if (
        $title === "" ||
        $eventDate === "" ||
        $eventTime === "" ||
        $venue === ""
    ) {
        respond(400, "Fill in all required fields.");
    }

    $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);
    $timeObject = DateTime::createFromFormat("!H:i", $eventTime);

    if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !== $eventDate ||
        !$timeObject ||
        $timeObject->format("H:i") !== substr($eventTime, 0, 5)
    ) {
        respond(400, "Invalid date or time.");
    }

    
    if ($action === "add") {
        $poster = savePoster($_FILES["poster"] ?? null);

        $stmt = $conn->prepare(
            "INSERT INTO event_details
            (title, description, event_date, event_time, venue, poster)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            removePoster($poster);
            respond(500, "Unable to prepare event insertion.");
        }

        $stmt->bind_param(
            "ssssss",
            $title, $description, $eventDate,
            $eventTime, $venue, $poster
        );

        if (!$stmt->execute()) {
            removePoster($poster);
            respond(500, "Unable to add event.");
        }

        respond(201, "Event added successfully.");
    }

  
    $id = filter_var(
        $_POST["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        respond(400, "Invalid event ID.");
    }

    $check = $conn->prepare(
        "SELECT poster FROM event_details WHERE event_id = ?"
    );
    $check->bind_param("i", $id);
    $check->execute();

    $oldEvent = $check->get_result()->fetch_assoc();

    if (!$oldEvent) {
        respond(404, "Event not found.");
    }

  
    $newPoster = savePoster($_FILES["poster"] ?? null);
    $poster = $newPoster ?? $oldEvent["poster"];

    $stmt = $conn->prepare(
        "UPDATE event_details
         SET title = ?, description = ?, event_date = ?,
             event_time = ?, venue = ?, poster = ?
         WHERE event_id = ?"
    );

    if (!$stmt) {
        removePoster($newPoster);
        respond(500, "Unable to prepare event update.");
    }

    $stmt->bind_param(
        "ssssssi",
        $title, $description, $eventDate,
        $eventTime, $venue, $poster, $id
    );

    if (!$stmt->execute()) {
        removePoster($newPoster);
        respond(500, "Unable to update event.");
    }

    if ($newPoster) {
        removePoster($oldEvent["poster"]);
    }

    respond(200, "Event updated successfully.");
}


if ($action === "delete") {
    $id = filter_var(
        $_POST["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        respond(400, "Invalid event ID.");
    }

    $check = $conn->prepare(
        "SELECT poster FROM event_details WHERE event_id = ?"
    );
    $check->bind_param("i", $id);
    $check->execute();

    $event = $check->get_result()->fetch_assoc();

    if (!$event) {
        respond(404, "Event not found.");
    }

    $stmt = $conn->prepare(
        "DELETE FROM event_details WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        respond(500, "Unable to delete event.");
    }

    removePoster($event["poster"]);

    respond(200, "Event and poster deleted successfully.");
}

respond(400, "Invalid action.");
?>
