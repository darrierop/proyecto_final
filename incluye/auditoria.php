<?php
// incluye/auditoria.php — Registro de auditoría/historial de cambios

/**
 * Registra una acción en la tabla auditoria.
 * Usa Prepared Statements para evitar inyección SQL.
 */
function registrarAuditoria(
    mysqli $conn,
    string $accion,
    string $tabla,
    ?int $registroId,
    string $detalle = ''
): void {
    $userId = (int)($_SESSION['usuario_id'] ?? 0);
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO auditoria (usuario_id, accion, tabla_afectada, registro_id, detalle, ip)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('issiis', $userId, $accion, $tabla, $registroId, $detalle, $ip);
    $stmt->execute();
}

/**
 * Registra un intento de login (exitoso o fallido) en la auditoría.
 */
function registrarLogin(mysqli $conn, string $usuario, bool $exitoso): void
{
    $accion  = $exitoso ? 'LOGIN' : 'LOGIN_ERR';
    $detalle = 'Usuario: ' . $usuario;
    registrarAuditoria($conn, $accion, 'usuarios', null, $detalle);
}
