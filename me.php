<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require 'vendor/autoload.php';

// CORS Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

// Preflight check
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get token from cookie
$token = $_COOKIE["token"] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["authenticated" => false, "error" => "No token found"]);
    exit;
}

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key("secret123", 'HS256'));

    echo json_encode([
        "authenticated" => true,
        "user" => [
            "id" => $decoded->user_id,
            "email" => $decoded->email
            
        ]
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        "authenticated" => false,
        "error" => "Invalid token",
        "message" => $e->getMessage() // Debug only. Remove in production.
    ]);
}
?>
