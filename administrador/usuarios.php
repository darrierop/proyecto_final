<?php
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';
require_once '../incluye/auditoria.php';

$msg = '';
$err = '';

// ── CREATE ──
if ($_POST['action'] ?? '' === 'create') {
  if (!validarCsrfToken($_POST['csrf_token'] ?? '')) {
    $err = 'Petición no válida. Recarga la página.';
  } else {
  $u  = trim($_POST['usuario']);
  $pw = $_POST['password'];
  $em = trim($_POST['email']);
  $nm = trim($_POST['nombre_completo']);
  $ri = (int) $_POST['rol_id'];

  if (strlen($pw) < 8) {
    $err = 'La contraseña debe tener al menos 8 caracteres.';
  } elseif (!preg_match('/[A-Z]/', $pw)) {
    $err = 'La contraseña debe incluir al menos una letra mayúscula.';
  } elseif (!preg_match('/[0-9]/', $pw)) {
    $err = 'La contraseña debe incluir al menos un número.';
  } else {
    $hash = password_hash($pw, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario,password,email,nombre_completo,rol_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param('ssssi', $u, $hash, $em, $nm, $ri);
    if ($stmt->execute())
      $msg = 'Usuario creado correctamente.';
    else
      $err = 'Error: ' . $conn->error;
  }
  } // cierre validar CSRF
}

// ── DELETE ──
if (isset($_GET['del']) && (int) $_GET['del'] !== 1) {
  $id = (int) $_GET['del'];
  $conn->query("DELETE FROM usuarios WHERE id = $id");
  $msg = 'Usuario eliminado.';
}

$usuarios = $conn->query("
    SELECT u.id, u.usuario, u.email, u.nombre_completo, u.fecha_creacion, r.nombre AS rol
    FROM usuarios u JOIN roles r ON u.rol_id = r.id
    ORDER BY u.id
");
$roles = $conn->query("SELECT * FROM roles ORDER BY id");

$tituloPagina = 'Gestión de Usuarios';
require_once '../incluye/cabecera.php';
?>

<?php if ($msg): ?>
  <div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?>
  <div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- CREATE FORM -->
<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">➕ Nuevo Usuario</div>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="csrf_token" value="<?= generarCsrfToken() ?>">
    <div class="form-row">
      <div class="form-group">
        <label>Nombre completo</label>
        <input type="text" name="nombre_completo" required placeholder="Ej. María García López">
      </div>
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="usuario" required placeholder="nombre_usuario">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required placeholder="correo@ejemplo.com">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required
          placeholder="Mínimo 8 car., 1 mayúscula, 1 número"
          id="pwd-nuevo-usuario" autocomplete="new-password">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Rol</label>
        <select name="rol_id" required>
          <?php $roles->data_seek(0);
          while ($r = $roles->fetch_assoc()): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primario">Crear Usuario</button>
  </form>
</div>

<!-- TABLE -->
<div class="tarjeta">
  <div class="tarjeta-titulo">
    👥 Usuarios del sistema
    <div style="display:flex;gap:8px;align-items:center">
      <div class="buscador-tabla"><input type="search" id="buscar-usuarios" placeholder="Buscar usuario..."></div>
      <a href="<?= $base ?>/exportar_excel.php?tipo=usuarios" class="btn btn-borde btn-sm">📥 Excel</a>
    </div>
  </div>
  <div class="tabla-contenedor">
    <table id="tbl-usuarios">
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Usuario</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Creado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
          <?php
          $bc = $u['rol'] == 'Administrador' ? 'etiqueta-amarilla' : ($u['rol'] == 'Profesor' ? 'etiqueta-azul' : 'etiqueta-verde');
          ?>
          <tr>
            <td style="color:var(--muted)"><?= $u['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($u['nombre_completo']) ?></td>
            <td style="color:var(--accent)"><?= htmlspecialchars($u['usuario']) ?></td>
            <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($u['rol']) ?></span></td>
            <td style="color:var(--muted);font-size:.8rem"><?= date('d/m/Y', strtotime($u['fecha_creacion'])) ?></td>
            <td>
              <?php if ($u['id'] != 1): ?>
                <button
                  onclick="confirmarBorrar('?del=<?= $u['id'] ?>','<?= htmlspecialchars(addslashes($u['nombre_completo'])) ?>')"
                  class="btn btn-peligro-sm">🗑 Eliminar</button>
              <?php else: ?>
                <span style="color:var(--muted);font-size:.78rem">Protegido</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>
<script>
filtrarTabla('buscar-usuarios', 'tbl-usuarios');

// Validación de contraseña al crear usuario
(function () {
  var form = document.querySelector('form[method="POST"]');
  var pwd  = document.getElementById('pwd-nuevo-usuario');
  if (!form || !pwd) return;
  form.addEventListener('submit', function (e) {
    var v = pwd.value;
    if (v.length < 8) {
      e.preventDefault();
      alert('La contraseña debe tener al menos 8 caracteres.');
      pwd.focus(); return;
    }
    if (!/[A-Z]/.test(v)) {
      e.preventDefault();
      alert('La contraseña debe incluir al menos una letra mayúscula.');
      pwd.focus(); return;
    }
    if (!/[0-9]/.test(v)) {
      e.preventDefault();
      alert('La contraseña debe incluir al menos un número.');
      pwd.focus(); return;
    }
  });
})();
</script>