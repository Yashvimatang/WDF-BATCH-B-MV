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
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
}
if (!$id || $id < 1) {
    $conn->close();
    header("Location: events.php?message=invalid");
    exit;
}
$stmt = $conn->prepare(
    "SELECT * FROM event_details WHERE event_id = ?"
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
$message = "";
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];
$maxSize = 2 * 1024 * 1024;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $posterName = $event["poster"];
    $newPosterPath = null;
    if ($title === "" || $eventDate === "" || $eventTime === "" || $venue === "") {
        $message = "Please fill in all required fields.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate) || !checkdate((int) substr($eventDate, 5, 2), (int) substr($eventDate, 8, 2),(int) substr($eventDate, 0, 4) )) {
        $message = "Please enter a valid event date.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $eventTime)) {
        $message = "Please enter a valid event time.";
    } else {
        $upload = $_FILES["poster"] ?? null;
        $uploadDir = __DIR__ . "/uploads/events/";

        if ($upload && $upload["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($upload["error"] !== UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
            } elseif ($upload["size"] > $maxSize) {
                $message = "Poster must be 2 MB or smaller.";
            } elseif (!is_uploaded_file($upload["tmp_name"])) {
                $message = "Invalid uploaded file.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload["tmp_name"]);

                if (!isset($allowedTypes[$mime])) {
                    $message = "Only JPG, PNG, and WebP posters are allowed.";
                } elseif (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                    $message = "Poster upload folder is unavailable.";
                } else {
                    $posterName = bin2hex(random_bytes(16))
                        . "." . $allowedTypes[$mime];
                    $newPosterPath = $uploadDir . $posterName;

                    if (!move_uploaded_file(
                        $upload["tmp_name"],
                        $newPosterPath
                    )) {
                        $message = "Could not save the new poster.";
                        $newPosterPath = null;
                        $posterName = $event["poster"];
                    }
                }
            }
        }
        if ($message === "") {
            $sql = "UPDATE event_details
                    SET title = ?, description = ?, event_date = ?,
                        event_time = ?, venue = ?, poster = ?
                    WHERE event_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssi",
                $title,
                $description,
                $eventDate,
                $eventTime,
                $venue,
                $posterName,
                $id
            );
            if ($stmt->execute()) {
                $stmt->close();
                if ($newPosterPath !== null && !empty($event["poster"])){
                    $oldPath = __DIR__ . "/uploads/events/"
                        . basename($event["poster"]);
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $conn->close();
                header("Location: events.php?message=updated");
                exit;
            }
            $message = "Unable to update the event.";
            $stmt->close();
            if ($newPosterPath !== null && is_file($newPosterPath)) {
                unlink($newPosterPath);
            }
        }
    }
}
if ($message !== "") {
    $stmt = $conn->prepare("SELECT * FROM event_details WHERE event_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Edit Event</title>
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
        .poster {
            display: block;
            max-width: 180px;
            max-height: 180px;
            margin-top: 10px;
            object-fit: contain;
        }
        .hint { color: #5b6e7f; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Event</h1>
    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int) $event["event_id"] ?>">
        <label for="title">Event Title *</label>
        <input type="text" id="title" name="title" maxlength="150" value="<?= htmlspecialchars($event["title"], ENT_QUOTES, "UTF-8") ?>" required>
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= htmlspecialchars($event["description"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
        <label for="event_date">Event Date *</label>
        <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event["event_date"], ENT_QUOTES, "UTF-8") ?>" required>
        <label for="event_time">Event Time *</label>
        <input type="time" id="event_time" name="event_time" value="<?= htmlspecialchars(substr($event["event_time"], 0, 5), ENT_QUOTES, "UTF-8") ?>"required>
        <label for="venue">Venue *</label>
        <input type="text" id="venue" name="venue" maxlength="200" value="<?= htmlspecialchars($event["venue"], ENT_QUOTES, "UTF-8") ?>" required>
        <?php if (!empty($event["poster"])): ?>
            <label>Current Poster</label>
            <img class="poster" src="uploads/events/<?= htmlspecialchars(basename($event["poster"]), ENT_QUOTES, "UTF-8") ?>" alt="Current event poster">
        <?php endif; ?>
        <label for="poster">Replace Poster (optional)</label>
        <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <p class="hint">JPG, PNG, or WebP. Maximum size: 2 MB.</p>
        <button type="submit" class="btn">Update Event</button>
        <a href="events.php" class="btn">&times;</a>
    </form>
</div>
</body>
</html>
