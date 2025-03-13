<?php
// MySQL server connection information
$servername = "172.17.0.5";
$username = "root";
$password = "pw";

// Create connection without database selection first
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS example_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db("example_db");

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS example_table (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully or already exists<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// SQL to insert new rows
$sql = "INSERT INTO example_table (first_name, last_name) VALUES ('John', 'Doe'), ('Jane', 'Smith')";

if ($conn->query($sql) === TRUE) {
    echo "New records created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>