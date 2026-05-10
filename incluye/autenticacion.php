<?php
// ============================================================
// AUTENTICACIÓN Y SEGURIDAD
// ============================================================

// Configurar cookie de sesión de forma segura ANTES de session_start()
$esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

session_set_cookie_params([
    'lifetime' => 0,           // Cookie de sesión (expira al cerrar navegador)
    'path'     => '/',
    'domain'   => '',          // Dominio actual
    'secure'   => $esHttps,    // Solo HTTPS en Railway; funciona sin flag en HTTP local
    'httponly' => true,        // Inaccesible desde JavaScript
    'samesite' => 'Strict',    // Protección CSRF: nunca se envía en peticiones cross-site
]);

session_start();

// ── Cabeceras de seguridad HTTP ───────────────────────────────
function enviarCabecerasSeguridad(): void
{
    global $esHttps;

    // Evitar que la página sea embebida en iframes (clickjacking)
    header('X-Frame-Options: DENY');
    // Evitar MIME-sniffing
    header('X-Content-Type-Options: nosniff');
    // No enviar Referer a sitios externos
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Ocultar que el servidor usa PHP
    header_remove('X-Powered-By');
    // Política básica de contenido: solo recursos del mismo origen + Google Fonts + CDNs necesarios
    header("Content-Security-Policy: default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' https://fonts.gstatic.com; "
         . "img-src 'self' data:; "
         . "connect-src 'self';");
    // Deshabilitar APIs del navegador que no necesitamos
    header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");
    // HSTS: forzar HTTPS durante 1 año (solo si ya estamos en HTTPS)
    if ($esHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ── Base path ─────────────────────────────────────────────────
function getBase(): string
{
    $proyectoDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $htdocsDir   = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return rtrim(substr($proyectoDir, strlen($htdocsDir)), '/');
}

// ── Comprobaciones de sesión ──────────────────────────────────
function estaAutenticado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function requiereLogin(): void
{
    if (!estaAutenticado()) {
        header('Location: ' . getBase() . '/login.php');
        exit;
    }
}

function requiereRol(array|int $roles): void
{
    requiereLogin();
    if (!in_array($_SESSION['rol'], (array) $roles)) {
        header('Location: ' . getBase() . '/panel.php');
        exit;
    }
}

// ── Timeout de inactividad (30 minutos) ──────────────────────
function comprobarTimeout(int $minutos = 30): void
{
    if (!estaAutenticado()) return;

    $limite = $minutos * 60;
    $ahora  = time();

    if (isset($_SESSION['ultimo_acceso']) && ($ahora - $_SESSION['ultimo_acceso']) > $limite) {
        // Sesión expirada por inactividad
        session_unset();
        session_destroy();
        $base = getBase();
        header('Location: ' . $base . '/login.php?timeout=1');
        exit;
    }
    $_SESSION['ultimo_acceso'] = $ahora;
}

// ── Inicio de sesión seguro (llamar tras verificar credenciales) ──
function iniciarSesionSegura(array $datos): void
{
    // Regenerar ID de sesión para evitar Session Fixation
    session_regenerate_id(true);

    $_SESSION['usuario_id']      = $datos['id'];
    $_SESSION['usuario']         = $datos['usuario'];
    $_SESSION['nombre_completo'] = $datos['nombre_completo'];
    $_SESSION['rol']             = $datos['rol_id'];
    $_SESSION['rol_nombre']      = $datos['rol_nombre'];
    $_SESSION['ultimo_acceso']   = time();

    // Regenerar CSRF token al iniciar sesión
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Cierre de sesión completo ─────────────────────────────────
function cerrarSesion(): void
{
    // 1. Limpiar variables de sesión
    session_unset();

    // 2. Destruir datos del servidor
    session_destroy();

    // 3. Eliminar la cookie de sesión del navegador
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    header('Location: ' . getBase() . '/login.php');
    exit;
}

// ── Getters de sesión ─────────────────────────────────────────
function getNombreRol(): string
{
    return $_SESSION['rol_nombre'] ?? '';
}

function getUsuarioId(): ?int
{
    return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
}

function getNombreUsuario(): string
{
    return $_SESSION['nombre_completo'] ?? '';
}

// ── CSRF ─────────────────────────────────────────────────────
function generarCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Rate limiting (brute-force) ───────────────────────────────
/**
 * Comprueba si la IP o el usuario han superado el límite de intentos fallidos.
 * Devuelve true si está bloqueado, false si puede continuar.
 */
function estaBloqueado(string $usuario, mysqli $conn): bool
{
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ventana = date('Y-m-d H:i:s', time() - 900); // últimos 15 minutos
    $maxIntentos = 5;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS intentos
        FROM intentos_login
        WHERE (ip = ? OR usuario = ?) AND fecha > ?
    ");
    $stmt->bind_param('sss', $ip, $usuario, $ventana);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    return (int)($fila['intentos'] ?? 0) >= $maxIntentos;
}

/**
 * Registra un intento de login fallido en la tabla intentos_login.
 */
function registrarIntentoFallido(string $usuario, mysqli $conn): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $conn->prepare("INSERT INTO intentos_login (ip, usuario) VALUES (?, ?)");
    $stmt->bind_param('ss', $ip, $usuario);
    $stmt->execute();
}

/**
 * Limpia los intentos fallidos tras un login exitoso.
 */
function limpiarIntentos(string $usuario, mysqli $conn): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $conn->prepare("DELETE FROM intentos_login WHERE ip = ? OR usuario = ?");
    $stmt->bind_param('ss', $ip, $usuario);
    $stmt->execute();
}