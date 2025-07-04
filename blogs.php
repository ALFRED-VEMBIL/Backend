<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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
