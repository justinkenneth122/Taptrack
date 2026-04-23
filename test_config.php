<?php
$config = require_once __DIR__ . '/config/config.php';

echo "<pre>";
echo "Database Config:\n";
echo "Host: " . ($config['db']['host'] ?? 'NOT SET') . "\n";
echo "Name: " . ($config['db']['name'] ?? 'NOT SET') . "\n";
echo "User: " . ($config['db']['user'] ?? 'NOT SET') . "\n";
echo "Pass: " . (empty($config['db']['pass']) ? '(empty)' : '(set)') . "\n";
echo "</pre>";
?>
