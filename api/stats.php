<?php
// api/stats.php — Estadísticas generales (GET, requiere token)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../incluye/bd.php';

// Autenticación por Bearer token
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? apache_request_headers()['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $auth);

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token requerido']);
    exit;
}

$t = $conn->real_escape_string($token);
$res = $conn->query("SELECT at.usuario_id, u.nombre_completo, r.nombre AS rol FROM api_tokens at JOIN usuarios u ON at.usuario_id=u.id JOIN roles r ON u.rol_id=r.id WHERE at.token='$t' AND at.expira>NOW()");
$user = $res ? $res->fetch_assoc() : null;

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}

echo json_encode([
    'usuario' => $user['nombre_completo'],
    'rol' => $user['rol'],
    'estadisticas' => [
        'usuarios' => (int) $conn->query("SELECT COUNT(*) AS c FROM usuarios")->fetch_assoc()['c'],
        'cursos' => (int) $conn->query("SELECT COUNT(*) AS c FROM cursos WHERE estado='Activo'")->fetch_assoc()['c'],
        'matriculas' => (int) $conn->query("SELECT COUNT(*) AS c FROM matriculas")->fetch_assoc()['c'],
        'asignaturas' => (int) $conn->query("SELECT COUNT(*) AS c FROM asignaturas")->fetch_assoc()['c'],
        'media_notas' => round((float) $conn->query("SELECT AVG(nota_final) AS m FROM matriculas WHERE nota_final IS NOT NULL")->fetch_assoc()['m'], 2),
    ],
    'timestamp' => date('c'),
]);
