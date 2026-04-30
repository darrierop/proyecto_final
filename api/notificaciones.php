<?php
// api/notificaciones.php — Endpoint JSON para notificaciones en tiempo real
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../incluye/bd.php';

$userId = (int) $_SESSION['usuario_id'];

// Mensajes sin leer
$sinLeer = 0;
$r = $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE destinatario_id=$userId AND leido=0");
if ($r)
    $sinLeer = (int) $r->fetch_assoc()['c'];

// Últimos mensajes nuevos (para mostrar en el toast)
$nuevos = [];
$desde = $_GET['desde'] ?? date('Y-m-d H:i:s', strtotime('-60 seconds'));
$desde = $conn->real_escape_string($desde);
$rNuevos = $conn->query("
    SELECT m.id, m.asunto, u.nombre_completo AS remitente, m.fecha_envio
    FROM mensajes m
    JOIN usuarios u ON m.remitente_id = u.id
    WHERE m.destinatario_id = $userId AND m.leido = 0 AND m.fecha_envio > '$desde'
    ORDER BY m.fecha_envio DESC LIMIT 3
");
if ($rNuevos) {
    while ($n = $rNuevos->fetch_assoc())
        $nuevos[] = $n;
}

echo json_encode([
    'sin_leer' => $sinLeer,
    'nuevos' => $nuevos,
    'hora' => date('H:i:s'),
]);
