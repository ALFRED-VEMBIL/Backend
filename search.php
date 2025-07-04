<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "root", "", "college");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

// filter by ID if provided
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

// otherwise fall back to fuzzy search
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
