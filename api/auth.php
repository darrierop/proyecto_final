<?php
// api/auth.php — Autenticación REST → devuelve token
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../incluye/bd.php';

$data = json_decode(file_get_contents('php://input'), true);
$usuario = trim($data['usuario'] ?? '');
$password = $data['password'] ?? '';

if (!$usuario || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan credenciales']);
    exit;
}

$u = $conn->real_escape_string($usuario);
$res = $conn->query("SELECT u.id, u.password, u.nombre_completo, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id=r.id WHERE u.usuario='$u'");
$fila = $res ? $res->fetch_assoc() : null;

if (!$fila || !password_verify($password, $fila['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales incorrectas']);
    exit;
}

// Generar token
$token = bin2hex(random_bytes(32));
$uid = (int) $fila['id'];
$conn->query("DELETE FROM api_tokens WHERE usuario_id=$uid"); // un token activo por usuario
$conn->query("INSERT INTO api_tokens (usuario_id, token) VALUES ($uid, '$token')");

echo json_encode([
    'token' => $token,
    'usuario' => $fila['nombre_completo'],
    'rol' => $fila['rol'],
    'expira' => date('Y-m-d H:i:s', strtotime('+30 days')),
]);
