<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ['id', 'widget_name', 'feed_url', 'layout', 'sublayout', 'width_mode', 'width_value', 'height_mode', 'height_value'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(["success" => false, "error" => "Missing field: $field"]);
        exit;
    }
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "feedspotclone");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection Failed"]);
    exit;
}

// Escape inputs
$id = $conn->real_escape_string($input['id']);
$widget_name = $conn->real_escape_string($input['widget_name']);
$feed_url = $conn->real_escape_string($input['feed_url']);
$layout = $conn->real_escape_string($input['layout']);
$sublayout = $conn->real_escape_string($input['sublayout']);
$width_mode = $conn->real_escape_string($input['width_mode']);
$width_value = (int)$input['width_value'];
$height_mode = $conn->real_escape_string($input['height_mode']);
$height_value = (int)$input['height_value'];

// Update query
$sql = "UPDATE widgetjson SET 
  widget_name = '$widget_name',
  feed_url = '$feed_url',
  layout = '$layout',
  sublayout = '$sublayout',
  width_mode = '$width_mode',
  width_value = $width_value,
  height_mode = '$height_mode',
  height_value = $height_value
WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
