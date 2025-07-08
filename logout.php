<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// ✅ Clear the token cookie
setcookie("token", "", [
    'expires' => time() - 3600, // Expire in the past
    'path' => '/',
    'httponly' => true,
    'secure' => false, // true if using HTTPS
    'samesite' => 'Lax',
]);

echo json_encode(["success" => true, "message" => "Logged out"]);
