<?php
$host = "localhost"; // or 127.0.0.1
$user = "root";      // default user for XAMPP
$password = "";      // default password is empty in XAMPP
$database = "restaurant_db"; // your database name

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>
