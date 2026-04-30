<?php
// incluye/auditoria.php — Registro de auditoría/historial de cambios

/**
 * Registra una acción en la tabla auditoria.
 */
function registrarAuditoria(
    mysqli $conn,
    string $accion,
    string $tabla,
    ?int $registroId,
    string $detalle = ''
): void {
    $userId = $_SESSION['usuario_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $detalle = $conn->real_escape_string($detalle);
    $tabla = $conn->real_escape_string($tabla);
    $accion = $conn->real_escape_string($accion);
    $ip = $conn->real_escape_string($ip);
    $conn->query("
        INSERT INTO auditoria (usuario_id, accion, tabla_afectada, registro_id, detalle, ip)
        VALUES ($userId, '$accion', '$tabla', " . ($registroId ?? 'NULL') . ", '$detalle', '$ip')
    ");
}
