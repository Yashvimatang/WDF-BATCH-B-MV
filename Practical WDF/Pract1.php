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
if (!isset($_SESSION["last_activity"]) || time() - $_SESSION["last_activity"] > 900) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.html?timeout=1");
    exit;
}
if (($_SESSION["role"] ?? "") !== "student") {
    header("Location: admin.php");
    exit;
}
$_SESSION["last_activity"] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<link rel="stylesheet" href="Prac1.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Student Portal</title>
</head>
<body class="home-page">

   <header>
    <div class="logo">
        <img src="logo.png" alt="StudentHub Logo">
        <br>
        <h1 id = "first">StudentHub</h1>
    </div>
    <br>
    <br>
    <nav id="navMenu">
        <a href ="notifications.html">&#x1F514;</a>
        <a href="dashboard.php">Home</a>
        <a href="about.html">About</a>
        <a href="contactus.html">Contact</a>
        <a href="index.html">Login</a>
        <a href="logout.php">Logout</a> 
    </nav>
     <button class="menu-btn" id="menuBtn" type="button">
        ☰
    </button>
</header>

    <main>
        <section style="text-align : center; background-color: rgba(238, 245, 250, 0.8)";>
            <br>
            <br>
            <h2>Welcome to StudentHub</h2>
            <p>
                A centralized platform designed to help students
                manage their academic activities in one place.
            </p>
            <br>
            <a href="index.html">
                Login to StudentHub
            </a>
        </section>
        <section style="text-align: center;">
            <h2>StudentHub Features</h2>
            <div class = "slider">
                <button class="prev" id="prevBtn">&#10094;</button>
            <div class = "slider-content">
                <article class = "block">
                    <h3>Attendance</h3>
                    <p>
                        Check your subject-wise attendance
                        and monitor your attendance percentage.
                    </p>
                    <a href="attendance.php">
                        View Attendance
                    </a>
                </article>
                <article class = "block">
                    <h3>Assignments</h3>
                    <p>
                        View upcoming assignments and
                        submit your academic work.
                    </p>
                    <a href="assignments.html">
                        View Assignments
                    </a>
                </article>
                <article class = "block">
                    <h3>Study Notes</h3>
                    <p>
                        Access important study materials
                        and download subject notes.
                    </p>
                    <a href="studynotes.html">
                        View Notes
                    </a>
                </article>
                <article class="block">
                    <h3>Results</h3>
                    <p>
                        View your examination results
                        and academic performance.
                    </p>
                    <a href="result.html">
                        View Results
                    </a>
                </article>
                <article class="block">
                    <h3>Timetable</h3>
                    <p>
                        Check your daily and weekly
                        class timetable.
                    </p>
                    <a href="timetable.html">
                        View Timetable
                    </a>
                </article>
            </div>
            <button class="next" id="nextBtn">&#10095;</button>
            </div>
        </section>
        
        <section style="text-align: center;">
            <h2>StudentHub Users</h2>
            <p>
                StudentHub provides different features
                for different types of users.
            </p>
            <ul>
                <li>
                    <strong>Students:</strong>
                    Access attendance, notes, assignments,
                    results, and timetable.
                </li>
                <li>
                    <strong>Faculty:</strong>
                    Manage attendance, assignments,
                    notes, and announcements.
                </li>
                <li>
                    <strong>Admin:</strong>
                    Manage students, faculty, courses,
                    and the overall portal.
                </li>
            </ul>
        </section>
        <section style="text-align: center;">
            <h2>Login to Your Account</h2>
            <a href="faculty.html">
                Faculty Login
            </a>
            <a href="admin.html">
                Admin Login
            </a>
        </section>
    </main>
    <hr>
    <footer>
        <h3>StudentHub</h3>
        <p>
        A centralized student academic portal.
        </p>
        <p>
            &copy; 2026 StudentHub. All Rights Reserved.
        </p>
    </footer>
    <script>
   //For slider portion 
let articles = document.querySelectorAll(".block");
let current = 0;
let nextBtn = document.getElementById("nextBtn");
let prevBtn = document.getElementById("prevBtn");
articles[current].classList.add("active");
nextBtn.addEventListener("click", function() {
    articles[current].classList.remove("active");
    current++;
    if (current >= articles.length) {
        current = 0;
    }
    articles[current].classList.add("active");
});
prevBtn.addEventListener("click", function() {
    articles[current].classList.remove("active");
    current--;
    if (current < 0) {
        current = articles.length - 1;
    }
    articles[current].classList.add("active");
});
// For hamburger portion 
let menuBtn = document.getElementById("menuBtn");
let navMenu = document.getElementById("navMenu");
menuBtn.addEventListener("click", function() {
    event.preventDefault();
    navMenu.classList.toggle("show");

    if (navMenu.classList.contains("show")) {
        menuBtn.textContent = "✕";
    } else {
        menuBtn.textContent = "☰";
    }
});
</script>
</body>
</html>







