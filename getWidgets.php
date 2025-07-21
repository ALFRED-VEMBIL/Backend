<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "DB connection failed"
    ]);
    exit;
}

// 🔽 If ID is passed, fetch single widget
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM widgetjson WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $row['widget'] = json_decode($row['widget'], true);
        echo json_encode([
            "success" => true,
            "data" => $row
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Widget not found"
        ]);
    }

    $stmt->close();
} else {
    //  No ID → return all widgets
    $result = $mysqli->query("SELECT * FROM widgetjson ORDER BY id DESC");
    $widgets = [];

    while ($row = $result->fetch_assoc()) {
        $row['widget'] = json_decode($row['widget'], true);
        $widgets[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $widgets
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

$mysqli->close();
?>
