<?php
// Securely load environment variables
$env = parse_ini_file(__DIR__ . '/.env');

if (!$env) {
    die("Security Error: .env file is missing or unreadable.");
}

$host = $env['DB_HOST'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$dbname = $env['DB_NAME'];

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
