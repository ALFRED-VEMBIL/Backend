<?php
// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "jwt-utils.php";

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data["username"] ?? '');
$password = $data["password"] ?? '';

// Validation
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Username and password are required."]);
    exit;
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

// Find user
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Validate user
if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid username or password"]);
    exit;
}

// Generate JWT
$token = generate_jwt([
    "user_id" => $user["id"], 
    "email" => $user["username"], // Better to use DB email or username directly
    "iat" => time(),
    "exp" => time() + 3600,
]);

// Set HTTP-only cookie
setcookie("token", $token, [
    'expires' => time() + 3600,
    'path' => '/',
    'httponly' => true,
    'secure' => false, // Change to true on HTTPS
    'samesite' => 'Lax',
]);

// Respond
echo json_encode(["success" => true, "message" => "Login successful"]);
