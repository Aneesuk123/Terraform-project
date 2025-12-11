<?php
// db.php - central DB connection file for all API scripts
// Reads credentials from environment variables so it works with Azure App Settings.

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Environment / app settings (Azure App Settings)
$DB_HOST     = getenv('DATABASE_HOST') ?: 'terraform-mysql-server00.mysql.database.azure.com';
$DB_USER     = getenv('DATABASE_USER') ?: 'mysqladmin@terraform-mysql-server00';
$DB_PASSWORD = getenv('DATABASE_PASSWORD') ?: 'Str0ngP@ssword2025!';
$DB_NAME     = getenv('DATABASE_NAME') ?: 'restaurantdb';
$DB_PORT     = getenv('DATABASE_PORT') ?: 3306;

// If user omitted server suffix, append servername (helps if someone sets "mysqladmin")
if (strpos($DB_USER, '@') === false) {
    // Extract server short name from host (left of first dot)
    $hostShort = explode('.', $DB_HOST)[0];
    $DB_USER = $DB_USER . '@' . $hostShort;
}

try {
    // Use mysqli constructor
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, (int)$DB_PORT);

    // Optional: set charset
    $conn->set_charset('utf8mb4');

    // Informational echo when opening directly (safe for debugging)
    // Remove or comment out in production.
    // echo "Loading DB...<br>SUCCESS!";

} catch (mysqli_sql_exception $e) {
    // If this file is included by API endpoints, return JSON error when appropriate
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'DB connection failed',
        'message' => $e->getMessage()
    ]);
    // Stop execution so APIs don't continue with undefined $conn
    exit;
}
?>
