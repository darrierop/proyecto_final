<?php
// ============================================================
// CONEXIÓN A BASE DE DATOS
// Lee las credenciales desde el archivo .env de la raíz.
// No necesitas tocar este archivo — edita solo el .env
// ============================================================

function cargarEnv()
{
    $rutas = [
        __DIR__ . '/../.env',
        __DIR__ . '/../../.env',
        __DIR__ . '/.env',
    ];
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lineas as $linea) {
                if (str_starts_with(trim($linea), '#'))
                    continue;
                if (!str_contains($linea, '='))
                    continue;
                [$clave, $valor] = explode('=', $linea, 2);
                $_ENV[trim($clave)] = trim($valor);
            }
            return true;
        }
    }
    return false;
}

cargarEnv();

// Soporta .env local Y variables nativas de Railway
$host     = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?? getenv('MYSQLHOST')     ?? 'localhost';
$puerto   = (int)($_ENV['DB_PORT']     ?? getenv('DB_PORT')     ?? getenv('MYSQLPORT')     ?? 3306);
$usuario  = $_ENV['DB_USUARIO']  ?? getenv('DB_USUARIO')  ?? getenv('MYSQLUSER')     ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? getenv('MYSQLPASSWORD') ?? '';
$nombre   = $_ENV['DB_NOMBRE']   ?? getenv('DB_NOMBRE')   ?? getenv('MYSQLDATABASE') ?? 'sistemaacademico';

$conn = new mysqli($host, $usuario, $password, $nombre, $puerto);

if ($conn->connect_error) {
    $esEntornoLocal = (
        str_contains(strtolower($_SERVER['DOCUMENT_ROOT'] ?? ''), 'htdocs') ||
        str_contains(strtolower($_SERVER['DOCUMENT_ROOT'] ?? ''), 'www') ||
        ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production') === 'local'
    );
    $mensajePublico = 'No se pudo conectar con la base de datos. Contacte con el administrador del sistema.';
    $mensajeTecnico = $conn->connect_error;
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Error de conexión</title>
        <link rel="stylesheet"
            href="<?= rtrim(substr(str_replace('\\', '/', realpath(__DIR__ . '/..')), strlen(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])))), '/') ?>/estilos/global.css">
        <style>
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh
            }

            .caja {
                background: var(--card);
                border: 1px solid var(--border);
                border-top: 3px solid var(--danger);
                border-radius: 16px;
                padding: 40px 48px;
                max-width: 520px;
                text-align: center
            }

            .caja h2 {
                font-family: 'Playfair Display', serif;
                color: var(--danger);
                margin-bottom: 10px
            }

            .caja p {
                color: var(--muted);
                font-size: .9rem;
                line-height: 1.6;
                margin-bottom: 12px
            }

            .tecnico {
                background: var(--bg);
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 10px 14px;
                font-family: monospace;
                font-size: .8rem;
                color: var(--danger);
                text-align: left;
                margin: 16px 0
            }

            .pasos {
                text-align: left;
                background: var(--bg);
                border-radius: 10px;
                padding: 16px 20px
            }

            .pasos li {
                color: var(--muted);
                font-size: .85rem;
                margin-bottom: 6px
            }

            .pasos code {
                background: var(--border);
                padding: 1px 6px;
                border-radius: 4px;
                font-size: .8rem;
                color: var(--accent)
            }
        </style>
    </head>

    <body>
        <div class="caja">
            <div style="font-size:3rem;margin-bottom:16px">🔌</div>
            <h2>No se pudo conectar a la base de datos</h2>
            <?php if ($esEntornoLocal): ?>
                <p>Revisa el archivo <strong>.env</strong> en la raíz del proyecto.</p>
                <div class="tecnico"><?= htmlspecialchars($mensajeTecnico) ?></div>
                <ul class="pasos">
                    <li>Abre el archivo <code>.env</code> en la raíz del proyecto</li>
                    <li>Comprueba <code>DB_USUARIO</code> y <code>DB_PASSWORD</code></li>
                    <li>En XAMPP el usuario es <code>root</code> con contraseña <strong>vacía</strong></li>
                    <li>Asegúrate de que MySQL está arrancado en el panel de XAMPP</li>
                    <li>Verifica que la base de datos <code>sistemaacademico</code> existe en phpMyAdmin</li>
                </ul>
            <?php else: ?>
                <p><?= htmlspecialchars($mensajePublico) ?></p>
            <?php endif; ?>
        </div>
    </body>

    </html>
    <?php
    exit;
}


$conn->set_charset('utf8mb4');

// ── Auto-migración: tabla de intentos de login (rate limiting) ──
// Se crea sola si no existe; TablePlus la verá automáticamente.
$conn->query("
    CREATE TABLE IF NOT EXISTS intentos_login (
        id       INT AUTO_INCREMENT PRIMARY KEY,
        ip       VARCHAR(45)  NOT NULL,
        usuario  VARCHAR(100) NOT NULL,
        fecha    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_fecha      (ip, fecha),
        INDEX idx_usuario_fecha (usuario, fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

