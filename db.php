<?php
$servername = "sql211.infinityfree.com"; // Replace with your host
$username = "";      // Replace with your username
$password = "";      // Replace with your password
$dbname = "";   // Replace with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
