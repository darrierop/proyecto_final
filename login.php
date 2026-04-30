<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: panel.php');
    exit;
}

require_once 'incluye/bd.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario && $password) {
        $stmt = $conn->prepare("
            SELECT u.id, u.usuario, u.password, u.nombre_completo, u.rol_id, r.nombre AS rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE u.usuario = ?
        ");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($fila = $res->fetch_assoc()) {
            if (password_verify($password, $fila['password'])) {
                $_SESSION['usuario_id']      = $fila['id'];
                $_SESSION['usuario']         = $fila['usuario'];
                $_SESSION['nombre_completo'] = $fila['nombre_completo'];
                $_SESSION['rol']             = $fila['rol_id'];
                $_SESSION['rol_nombre']      = $fila['rol_nombre'];
                header('Location: panel.php');
                exit;
            }
        }
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>educamos — Iniciar Sesión</title>
  <meta name="description" content="Plataforma educativa institucional. Inicia sesión para acceder al sistema académico.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="estilos/login.css?v=<?= filemtime('estilos/login.css') ?>">
</head>
<body>

<div class="login-page">

  <!-- TARJETA CENTRAL -->
  <div class="login-card" role="main">

    <!-- CABECERA: escudo + marca -->
    <div class="login-header">
      <div class="escudo" aria-hidden="true">
        <svg width="52" height="60" viewBox="0 0 52 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M26 2L4 10V28C4 41.255 13.6 53.41 26 57C38.4 53.41 48 41.255 48 28V10L26 2Z" fill="#1a2e44" stroke="#1a2e44" stroke-width="0.5"/>
          <path d="M26 10L12 16V28C12 37.082 18.4 45.328 26 47.8C33.6 45.328 40 37.082 40 28V16L26 10Z" fill="#2d4a6b"/>
          <path d="M21 28L25 32L33 23" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <p class="junta-label">JUNTA DE COMUNIDADES</p>
      <h1 class="marca-educamos">
        <span class="letra-e">e</span>ducamos
      </h1>
    </div>

    <!-- SECCIÓN CLAVE -->
    <div class="seccion-clave">
      <button type="button" class="btn-clave" id="btn-clave">
        <span class="clave-badge" aria-hidden="true">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <rect width="14" height="14" rx="3" fill="white"/>
            <path d="M3 7l2.5 2.5L11 4.5" stroke="#2d7d46" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Cl@ve
        </span>
        <span>Iniciar sesión con Cl@ve</span>
      </button>

      <div class="clave-links">
        <a href="#" class="clave-link" id="link-soporte">Soporte técnico</a>
        <a href="#" class="clave-link" id="link-problemas">Problemas de acceso</a>
      </div>
    </div>

    <!-- DIVISOR CON TOGGLE -->
    <div class="divisor-toggle">
      <hr class="divisor-linea">
      <div class="toggle-row">
        <label class="toggle-switch" for="toggle-otras-formas">
          <input type="checkbox" id="toggle-otras-formas" checked aria-label="Mostrar otras formas de acceso">
          <span class="toggle-track">
            <span class="toggle-thumb"></span>
          </span>
        </label>
        <span class="toggle-label">Otras formas de acceso</span>
      </div>
    </div>

    <!-- FORMULARIO (visible cuando el toggle está activo) -->
    <div class="seccion-formulario" id="seccion-formulario">

      <?php if ($error): ?>
        <div class="alerta-error" role="alert" id="alerta-login">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="form-login" novalidate>

        <div class="campo-grupo">
          <label for="campo-usuario">Usuario</label>
          <input
            type="text"
            id="campo-usuario"
            name="usuario"
            autocomplete="username"
            spellcheck="false"
            value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
          >
        </div>

        <div class="campo-grupo">
          <label for="campo-password">Contraseña</label>
          <div class="campo-password-wrap">
            <input
              type="password"
              id="campo-password"
              name="password"
              autocomplete="current-password"
            >
            <button type="button" class="btn-ojo" id="btn-ojo" aria-label="Mostrar contraseña">
              <svg id="ojo-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-iniciar" id="btn-iniciar-sesion">
          Iniciar sesión
        </button>
      </form>

      <!-- Acceso rápido demo -->
      <div class="demo-accesos">
        <p class="demo-titulo">Acceso rápido (demo)</p>
        <div class="demo-chips">
          <button type="button" class="chip chip-admin"   onclick="rellenarLogin('admin','admin123')">Admin</button>
          <button type="button" class="chip chip-prof"    onclick="rellenarLogin('profesor1','profesor123')">Profesor</button>
          <button type="button" class="chip chip-alumno"  onclick="rellenarLogin('alumno1','alumno123')">Alumno</button>
        </div>
      </div>

    </div><!-- /seccion-formulario -->

  </div><!-- /login-card -->

  <footer class="login-footer">
    Servicio de Gestión Académica &mdash; <?= date('Y') ?>
  </footer>

</div><!-- /login-page -->

<script>
/* Toggle: mostrar/ocultar formulario */
const toggle = document.getElementById('toggle-otras-formas');
const seccion = document.getElementById('seccion-formulario');

toggle.addEventListener('change', () => {
  if (toggle.checked) {
    seccion.classList.remove('oculto');
    seccion.style.maxHeight = seccion.scrollHeight + 'px';
  } else {
    seccion.style.maxHeight = seccion.scrollHeight + 'px';
    requestAnimationFrame(() => {
      seccion.style.maxHeight = '0';
    });
    setTimeout(() => seccion.classList.add('oculto'), 350);
  }
});

/* Botón ojo: mostrar/ocultar contraseña */
const btnOjo = document.getElementById('btn-ojo');
const inputPass = document.getElementById('campo-password');

btnOjo.addEventListener('click', () => {
  const visible = inputPass.type === 'text';
  inputPass.type = visible ? 'password' : 'text';
  btnOjo.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
  btnOjo.classList.toggle('activo', !visible);
});

/* Relleno rápido demo */
function rellenarLogin(u, p) {
  document.getElementById('campo-usuario').value = u;
  document.getElementById('campo-password').value = p;
  document.getElementById('campo-usuario').focus();
}

/* Animación de carga en el botón al enviar */
document.getElementById('form-login').addEventListener('submit', function() {
  const btn = document.getElementById('btn-iniciar-sesion');
  btn.textContent = 'Iniciando…';
  btn.disabled = true;
});
</script>
</body>
</html>
