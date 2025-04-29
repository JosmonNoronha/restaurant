<?php
require_once __DIR__ . '/../../config/database.php';

// Get raw JSON data
$db_file = __DIR__ . '/../../database/reservations.json';
$raw_data = file_get_contents($db_file);

// Display raw data
echo "<pre>";
echo htmlspecialchars($raw_data);
echo "</pre>";
?> 