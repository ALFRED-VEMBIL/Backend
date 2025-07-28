<?php
require_once 'vendor/autoload.php';
require_once 'jwt-utils.php'; // ✅ Include shared secret + decodeJWT()

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ✅ CORS + cookies
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// ✅ Get token from cookie
$token = $_COOKIE['token'] ?? '';

if (!$token) {
    echo json_encode(["success" => false, "error" => "Token missing"]);
    exit;
}

// ✅ Decode token using the same secret from jwt-utils.php
$decoded = decodeJWT($token);

// ✅ Return user_id if valid
if (!$decoded || !isset($decoded['user_id'])) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user_id" => $decoded['user_id']
]);
