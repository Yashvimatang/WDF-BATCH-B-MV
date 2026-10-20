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
$message = "";
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];
$maxSize = 2 * 1024 * 1024;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $posterName = null;
    $savedPath = null;
    if ($title === "" || $eventDate === "" ||$eventTime === "" || $venue === "") {
        $message = "Please fill in all required fields.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate) || !checkdate( (int) substr($eventDate, 5, 2), (int) substr($eventDate, 8, 2), (int) substr($eventDate, 0, 4) )) {
        $message = "Please enter a valid event date.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $eventTime)) {
        $message = "Please enter a valid event time.";
    } else {
        $uploadDir = __DIR__ . "/uploads/events/";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $message = "Unable to create the poster upload folder.";
        } elseif (!is_writable($uploadDir)) { 
            $message = "The poster upload folder is not writable.";
        } else {
            $upload = $_FILES["poster"] ?? null;
            if ($upload && $upload["error"] !== UPLOAD_ERR_NO_FILE) {
                if ($upload["error"] !== UPLOAD_ERR_OK) {
                    $message = "Poster upload failed. Please try again.";
                } elseif ($upload["size"] > $maxSize) {
                    $message = "Poster must be 2 MB or smaller.";
                } elseif (!is_uploaded_file($upload["tmp_name"])) {
                    $message = "Invalid uploaded file.";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($upload["tmp_name"]);
                    if (!isset($allowedTypes[$mime])) {
                        $message = "Only JPG, PNG, and WebP posters are allowed.";
                    } else {
                        $posterName = bin2hex(random_bytes(16))
                            . "." . $allowedTypes[$mime];
                        $savedPath = $uploadDir . $posterName;
                        if (!move_uploaded_file($upload["tmp_name"],$savedPath)){
                            $message = "Could not save the poster.";
                        }
                    }
                }
            }
            if ($message === "") {
                $sql = "INSERT INTO event_details
                    (title, description, event_date, event_time, venue, poster)
                    VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $message = "Database error. Event was not saved.";
                } else {
                    $stmt->bind_param("ssssss",$title,$description,$eventDate,$eventTime,$venue,$posterName);
                    if ($stmt->execute()) {
                        $stmt->close();
                        $conn->close();
                        header("Location: events.php?message=added");
                        exit;
                    }
                    $message = "Unable to save the event.";
                    $stmt->close();
                }
                if ($message !== "" && $savedPath !== null) {
                    if (is_file($savedPath)) {
                        unlink($savedPath);
                    }
                }
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Add Event</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 25px;
            font-family: Georgia, "Times New Roman", serif;
            background: #c9dbec url("commonbackground.png") center / cover fixed;
            color: #24384b;
        }
        .container {
            max-width: 650px;
            margin: 25px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.15);
        }
        h1 { color: #3f607d; }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            margin-top: 7px;
            padding: 11px;
            border: 1px solid #b7c6d3;
            border-radius: 5px;
            font: inherit;
        }
        textarea { min-height: 100px; resize: vertical; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 11px 16px;
            border: 0;
            border-radius: 6px;
            background: #3f607d;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
        }
        .message {
            padding: 12px;
            background: #fff1f0;
            border: 1px solid #e0a5a0;
            border-radius: 5px;
        }
        .hint {
            color: #5b6e7f;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Add New Event</h1>
    <p>Enter the event details below. Fields marked * are required.</p>
    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title *</label>
        <input type="text" id="title" name="title" maxlength="150" required>
        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>
        <label for="event_date">Event Date *</label>
        <input type="date" id="event_date" name="event_date" required>
        <label for="event_time">Event Time *</label>
        <input type="time" id="event_time" name="event_time" required>
        <label for="venue">Venue *</label>
        <input type="text" id="venue" name="venue" maxlength="200" required>
        <label for="poster">Event Poster (optional)</label>
        <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <p class="hint">Allowed formats: JPG, PNG, WebP. Maximum size: 2 MB.</p>
        <button type="submit" class="btn">Save Event</button>
        <a href="events.php" class="btn">Cancel</a>
    </form>
</div>
</body>
</html>
