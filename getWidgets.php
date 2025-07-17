<?php
// CORS and headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

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

// ✅ Use the `id` parameter if present
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // sanitize input
    $stmt = $mysqli->prepare("SELECT * FROM Widgets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $widget = $result->fetch_assoc();
    
    echo json_encode([
        "success" => true,
        "data" => $widget
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else {
    // fallback: return all widgets
    $result = $mysqli->query("SELECT * FROM Widgets ORDER BY id DESC");
    $widgets = [];
    while ($row = $result->fetch_assoc()) {
        $widgets[] = $row;
    }

    echo json_encode([
        "success"=>true,
        "data"=>$widgets
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

$mysqli->close();
?>
