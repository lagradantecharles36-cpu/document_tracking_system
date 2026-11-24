<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*
 * LOCAL XAMPP SETTINGS:
 * host=localhost, username=root, password="", database=document_tracking_db
 *
 * INFINITYFREE:
 * Replace these values with the exact MySQL credentials shown in
 * your InfinityFree Control Panel > MySQL Databases.
 */
$db_host = "sql210.infinityfree.com";
$db_user = "if0_43483380";
$db_pass = "zidoy123";
$db_name = "if0_43483380_document_db";
$db_port = 3306;

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit("Database connection failed. Check db_connect.php credentials and confirm MySQL is running.");
}
