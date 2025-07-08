<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// OPTIONAL: Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

require_once "jwt-utils.php";
header("Content-Type: application/json");

if (!isset($_COOKIE["token"])) {
    echo json_encode(["authenticated" => false]);
    exit;
}

$decoded = verify_jwt($_COOKIE["token"]);
if ($decoded) {
    echo json_encode(["authenticated" => true, "user" => $decoded->email]);

} else {
    echo json_encode(["authenticated" => false]);
}
?>
