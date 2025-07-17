<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connect to DB
$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed: " . $mysqli->connect_error]);
    exit();
}

// Read raw JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// 🔍 Debug: Check if JSON is valid
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON input",
        "raw_input" => $input  // 👈 DEBUG this in frontend/postman
    ]);
    exit();
}

// Optional: Debug to file (helpful for you during dev)
file_put_contents("debug_json.txt", print_r($data, true));

// Grab values from decoded JSON
$userId = $data["user_id"] ?? null;
$widgetName = $data["widget_name"] ?? null;
$jsonString = json_encode($data); // full JSON blob

// Validate required fields
if (!$userId || !$widgetName) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing user_id or widget_name"
    ]);
    exit();
}

// ✅ Insert into widgetjson table
$stmt = $mysqli->prepare("INSERT INTO widgetjson (user_id, widget_name, widget) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $mysqli->error]);
    exit();
}

$stmt->bind_param("iss", $userId, $widgetName, $jsonString);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget saved successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
