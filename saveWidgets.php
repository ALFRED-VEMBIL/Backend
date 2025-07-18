<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === CORS HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// === HANDLE OPTIONS PRE-FLIGHT ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === CONNECT TO DATABASE ===
$mysqli = new mysqli("localhost", "root", "", "college");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

// === READ AND DECODE JSON ===
$input = file_get_contents("php://input");
file_put_contents("debug_input.json", $input); // For debugging

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON input",
        "raw_input" => $input,
        "json_error" => json_last_error_msg()
    ]);
    exit();
}

// === VALIDATE REQUIRED FIELDS ===
$userId = $data["user_id"] ?? null;
$widgetName = $data["widget_name"] ?? null;

if (!$userId || !$widgetName) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing user_id or widget_name"
    ]);
    exit();
}

// === PREPARE AND INSERT INTO DATABASE ===
$jsonString = json_encode($data); // Save the entire widget config as JSON

$stmt = $mysqli->prepare("INSERT INTO widgetjson (user_id, widget_name, widget) VALUES (?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $mysqli->error]);
    exit();
}

$stmt->bind_param("iss", $userId, $widgetName, $jsonString);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget saved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
