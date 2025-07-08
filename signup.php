<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$input = json_decode(file_get_contents("php://input"), true);
$username = $input["username"] ?? '';
$password = $input["password"] ?? '';

if (!$username || !$password) {
  echo json_encode(["success" => false, "error" => "Missing username or password"]);
  exit;
}

$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["success" => false, "error" => "Database connection failed"]);
  exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed);
if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => "User may already exist"]);
}
$stmt->close();
$conn->close();
?>
