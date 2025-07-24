<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "college");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data["user_id"], $data["widget_name"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$userId = $data["user_id"];
$widgetName = $data["widget_name"];
$jsonString = json_encode($data);

// Detect if this is an update or insert
if (isset($data["id"])) {
    // UPDATE existing widget
    $widgetId = intval($data["id"]);
    $stmt = $mysqli->prepare("UPDATE widgetjson SET widget_name = ?, widget = ? WHERE id = ?");
    $stmt->bind_param("ssi", $widgetName, $jsonString, $widgetId);
} else {
    // INSERT new widget
    $stmt = $mysqli->prepare("INSERT INTO widgetjson (user_id, widget_name, widget) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $widgetName, $jsonString);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget saved"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Save failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
