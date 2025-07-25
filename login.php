
<?php
// CORS preflight headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// 🔁 Respond to preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "jwt-utils.php";



// ✅ Read JSON input from frontend
$data = json_decode(file_get_contents("php://input"), true);
$username = $data["username"] ?? '';
$password = $data["password"] ?? '';

// ✅ Connect to DB
$conn = new mysqli("localhost", "root", "", "college");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

// ✅ Find user by username
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Validate credentials
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit;
}

if (!password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Incorrect password"]);
    exit;
}

// ✅ Create JWT token
$token = generate_jwt([
    "email" => $username,
    "iat" => time(),
    "exp" => time() + 3600,
]);

// ✅ Set cookie
setcookie("token", $token, [
    'expires' => time() + 3600,
    'path' => '/',
    'httponly' => true,
    'secure' => false, // Set to true if using HTTPS
    'samesite' => 'Lax',
]);

echo json_encode(["success" => true]);
?>