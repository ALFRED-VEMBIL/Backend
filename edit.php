<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($input['id']) || !isset($input['widget_name'])) {
    echo json_encode(["success" => false, "error" => "Missing ID or widget_name"]);
    exit;
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection Failed"]);
    exit;
}

$id = (int)$input['id'];
$widgetName = $conn->real_escape_string($input['widget_name']);

// ✅ Encode the full input payload
$widgetJson = $conn->real_escape_string(json_encode($input));

// Run the update query
$sql = "UPDATE widgetjson 
        SET widget_name = '$widgetName', widget = '$widgetJson', updated_at = CURRENT_TIMESTAMP 
        WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
