<?php
// Clean output buffer and start fresh
ob_start();
ob_clean();

// Error reporting
ini_set('display_errors', 1); // Turn off in production
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// CORS & JSON header
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// JWT check
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

// DB connection
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

// Input parsing
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

// Field check
if (!isset($data['id'], $data['widget_name'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing widget ID or name"]);
    exit();
}

$id = intval($data['id']);
$widgetName = $conn->real_escape_string($data['widget_name']);

// Remove keys not to be stored in JSON
unset($data['id'], $data['widget_name']);
$widgetJson = $conn->real_escape_string(json_encode($data, JSON_UNESCAPED_UNICODE));

// Update query
$sql = "UPDATE widgetjson 
        SET widget_name = '$widgetName', widget = '$widgetJson', updated_at = CURRENT_TIMESTAMP 
        WHERE id = $id AND user_id = $userId";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Widget updated"]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "No widget updated (wrong ID or unauthorized)"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB Error: " . $conn->error]);
}

$conn->close();
exit();
?>
