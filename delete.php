<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// connect to DB
$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed: " . $mysqli->connect_error]);
    exit();
}

// get POST data
$id = $_POST["id"] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "error" => "Missing widget ID"]);
    exit();
}

// delete from DB
$stmt = $mysqli->prepare("DELETE FROM Widgets WHERE id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $mysqli->error]);
    exit();
}
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget deleted"]);
} else {
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
    