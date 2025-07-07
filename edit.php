<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// Handle preflight request
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

// Get POST data
$id = $_POST["id"] ?? null;
$widgetName = $_POST["widget_name"] ?? null;
$feedUrl = $_POST["feed_url"] ?? null;
$layout = $_POST["layout"] ?? null;
$sublayout = $_POST["sublayout"] ?? null;
$widthMode = $_POST["width_mode"] ?? null;
$widthValue = $_POST["width_value"] ?? null;
$heightMode = $_POST["height_mode"] ?? null;
$heightValue = $_POST["height_value"] ?? null;

// Validation
if (
    !$id || !$widgetName || !$feedUrl || !$layout || !$sublayout ||
    !$widthMode || !$widthValue || !$heightMode || !$heightValue
) {
    echo json_encode([
        "success" => false,
        "error" => "Missing fields",
        "debug" => $_POST
    ]);
    exit();
}

// Update statement
$stmt = $mysqli->prepare("
    UPDATE Widgets
    SET widget_name = ?, feed_url = ?, layout = ?, sublayout = ?,
        width_mode = ?, width_value = ?, height_mode = ?, height_value = ?
    WHERE id = ?
");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $mysqli->error]);
    exit();
}

$stmt->bind_param(
    "ssssssisi",
    $widgetName,
    $feedUrl,
    $layout,
    $sublayout,
    $widthMode,
    $widthValue,
    $heightMode,
    $heightValue,
    $id
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated"]);
} else {
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
