<?php
require_once "jwt-utils.php"; // Make sure this file exists and has decodeJWT()

// CORS & Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Check if token is present in cookie
if (!isset($_COOKIE['token'])) {
    echo json_encode(["success" => false, "error" => "Token missing"]);
    exit;
}

// ✅ Decode JWT token to get user ID
$userId = decodeJWT($_COOKIE['token']);
if (!$userId) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid token",
        "token" => $_COOKIE['token']
    ]);
    exit;
}

// ✅ Read JSON body input
$input = json_decode(file_get_contents("php://input"), true);

// ✅ Validate required fields
if (!isset($input['id']) || !isset($input['widget_name'])) {
    echo json_encode(["success" => false, "error" => "Missing ID or widget_name"]);
    exit;
}

// ✅ DB connection
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection Failed"]);
    exit;
}

$id = (int)$input['id'];
$widgetName = $conn->real_escape_string($input['widget_name']);

// 🔁 Remove id & widget_name from widget data before saving
$widgetData = $input;
unset($widgetData['id'], $widgetData['widget_name']);

$widgetJson = $conn->real_escape_string(json_encode($widgetData));

// ✅ Only update if user owns the widget
$sql = "UPDATE widgetjson 
        SET widget_name = '$widgetName', widget = '$widgetJson', updated_at = CURRENT_TIMESTAMP 
        WHERE id = $id AND user_id = $userId";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "No widget updated (wrong ID or unauthorized)"]);
    }
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
