<?php
// Database Setup Script
// Run this file once to create the database and import the schema

$servername = "localhost";
$username = "root";
$password = "";

echo "<h2>DormPilot Database Setup</h2>";

try {
    // Connect to MySQL server (without database)
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $dbname = "dormpilot";
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✓ Database '$dbname' created or already exists</p>";
    } else {
        echo "<p>✗ Error creating database: " . $conn->error . "</p>";
        exit();
    }
    
    // Select the database
    $conn->select_db($dbname);
    echo "<p>✓ Selected database '$dbname'</p>";
    
    // Read and execute SQL file
    $sql_file = "dormpilot.sql";
    
    if (!file_exists($sql_file)) {
        die("<p>✗ SQL file '$sql_file' not found!</p>");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Remove SQL comments
    $sql_content = preg_replace('/--.*$/m', '', $sql_content);
    $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
    
    // Execute the SQL file using multi_query for better handling
    $success = true;
    
    // Split by semicolon but preserve multi-line statements
    $queries = array();
    $current_query = '';
    $lines = explode("\n", $sql_content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 2) == '--') {
            continue;
        }
        $current_query .= $line . "\n";
        if (substr(rtrim($line), -1) == ';') {
            $query = trim($current_query);
            if (strlen($query) > 10) {
                $queries[] = $query;
            }
            $current_query = '';
        }
    }
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query) || strlen($query) < 10) {
            continue;
        }
        
        // Skip SET commands and transaction commands
        if (stripos($query, 'SET SQL_MODE') !== false || 
            stripos($query, 'START TRANSACTION') !== false ||
            stripos($query, 'COMMIT') !== false ||
            stripos($query, 'SET time_zone') !== false ||
            stripos($query, 'SET @OLD') !== false ||
            stripos($query, 'SET NAMES') !== false ||
            stripos($query, 'SET CHARACTER_SET') !== false) {
            continue;
        }
        
        if ($conn->query($query) === TRUE) {
            $success_count++;
        } else {
            // Ignore "already exists" and "duplicate" errors
            $error_msg = $conn->error;
            if (stripos($error_msg, 'already exists') === false && 
                stripos($error_msg, 'Duplicate') === false &&
                stripos($error_msg, 'Unknown database') === false) {
                echo "<p style='color:orange;'>⚠ Query warning: " . htmlspecialchars(substr($error_msg, 0, 150)) . "...</p>";
                $error_count++;
            }
        }
    }
    
    echo "<p>✓ Executed SQL queries (Success: $success_count, Warnings: $error_count)</p>";
    
    // Verify tables were created
    $tables = $conn->query("SHOW TABLES");
    $table_count = $tables->num_rows;
    
    echo "<p>✓ Found $table_count tables in database</p>";
    
    echo "<h3 style='color:green;'>✓ Database setup completed successfully!</h3>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "<p><strong>Note:</strong> You can delete this file (setup_database.php) after setup is complete.</p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #333;
        }
        p {
            margin: 10px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }
    </style>
</head>
<body>
</body>
</html>

