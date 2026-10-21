```php
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

$logs = [];
$message = "";

$sql = "SELECT log_id, admin_id, admin_name, action, details, created_at
        FROM audit_logs
        ORDER BY created_at DESC
        LIMIT 200";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
} else {
    $message = "Unable to load audit logs.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Audit Logs</title>

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
            max-width: 1200px;
            margin: 20px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
        }

        h1 {
            color: #3f607d;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            background: #3f607d;
            color: white;
            text-decoration: none;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #d5e0e9;
            text-align: left;
            overflow-wrap: anywhere;
        }

        th {
            background: #3f607d;
            color: white;
        }

        tr:nth-child(even) {
            background: #edf3f8;
        }

        .message {
            padding: 12px;
            background: #fff1f0;
            border: 1px solid #e0a5a0;
            border-radius: 5px;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #5b6e7f;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="top-bar">
        <h1>Audit Logs</h1>
        <a class="btn" href="admin.php">Back to Admin</a>
    </div>

    <p>Showing the latest 200 administrator activities.</p>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>

    <?php elseif (empty($logs)): ?>
        <p class="empty">No audit records found yet.</p>

    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Admin ID</th>
                        <th>Admin Name</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date and Time</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= (int) $log["log_id"] ?></td>
                        <td><?= (int) $log["admin_id"] ?></td>

                        <td>
                            <?= htmlspecialchars(
                                $log["admin_name"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $log["action"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $log["details"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $log["created_at"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
