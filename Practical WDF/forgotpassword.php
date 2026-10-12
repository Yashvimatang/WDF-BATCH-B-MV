<?php
require_once "db.php";
$email = trim($_POST["email"] ?? "");
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Please enter a valid email address.");
}
$stmt = $conn->prepare(
    "SELECT id FROM signupdetails WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    exit("No account was found for that email. <a href='forgotpassword.html'>Try again</a>");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | StudentHub</title>
     <style>
        body {
            font-family: Georgia, "Times New Roman", serif;
            background-color: #c9dbec;
            color: #333;
            text-align: center;
        }
        .other-page {
    background-image: url("commonbackground.png");
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
        }
        .login-box {
            background-color:  #6b8ba9;
            width: 400px;
            padding: 30px;
            margin: 80px auto;
            border-radius: 10px;
        }
        h2 {
            color: #333;
        }
        p {
            font-size: 16px;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .btn {
            margin-top: 25px;
            padding: 12px 30px;
            background-color:  #3f607d;
            color: white;
            border: none;
        }
              .block {
        position: relative;
        background-color: white;
        width: 90%;
        max-width: 1450px;
        margin: 40px auto;
        padding: 40px;
        border-radius: 10px;
        box-sizing: border-box;
      }
      .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 30px;
        height: 30px;
        border: 1px solid #777;
        border-radius: 5px;
        background-color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .close-btn:hover {
        color: rgb(48, 83, 164);
        background-color: #f0f0f0;
      }
    </style>
</head>
<body>
    <h2>Set a New Password</h2>
    <form action="savepassword.php" method="post">
        <input type="hidden" name="email"
               value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password"
               minlength="8" required>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password"
               name="confirm_password" minlength="8" required>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>