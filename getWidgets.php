<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");  // <-- important

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success"=>false,
        "error"=>"DB connection failed"
    ]);
    exit;
}

$result = $mysqli->query("SELECT * FROM Widgets ORDER BY id DESC");
$widgets = [];
while ($row = $result->fetch_assoc()) {
    $widgets[] = $row;
}

echo json_encode([
    "success"=>true,
    "data"=>$widgets
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

$mysqli->close();
?>
