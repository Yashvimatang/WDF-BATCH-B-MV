
<?php
ini_set("session.use_only_cookies", "1");
ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

// Admin authentication
if (!isset($_SESSION["student_id"])) {
    header("Location: index.html");
    exit;
}

// Session timeout: 15 minutes
if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > 900
) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.html?timeout=1");
    exit;
}

// Only administrators can access the dashboard
if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: Pract1.php");
    exit;
}

$_SESSION["last_activity"] = time();

require_once "db.php";

function escapeHTML($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Total registered users
$userQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM signupdetails"
);

// Total students
$studentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM signupdetails
     WHERE role = 'student'"
);

// Total events
$eventQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM event_details"
);

// Latest five registrations
$latestQuery = mysqli_query(
    $conn,
    "SELECT fullname, email, role, created_at
     FROM signupdetails
     ORDER BY created_at DESC
     LIMIT 5"
);

if (!$userQuery || !$studentQuery || !$eventQuery || !$latestQuery) {
    error_log("Admin dashboard query failed: " . mysqli_error($conn));
    http_response_code(500);
    exit("Unable to load dashboard data. Please check the database.");
}

$userCount = mysqli_fetch_assoc($userQuery)["total"];
$studentCount = mysqli_fetch_assoc($studentQuery)["total"];
$eventCount = mysqli_fetch_assoc($eventQuery)["total"];

$adminName = $_SESSION["student_name"] ?? "Administrator";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Admin Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: #243b53;
            background: url("commonbackground.png") center / cover fixed;
        }

        header {
            background: #6b96bd;
            color: white;
            padding: 25px 15px;
            text-align: center;
        }

        header h1 {
            margin: 0 0 10px;
        }

        nav {
            background: #3f607d;
            padding: 12px;
            text-align: center;
        }

        nav a {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin: 5px 8px;
            padding: 8px;
        }

        nav a:hover {
            background: #6b96bd;
            border-radius: 5px;
        }

        main {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }

        .statistics {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .card {
            flex: 1;
            min-width: 180px;
            background: white;
            padding: 25px 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 3px 10px #0002;
        }

        .card h3 {
            margin-top: 0;
        }

        .card h2 {
            color: #3f607d;
            font-size: 32px;
            margin-bottom: 0;
        }

        .registrations {
            background: white;
            margin-top: 25px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px #0002;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 10px;
            border: 1px solid #ccd6df;
            text-align: left;
        }

        th {
            background: #6b96bd;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f6fa;
        }

        
.back-link {
    position: absolute;
    top: 25px;
    right: 30px;
    display: inline-block;
    padding: 12px 20px;
    background-color: #3f607d;
    color: #ffffff;
    text-decoration: none;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 15px;
    font-weight: bold;
    border: 1px solid #3f607d;
    border-radius: 6px;
    transition: background-color 0.3s ease;
    z-index: 1000;
}

.back-link:hover {
    background-color: #6b96bd;
    border-color: #6b96bd;
    color: #ffffff;
}

    </style>
</head>

<body>

<header>
    <h1>StudentHub Admin Dashboard</h1>
    <p>
        Welcome,
        <?php echo escapeHTML($adminName); ?>!
    </p>
    <a class="back-link" href="admin.php">&times;</a>
</header>

<nav>
    <a href="admin.php">Admin Home</a>
    <a href="admin_dashboard.php">Admin Dashboard</a>
    <a href="students.php">Manage Students</a>
    <a href="events.php">Manage Events</a>
    <a href="api_events.php">Event API</a>
    <a href="Pract1.php">Student Home</a>
    <a href="logout.php">Logout</a>
</nav>

<main>

    <h2>Dashboard Statistics</h2>

    <section class="statistics">

        <div class="card">
            <h3>Total Users</h3>
            <h2><?php echo (int)$userCount; ?></h2>
        </div>

        <div class="card">
            <h3>Total Students</h3>
            <h2><?php echo (int)$studentCount; ?></h2>
        </div>

        <div class="card">
            <h3>Total Events</h3>
            <h2><?php echo (int)$eventCount; ?></h2>
        </div>

    </section>

    <section class="registrations">
        <h2>Latest Registrations</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($latestQuery) > 0): ?>

                        <?php while ($user = mysqli_fetch_assoc($latestQuery)): ?>
                            <tr>
                                <td><?php echo escapeHTML($user["fullname"]); ?></td>
                                <td><?php echo escapeHTML($user["email"]); ?></td>
                                <td><?php echo escapeHTML(ucfirst($user["role"])); ?></td>
                                <td><?php echo escapeHTML($user["created_at"]); ?></td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4">No registrations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
