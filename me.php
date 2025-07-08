<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$token = $_COOKIE["token"] ?? null;

if (!$token) {
  echo json_encode(["authenticated" => false]);
  exit;
}

try {
  $decoded = JWT::decode($token, new Key("secret123", 'HS256'));
  echo json_encode(["authenticated" => true, "user" => $decoded->name]);
} catch (Exception $e) {
  echo json_encode(["authenticated" => false, "error" => "Invalid token"]);
}
?>
