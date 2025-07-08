<?php
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_SECRET = "your_secret_key_here"; // replace with env-based secret

function generate_jwt($payload) {
    global $JWT_SECRET;
    return JWT::encode($payload, $JWT_SECRET, 'HS256');
}

function verify_jwt($token) {
    global $JWT_SECRET;
    try {
        return JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        return false;
    }
}
?>
