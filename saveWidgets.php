<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


header("Access-Control-Allow-Origin: http://localhost:3000");

header("Access-Control-Allow-Origin: *"); // allow any origin — use "*" only for development
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request (OPTIONS), exit early
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}



// connect to DB
$mysqli = new mysqli("localhost", "root", "", "college");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"DB connection failed: " . $mysqli->connect_error]);
    exit();
}

// get POST data from FormData
$widgetName = $_POST["widget_name"] ?? null;
$feedUrl = $_POST["feed_url"] ?? null;
$layout = $_POST["layout"] ?? null;
$sublayout = $_POST["sublayout"] ?? null;
$widthMode = $_POST["width_mode"] ?? null;
$widthValue = $_POST["width_value"] ?? null;
$heightMode = $_POST["height_mode"] ?? null;
$heightValue = $_POST["height_value"] ?? null;

// debug to file
file_put_contents("debug_post.txt", print_r($_POST, true));

// validation
if (
    !$widgetName || !$feedUrl || !$layout || !$sublayout ||
    !$widthMode || !$widthValue || !$heightMode || !$heightValue
) {
    echo json_encode([
        "success"=>false,
        "error"=>"Missing fields",
        "debug"=>$_POST
    ]);
    exit();
}

// insert
$stmt = $mysqli->prepare("
    INSERT INTO Widgets
    (widget_name, feed_url, layout, sublayout, width_mode, width_value, height_mode, height_value)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(["success"=>false, "error"=>"Prepare failed: " . $mysqli->error]);
    exit();
}
$stmt->bind_param(
    "ssssssis",
    $widgetName,
    $feedUrl,
    $layout,
    $sublayout,
    $widthMode,
    $widthValue,
    $heightMode,
    $heightValue
);

if ($stmt->execute()) {
    echo json_encode(["success"=>true,"message"=>"Widget saved"]);
} else {
    echo json_encode(["success"=>false,"error"=>"Execute failed: " . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>
