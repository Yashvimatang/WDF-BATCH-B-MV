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
$search = trim($_GET["search"] ?? "");
$roleFilter = $_GET["role"] ?? "";
$sql = "SELECT id, fullname, country, phone, email, role, created_at FROM signupdetails WHERE (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
$searchTerm = "%" . $search . "%";
if (in_array($roleFilter, ["student", "admin"], true)) {
    $sql .= " AND role = ?";
}
$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if (in_array($roleFilter, ["student", "admin"], true)) {
    $stmt->bind_param("ssss",$searchTerm,$searchTerm,$searchTerm,$roleFilter);
} else {
    $stmt->bind_param("sss",$searchTerm,$searchTerm,$searchTerm);
}
$stmt->execute();
$result = $stmt->get_result();
$message = "";
$messageType = "success";
if (($_GET["message"] ?? "") === "deleted") {
    $message = "Student deleted successfully!";
} elseif (($_GET["message"] ?? "") === "failed") {
    $message = "Unable to delete student. It may not exist or may have related records.";
    $messageType = "error";
} elseif (($_GET["message"] ?? "") === "invalid") {
    $message = "Invalid student ID or operation.";
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Student Management</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 30px 20px;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            background-color: #c9dbec;
            background-image: url("commonbackground.png");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #24384a;
        }
        .container {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 35px 25px 25px;
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(63, 96, 125, 0.18);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(35, 62, 87, 0.18);
            overflow-x: auto;
        }
        h1 {
            margin: 15px 0 25px;
            color: #3f607d;
            text-align: center;
            font-size: 32px;
        }
        p {
            line-height: 1.6;
        }
        .message {
            padding: 12px 15px;
            background: #e8f1f8;
            border: 1px solid #d3e1ed;
            border-radius: 6px;
            color: #31516d;
            font-size: 15px;
        }
        .message.error {
            background: #fde8e8;
            border-color: #f3cccc;
            color: #9b2525;
        }
        .back {
            position: absolute;
            top: 12px;
            left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            color: #3f607d;
            font-family: Arial, sans-serif;
            font-size: 26px;
            text-decoration: none;
            border-radius: 50%;
        }
        .back:hover {
            background: #e8f1f8;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        .toolbar input,.toolbar select {
            padding: 10px;
            border: 1px solid #b9cbdc;
            border-radius: 5px;
            font: inherit;
        }
        .toolbar input {
            flex: 1;
            min-width: 180px;
        }
        .add-btn,.edit-btn,.delete-btn,.filter-btn,.reset-btn {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
            white-space: nowrap;
        }
        .add-btn,.edit-btn,.filter-btn {
            background: #3f607d;
        }
        .add-btn:hover,.edit-btn:hover,.filter-btn:hover {
            background: #294967;
        }
        .reset-btn {
            background: #6b96bd;
        }
        .reset-btn:hover {
            background: #4d789f;
        }
        .delete-btn {
            background: #b74747;
        }
        .delete-btn:hover {
            background: #913333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            font-size: 14px;
        }
        th,td {
            padding: 12px 10px;
            border: 1px solid #c5d2de;
            text-align: left;
            overflow-wrap: anywhere;
        }
        th {
            background: #6b96bd;
            color: white;
            font-weight: bold;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) {
            background: #f1f5f8;
        }
        tbody tr:hover {
            background: #e4eff8;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .actions form {
            display: inline;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <a class="back" href="admin.php" title="Back to admin dashboard"
       aria-label="Back to admin dashboard">&times;</a>
    <h1>Student Management</h1>
    <p>View and manage registered StudentHub users below.</p>
    <?php if ($message !== ""): ?>
        <p class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <p class="message">
        Total matching records: <?= $result->num_rows ?>
    </p>
    <form class="toolbar" method="GET" action="students.php">
        <input type="search" name="search" placeholder="Search name, email or phone"
            value="<?= htmlspecialchars($search) ?>"
        >
        <select name="role">
            <option value="">All roles</option>
            <option value="student"
                <?= $roleFilter === "student" ? "selected" : "" ?>>
                Students
            </option>
            <option value="admin"
                <?= $roleFilter === "admin" ? "selected" : "" ?>>
                Admins
            </option>
        </select>
        <button class="filter-btn" type="submit">Search</button>
        <a class="reset-btn" href="students.php">Reset</a>
        <a class="add-btn" href="add_student.php">+ Add Student</a>
    </form>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Country</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int) $row["id"] ?></td>

                    <td>
                        <?= htmlspecialchars($row["fullname"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["email"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["phone"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["country"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["role"]) ?>
                    </td>

                    <td>
                        <div class="actions">

                            <?php if ($row["role"] === "student"): ?>

                                <a class="edit-btn"
                                   href="edit_student.php?id=<?= (int) $row["id"] ?>">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="delete_student.php"
                                      onsubmit="return confirm('Are you sure you want to delete this student?');">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int) $row["id"] ?>">

                                    <button class="delete-btn" type="submit">
                                        Delete
                                    </button>
                                </form>

                            <?php else: ?>
                                <span>Admin account</span>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No matching records found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
