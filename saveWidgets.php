<?php
ob_start();
ob_clean();

ini_set('display_errors', 1); // Turn off in production
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include JWT decoder
require_once "jwt-utils.php";

if (!isset($_COOKIE['token'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Token missing"]);
    exit();
}

$decoded = decodeJWT($_COOKIE['token']);
if (!$decoded || !isset($decoded['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    exit();
}

$userId = intval($decoded['user_id']);

// Database
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

$input = file_get_contents("php://input");
if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Empty request body"]);
    exit();
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit();
}

if (!isset($data['widget_name'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing widget name"]);
    exit();
}

$widgetName = $conn->real_escape_string($data['widget_name']);
unset($data['widget_name']); // Remove name from main widget JSON

$widgetJson = $conn->real_escape_string(json_encode($data, JSON_UNESCAPED_UNICODE));

// Insert new widget
$sql = "INSERT INTO widgetjson (user_id, widget_name, widget) VALUES ($userId, '$widgetName', '$widgetJson')";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "id" => $conn->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
exit();
?>
