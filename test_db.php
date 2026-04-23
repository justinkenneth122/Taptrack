<?php
// Test database connection

$DB_HOST = 'localhost';
$DB_NAME = 'taptrack';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$DB_DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

echo "<h1>Database Connection Test</h1>";
echo "<p>DSN: " . htmlspecialchars($DB_DSN) . "</p>";
echo "<p>User: $DB_USER</p>";
echo "<p>Host: $DB_HOST</p>";
echo "<p>Database: $DB_NAME</p>";

try {
    $pdo = new PDO(
        $DB_DSN,
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "<h2 style='color: green;'>✅ Connection Successful!</h2>";
    
    // Check tables
    echo "<h3>Tables in database:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . current($table) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Connection Failed!</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
}
?>
