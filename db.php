<?php
// Database connection parameters
$host = "localhost";
$username = "u8gr0sjr9p4p4";
$password = "9yxuqyo3mt85";
$database = "dbm9wqoxh4nkyj";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>
