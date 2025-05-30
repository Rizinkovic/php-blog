<?php
$servername = "sql211.infinityfree.com"; // Replace with your host
$username = "if0_39077665";      // Replace with your username
$password = "salemx1234";      // Replace with your password
$dbname = "if0_39077665_myblog";   // Replace with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
