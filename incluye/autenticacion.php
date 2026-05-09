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
    'samesite' => 'Lax',       // Protección CSRF básica
]);

session_start();

// ── Cabeceras de seguridad HTTP ───────────────────────────────
function enviarCabecerasSeguridad(): void
{
    // Evitar que la página sea embebida en iframes (clickjacking)
    header('X-Frame-Options: DENY');
    // Evitar MIME-sniffing
    header('X-Content-Type-Options: nosniff');
    // No enviar Referer a sitios externos
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Política básica de contenido: solo recursos del mismo origen + Google Fonts + CDNs necesarios
    header("Content-Security-Policy: default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' https://fonts.gstatic.com; "
         . "img-src 'self' data:; "
         . "connect-src 'self';");
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