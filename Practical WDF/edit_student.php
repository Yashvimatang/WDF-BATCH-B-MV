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
$messageType = "";
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $fullname = trim($_POST["fullname"] ?? "");
    $country = trim($_POST["country"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    if (!$id || $fullname === "" || $country === "" ||
        $phone === "" || $email === "") {
        $message = "Please enter valid details in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $check = $conn->prepare("SELECT id FROM signupdetails WHERE email = ? AND id <> ?" );
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = "That email is already used by another account.";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare( "UPDATE signupdetails SET fullname = ?, country = ?, phone = ?, email = ? WHERE id = ? AND role = 'student'");
            $stmt->bind_param("ssssi",$fullname,$country,$phone,$email, $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "Student record updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "No changes made, or student record not found.";
                    $messageType = "error";
                }
            } else {
                $message = "Unable to update the student record.";
                $messageType = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}
$student = null;
if ($id) {
    $stmt = $conn->prepare("SELECT id, fullname, country, phone, email FROM signupdetails WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
}
if (!$student && $message === "") {
    $message = "Student record not found.";
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Edit Student</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 25px 15px;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            background: #c9dbec url("commonbackground.png") center / cover fixed;
            color: #24384a;
        }
        .container {
            position: relative;
            max-width: 600px;
            margin: 35px auto;
            padding: 35px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(35, 62, 87, 0.18);
        }
        h1 {
            text-align: center;
            color: #3f607d;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin: 15px 0 7px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #b9cbdc;
            border-radius: 5px;
            font: inherit;
        }
        button, .back {
            display: inline-block;
            margin-top: 20px;
            padding: 11px 18px;
            border: none;
            border-radius: 5px;
            background: #3f607d;
            color: white;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
        }
        button:hover, .back:hover {
            background: #294967;
        }
        .message {
            padding: 12px;
            border-radius: 5px;
        }
        .success {
            background: #e4f5e9;
            color: #216b37;
        }
        .error {
            background: #fde8e8;
            color: #9b2525;
        }
        .close {
            position: absolute;
            top: 10px;
            left: 15px;
            font: 26px Arial, sans-serif;
            color: #3f607d;
            text-decoration: none;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <a class="close" href="students.php" aria-label="Close">&times;</a>
    <h1>Edit Student</h1>
    <?php if ($message !== ""): ?>
        <p class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <?php if ($student): ?>
        <form method="POST" action="edit_student.php">
            <input type="hidden" name="id"
                   value="<?= (int) $student["id"] ?>">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname"
                   maxlength="150" required
                   value="<?= htmlspecialchars($student["fullname"]) ?>">
            <label for="country">Country / Country Code</label>
            <input type="text" id="country" name="country"
                   maxlength="100" required
                   value="<?= htmlspecialchars($student["country"]) ?>">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone"
                   maxlength="30" required
                   value="<?= htmlspecialchars($student["phone"]) ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   maxlength="254" required
                   value="<?= htmlspecialchars($student["email"]) ?>">
            <div class="actions">
                <button type="submit">Save Changes</button>
                <a class="back" href="students.php">Back to Students</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
