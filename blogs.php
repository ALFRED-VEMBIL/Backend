<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}


// connect to MySQL
$mysqli = new mysqli("localhost", "root", "", "college");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

// get all blogs, including the new `image` column
$sql = "SELECT id, title, category, url, image FROM blogs";
$result = $mysqli->query($sql);

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

echo json_encode($blogs);

$mysqli->close();
?>
