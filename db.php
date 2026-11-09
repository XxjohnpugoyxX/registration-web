<?php
/* STEP A: DATABASE CONNECTION - shared by every page */
$host = "localhost";
$db   = "registration_db";
$user = "root";
$pass = "";   // XAMPP default is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* Escape output safely */
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* Shared page header + navigation */
function pageHeader($title) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - Events Registration System</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f8; }
    nav { background: #1f3a5f; padding: 12px 20px; }
    nav a { color: #fff; text-decoration: none; margin-right: 20px; font-weight: bold; }
    nav a:hover { text-decoration: underline; }
    .container { max-width: 900px; margin: 20px auto; background: #fff; padding: 20px 30px; border-radius: 6px; }
    h2 { margin-top: 0; }
    label { display: inline-block; width: 130px; margin: 5px 0; }
    input[type=text], input[type=number], input[type=date], select { padding: 6px; width: 260px; }
    button { padding: 8px 16px; margin: 6px 6px 6px 0; cursor: pointer; }
    .msg { padding: 10px; margin: 12px 0; background: #eef6ff; border-left: 4px solid #1f3a5f; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #999; padding: 6px 10px; text-align: left; }
    th { background: #ddd; }
    .summary td { font-weight: bold; background: #f0f0f0; }
</style>
</head>
<body>
<nav>
    <a href="events.php">Events</a>
    <a href="participants.php">Participants</a>
    <a href="register.php">Registration</a>
    <a href="monitor.php">Monitoring</a>
</nav>
<div class="container">
<?php }

function pageFooter() { ?>
</div>
</body>
</html>
<?php }
