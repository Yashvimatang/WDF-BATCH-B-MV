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
$fullname = "";
$country = "";
$phone = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $country = trim($_POST["country"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    if (
        $fullname === "" ||
        $country === "" ||
        $phone === "" ||
        $email === "" ||
        $password === ""
    ) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
        $messageType = "error";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM signupdetails WHERE email = ?"
        );
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = "This email address is already registered.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
            $role = "student";
            $stmt = $conn->prepare(
                "INSERT INTO signupdetails
                (fullname, country, phone, email, password, role)
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssssss",
                $fullname,
                $country,
                $phone,
                $email,
                $hashedPassword,
                $role
            );
            if ($stmt->execute()) {
                $message = "Student added successfully!";
                $messageType = "success";
                $fullname = "";
                $country = "";
                $phone = "";
                $email = "";
            } else {
                $message = "Unable to add student. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Add Student</title>
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
            color: #3f607d;
            text-align: center;
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
        input:focus {
            outline: 2px solid #6b96bd;
            border-color: #3f607d;
        }
        button,
        .back {
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
        button:hover,
        .back:hover {
            background: #294967;
        }
        .message {
            padding: 12px;
            border-radius: 5px;
            overflow-wrap: anywhere;
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
            color: #3f607d;
            text-decoration: none;
            font: 26px Arial, sans-serif;
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
    <h1>Add Student</h1>
    <?php if ($message !== ""): ?>
        <p class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <form method="POST" action="add_student.php">
        <label for="fullname">Full Name</label>
        <input
            type="text"
            id="fullname"
            name="fullname"
            value="<?= htmlspecialchars($fullname) ?>"
            maxlength="150"
            required
        >
        <label for="country">Country / Country Code</label>
        <input
            type="text"
            id="country"
            name="country"
            value="<?= htmlspecialchars($country) ?>"
            maxlength="100"
            placeholder="e.g. India or +91"
            required
        >
        <label for="phone">Phone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            value="<?= htmlspecialchars($phone) ?>"
            maxlength="30"
            required
        >
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            maxlength="254"
            required
        >
        <label for="password">Initial Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >
        <div class="actions">
            <button type="submit">Add Student</button>
            <a class="back" href="students.php">Back to Students</a>
        </div>
    </form>
</div>
</body>
</html>
