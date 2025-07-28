<?php
// ✅ Enable error reporting for debugging (disable in production)


ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_clean(); // clears any previous output (like BOM or PHP warnings)

// ✅ Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ JWT verification
require_once 'jwt_utils.php'; // Must return ['user_id' => ..., 'exp' => ...] or false

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

// ✅ Database connection
$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

// ✅ Read and decode JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data["widget_name"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing widget_name"]);
    exit();
}

$widgetName = $data["widget_name"];
$widgetData = $data;

// ✅ Remove metadata fields from widget JSON
unset($widgetData['id'], $widgetData['widget_name']);
$jsonString = json_encode($widgetData, JSON_UNESCAPED_UNICODE);

// ✅ Insert or Update logic
if (isset($data["id"])) {
    $widgetId = intval($data["id"]);
    $stmt = $mysqli->prepare("UPDATE widgetjson SET widget_name = ?, widget = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $widgetName, $jsonString, $widgetId, $userId);
    $action = "updated";
} else {
    $stmt = $mysqli->prepare("INSERT INTO widgetjson (user_id, widget_name, widget) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $widgetName, $jsonString);
    $action = "created";
}

// ✅ Execute statement
if ($stmt->execute()) {
    $response = ["success" => true, "message" => "Widget $action"];
    if (!isset($data["id"])) {
        $response["id"] = $stmt->insert_id;
    }
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
