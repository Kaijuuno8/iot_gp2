<?php
// Database configuration
$host = 'localhost';
$db   = 'IOT';
$user = 'postgres';
$pass = 'root';
$port = '5432'; // Default PostgreSQL port

// Create connection string
$conn_string = "host=$host port=$port dbname=$db user=$user password=$pass";

// Establish a connection to the PostgreSQL database
$conn = pg_connect($conn_string);

if (!$conn) {
    die("Error: Unable to connect to the database.");
}
?>
