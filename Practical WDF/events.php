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
$sql = "SELECT * FROM event_details ORDER BY event_date DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Unable to load events. Please check the database.");
}
$messages = [
    "added"    => "Event added successfully.",
    "updated"  => "Event updated successfully.",
    "deleted"  => "Event deleted successfully.",
    "invalid"  => "Invalid event ID.",
    "notfound" => "Event not found.",
    "failed"   => "Unable to delete the event."
];
$messageKey = $_GET["message"] ?? "";
$message = $messages[$messageKey] ?? "";
$isError = in_array(
    $messageKey,
    ["invalid", "notfound", "failed"],
    true
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Event Management</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 25px;
            font-family: Georgia, "Times New Roman", serif;
            background: #c9dbec url("commonbackground.png") center / cover fixed;
            color: #24384b;
        }
        .container {
            max-width: 1150px;
            margin: 30px auto;
            padding: 25px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0;
            color: #3f607d;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background: #3f607d;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }
        .btn:hover {
            background: #29445d;
        }
        .back {
            background: #687b8d;
        }
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #d5dee6;
            text-align: left;
        }
        th {
            background: #3f607d;
            color: white;
        }
        tr:nth-child(even) {
            background: #f1f5f8;
        }
        .poster {
            width: 90px;
            height: auto;
            max-height: 100px;
            object-fit: contain;
        }
        .empty {
            padding: 25px;
            text-align: center;
            color: #64788a;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .actions form {
            margin: 0;
        }
        .message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .success {
            color: #245b32;
            background: #e8f4e8;
            border: 1px solid #a8d2af;
        }
        .error {
            color: #842b2b;
            background: #fff1f0;
            border: 1px solid #e0a5a0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Event Management</h1>
        <div>
            <a href="add_event.php" class="btn">+ Add Event</a>
            <a href="admin.php" class="btn back">&times;</a>
        </div>
    </div>
    <p>Manage StudentHub events, schedules, venues, and posters.</p>
    <?php if ($message !== ""): ?>
        <p class="message <?= $isError ? 'error' : 'success' ?>"> <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?> </p>
    <?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Poster</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $event["event_id"] ?></td>
                        <td><?php if (!empty($event["poster"])): ?><img class="poster" src="uploads/events/<?= htmlspecialchars(basename($event["poster"]), ENT_QUOTES, "UTF-8") ?>"alt="Event poster">
                            <?php else: ?> No poster <?php endif; ?></td>
                        <td><?= htmlspecialchars($event["title"], ENT_QUOTES, "UTF-8") ?> </td>
                        <td><?= htmlspecialchars($event["event_date"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($event["event_time"], ENT_QUOTES, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($event["venue"], ENT_QUOTES, "UTF-8") ?></td>
                        <td> <div class="actions">
                                <a class="btn" href="edit_event.php?id=<?= (int) $event["event_id"] ?>">Edit</a>
                                <form action="delete_event.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="id" value="<?= (int) $event["event_id"] ?>">
                                    <button class="btn" type="submit"> Delete </button>
                                </form>
                            </div> </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">No events found. Click Add Event to create your first event.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
$result->free();
$conn->close();
?>
