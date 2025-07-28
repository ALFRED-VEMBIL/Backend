<?php
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ✅ SECRET SHOULD BE SAME EVERYWHERE
$JWT_SECRET = "secret123";

function generate_jwt($payload) {
    global $JWT_SECRET;
    return JWT::encode($payload, $JWT_SECRET, 'HS256');
}

function decodeJWT($token) {
    global $JWT_SECRET;
    try {
        return (array) JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        // Optional: Log error for debugging (but don't expose it to frontend in production)
        error_log("JWT decode error: " . $e->getMessage());
        return null;
    }
}
