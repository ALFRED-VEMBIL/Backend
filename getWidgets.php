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

// DB Connection
$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

require_once("jwt-utils.php"); // Ensure this file has decodeJWT()

// Get user_id from token if available
function getUserIdFromToken() {
    if (!isset($_COOKIE['token'])) return null;
    $payload = decodeJWT($_COOKIE['token']);
    return $payload['user_id'] ?? null;
}

$userId = getUserIdFromToken();

// Case 1: Fetch widget by specific ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM widgetjson WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $widgetData = json_decode($row['widget'], true);
        $row['widget'] = $widgetData;
        $row['feed_url'] = $widgetData['feed_url'] ?? ''; // ✅ Add this line

        echo json_encode(["success" => true, "data" => $row], JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode(["success" => false, "error" => "Widget not found"]);
    }

    $stmt->close();
}

// Case 2: Fetch all widgets for logged-in user
elseif ($userId) {
    $stmt = $mysqli->prepare("SELECT * FROM widgetjson WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $widgets = [];
    while ($row = $result->fetch_assoc()) {
        $widgetData = json_decode($row['widget'], true);
        $row['widget'] = $widgetData;
        $row['feed_url'] = $widgetData['feed_url'] ?? ''; // ✅ Add this line
        $widgets[] = $row;
    }

    echo json_encode(["success" => true, "data" => $widgets], JSON_UNESCAPED_SLASHES);
    $stmt->close();
}


// Case 3: Unauthorized access (no token, no ID)
else {
    echo json_encode(["success" => false, "error" => "Unauthorized: No token or ID provided"]);
}

$mysqli->close();
?>
