<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "root", "", "college");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

// === NEW: Fetch full widget JSON if widget_id is provided ===
if (isset($_GET['widget_id'])) {
    $widgetId = intval($_GET['widget_id']);

    $stmt = $mysqli->prepare("SELECT id, user_id, widget_name, widget FROM widgetjson WHERE id = ?");
    $stmt->bind_param("i", $widgetId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $widgetData = json_decode($row['widget'], true);

        $response = array_merge([
            "id" => $row['id'],
            "user_id" => $row['user_id'],
            "widget_name" => $row['widget_name']
        ], $widgetData);

        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Widget not found"]);
    }

    $stmt->close();
    $mysqli->close();
    exit();
}

// === EXISTING: Fetch blog by ID ===
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM blogs WHERE id = $id LIMIT 1";
    $result = $mysqli->query($sql);
    $blogs = [];
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    echo json_encode($blogs);
    $mysqli->close();
    exit();
}

// === NEW: Search blogs by category ===
if (isset($_GET['category'])) {
    $category = $mysqli->real_escape_string($_GET['category']);
    $sql = "SELECT * FROM blogs WHERE category = '$category' LIMIT 10";
    $result = $mysqli->query($sql);
    $blogs = [];
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    echo json_encode($blogs);
    $mysqli->close();
    exit();
}

// === EXISTING: Fuzzy search for blog by title ===
$search = isset($_GET['q']) ? $mysqli->real_escape_string($_GET['q']) : '';

if ($search !== '') {
    $sql = "SELECT * FROM blogs WHERE title LIKE '%$search%' LIMIT 10";
    $result = $mysqli->query($sql);

    $blogs = [];
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }

    echo json_encode($blogs);
} else {
    echo json_encode([]);
}

$mysqli->close();
?>
