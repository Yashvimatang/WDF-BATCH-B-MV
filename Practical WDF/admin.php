<?php
ini_set("session.use_only_cookies", "1");
ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"])
        && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();
if (!isset($_SESSION["student_id"])) {
    header("Location: index.html");
    exit;
}
if ( !isset($_SESSION["last_activity"]) || time() - $_SESSION["last_activity"] > 900) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.html?timeout=1");
    exit;
}
if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: dashboard.php");
    exit;
}
$_SESSION["last_activity"] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | StudentHub</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Georgia, "Times New Roman", serif;
            background-color: #c9dbec;
            background-image: url("commonbackground.png");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #333;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 35px 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(35, 62, 87, 0.15);
        }
        h1 {
            text-align: center;
            color: #3f607d;
            font-size: 32px;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .welcome {
            font-size: 18px;
            color: #333;
            text-align: center;
            margin: 20px 0 12px;
        }
        .message {
            text-align: center;
            padding: 20px;
            color: #52677c;
            font-size: 16px;
            line-height: 1.7;
        }
        .admin-badge {
            display: block;
            width: fit-content;
            margin: 0 auto 20px;
            padding: 8px 16px;
            background-color: #e4eff8;
            color: #3f607d;
            border: 1px solid #c5d9e9;
            border-radius: 20px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 22px;
            background-color: #3f607d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 15px;
            transition: background-color 0.2s ease,transform 0.2s ease;
        }
        .back-btn:hover {
            background-color: #294967;
            transform: translateY(-2px);
        }
        .actions {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-badge">Administrator Access</div>
        <h1>Admin Dashboard</h1>
        <p class="welcome">
            Welcome,
            <strong><?php echo htmlspecialchars($_SESSION["student_name"] ?? "Admin"); ?></strong>!
        </p>
        <p class="message">
            You are logged in with administrator access.
        </p>
        <div class="actions">
            <a class="back-btn" href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
