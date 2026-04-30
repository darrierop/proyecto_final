<?php
require_once 'incluye/autenticacion.php';
requiereLogin();
require_once 'incluye/bd.php';

$userId = getUsuarioId();
$msg = '';
$err = '';
$seccion = $_GET['s'] ?? 'info';

// ── Cargar datos actuales ──
$usuario = $conn->query("
    SELECT u.*, r.nombre AS rol_nombre
    FROM usuarios u JOIN roles r ON u.rol_id = r.id
    WHERE u.id = $userId
")->fetch_assoc();

// ── Guardar información personal ──
if ($_POST['accion'] ?? '' === 'info') {
  $nombre = trim($_POST['nombre_completo']);
  $email = trim($_POST['email']);
  if ($nombre && $email) {
    // Comprobar email único
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email=? AND id!=?");
    $check->bind_param('si', $email, $userId);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
      $err = 'Ese email ya está en uso por otro usuario.';
    } else {
      $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, email=? WHERE id=?");
      $stmt->bind_param('ssi', $nombre, $email, $userId);
      $stmt->execute();
      $_SESSION['nombre_completo'] = $nombre;
      $msg = 'Información actualizada correctamente.';
      $usuario['nombre_completo'] = $nombre;
      $usuario['email'] = $email;
    }
  } else {
    $err = 'Nombre y email son obligatorios.';
  }
}

// ── Subir foto de perfil ──
if ($_POST['accion'] ?? '' === 'foto') {
  $seccion = 'info';
  if (isset($_FILES['foto_archivo']) && $_FILES['foto_archivo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto_archivo'];
    $tipos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB
    if (!in_array($file['type'], $tipos)) {
      $err = 'Formato no permitido. Usa JPG, PNG, GIF o WEBP.';
    } elseif ($file['size'] > $maxSize) {
      $err = 'La imagen no puede superar 2 MB.';
    } else {
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $nombre = 'avatar_' . $userId . '_' . time() . '.' . strtolower($ext);
      $destino = __DIR__ . '/uploads/avatares/' . $nombre;
      if (move_uploaded_file($file['tmp_name'], $destino)) {
        // Borrar foto anterior si existe
        $fotoVieja = $usuario['foto'] ?? null;
        if ($fotoVieja && file_exists(__DIR__ . '/uploads/avatares/' . $fotoVieja)) {
          unlink(__DIR__ . '/uploads/avatares/' . $fotoVieja);
        }
        $n = $conn->real_escape_string($nombre);
        $conn->query("UPDATE usuarios SET foto='$n' WHERE id=$userId");
        $_SESSION['foto'] = $nombre;
        $usuario['foto'] = $nombre;
        $msg = 'Foto de perfil actualizada.';
      } else {
        $err = 'No se pudo guardar la imagen. Verifica permisos del servidor.';
      }
    }
  } else {
    $err = 'No se recibió ningún archivo.';
  }
}


// ── Cambiar contraseña ──
if ($_POST['accion'] ?? '' === 'password') {
  $actual = $_POST['password_actual'];
  $nueva = $_POST['password_nueva'];
  $repetir = $_POST['password_repetir'];

  if (!password_verify($actual, $usuario['password'])) {
    $err = 'La contraseña actual no es correcta.';
  } elseif (strlen($nueva) < 6) {
    $err = 'La nueva contraseña debe tener al menos 6 caracteres.';
  } elseif ($nueva !== $repetir) {
    $err = 'Las contraseñas nuevas no coinciden.';
  } else {
    $hash = password_hash($nueva, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE usuarios SET password=? WHERE id=?");
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute();
    $msg = 'Contraseña cambiada correctamente.';
  }
  $seccion = 'password';
}

// ── Estadísticas del usuario ──
$stats = [];
if ($usuario['rol_id'] == 3) {
  $stats['matriculas'] = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$userId")->fetch_assoc()['c'];
  $stats['aprobadas'] = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$userId AND nota_final>=5")->fetch_assoc()['c'];
  $stats['media'] = $conn->query("SELECT ROUND(AVG(nota_final),2) AS m FROM matriculas WHERE alumno_id=$userId AND nota_final IS NOT NULL")->fetch_assoc()['m'];
  $stats['mensajes'] = $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE remitente_id=$userId OR destinatario_id=$userId")->fetch_assoc()['c'];
} elseif ($usuario['rol_id'] == 2) {
  $stats['cursos'] = $conn->query("SELECT COUNT(*) AS c FROM cursos WHERE profesor_id=$userId")->fetch_assoc()['c'];
  $stats['alumnos'] = $conn->query("SELECT COUNT(DISTINCT m.alumno_id) AS c FROM matriculas m JOIN cursos c ON m.curso_id=c.id WHERE c.profesor_id=$userId")->fetch_assoc()['c'];
  $stats['calificaciones'] = $conn->query("SELECT COUNT(*) AS c FROM calificaciones cal JOIN matriculas m ON cal.matricula_id=m.id JOIN cursos c ON m.curso_id=c.id WHERE c.profesor_id=$userId")->fetch_assoc()['c'];
  $stats['mensajes'] = $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE remitente_id=$userId OR destinatario_id=$userId")->fetch_assoc()['c'];
} else {
  $stats['usuarios'] = $conn->query("SELECT COUNT(*) AS c FROM usuarios")->fetch_assoc()['c'];
  $stats['cursos'] = $conn->query("SELECT COUNT(*) AS c FROM cursos")->fetch_assoc()['c'];
  $stats['matriculas'] = $conn->query("SELECT COUNT(*) AS c FROM matriculas")->fetch_assoc()['c'];
  $stats['mensajes'] = $conn->query("SELECT COUNT(*) AS c FROM mensajes")->fetch_assoc()['c'];
}

// Actividad reciente (mensajes)
$actividad = $conn->query("
    SELECT m.asunto, m.fecha_envio, m.leido,
           u.nombre_completo AS otro,
           IF(m.remitente_id=$userId,'enviado','recibido') AS tipo
    FROM mensajes m
    JOIN usuarios u ON (CASE WHEN m.remitente_id=$userId THEN m.destinatario_id ELSE m.remitente_id END) = u.id
    WHERE m.remitente_id=$userId OR m.destinatario_id=$userId
    ORDER BY m.fecha_envio DESC LIMIT 5
");

$iniciales = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice(explode(' ', trim($usuario['nombre_completo'])), 0, 2)));
$claseRol = $usuario['rol_id'] == 1 ? 'rol-admin' : ($usuario['rol_id'] == 2 ? 'rol-prof' : 'rol-alumno');
$fechaRegistro = date('d \d\e F \d\e Y', strtotime($usuario['fecha_creacion']));

$tituloPagina = 'Mi Perfil';
require_once 'incluye/cabecera.php';
?>

<div class="perfil-grid-2col" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

  <!-- ── TARJETA LATERAL DE PERFIL ── -->
  <div>
    <div class="tarjeta" style="text-align:center;padding:28px 22px">
      <!-- Avatar: foto o iniciales -->
      <?php if (!empty($usuario['foto'])): ?>
        <img src="<?= $base ?>/uploads/avatares/<?= htmlspecialchars($usuario['foto']) ?>"
             style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 16px;display:block;border:3px solid var(--azul-borde);box-shadow:0 4px 14px rgba(37,99,235,.2);" alt="Avatar">
      <?php else: ?>
        <div class="avatar-grande" style="margin:0 auto 16px">
          <?= $iniciales ?>
        </div>
      <?php endif; ?>

      <!-- Formulario subida de foto -->
      <form method="POST" enctype="multipart/form-data" style="margin-bottom:12px">
        <input type="hidden" name="accion" value="foto">
        <label style="display:inline-block;padding:6px 14px;background:var(--bg);border:1px solid var(--borde);border-radius:8px;cursor:pointer;font-size:.75rem;color:var(--texto-2)">
          📷 Cambiar foto
          <input type="file" name="foto_archivo" accept="image/*" onchange="this.form.submit()" style="display:none">
        </label>
      </form>

      <div style="font-family:'Sora',sans-serif;font-size:1.05rem;font-weight:700;color:var(--texto);margin-bottom:4px">
        <?= htmlspecialchars($usuario['nombre_completo']) ?>
      </div>
      <div style="color:var(--texto-3);font-size:.82rem;margin-bottom:10px">
        @<?= htmlspecialchars($usuario['usuario']) ?>
      </div>
      <span class="etiqueta-rol <?= $claseRol ?>" style="font-size:.76rem;padding:4px 12px">
        <?= htmlspecialchars($usuario['rol_nombre']) ?>
      </span>

      <hr class="divisor">

      <div style="text-align:left">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
          <span style="color:var(--texto-3);font-size:13px">✉️</span>
          <span style="font-size:.82rem;color:var(--texto-2)"><?= htmlspecialchars($usuario['email']) ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
          <span style="color:var(--texto-3);font-size:13px">📅</span>
          <span style="font-size:.82rem;color:var(--texto-2)">Desde <?= $fechaRegistro ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <span style="color:var(--texto-3);font-size:13px">🆔</span>
          <span style="font-size:.82rem;color:var(--texto-2)">ID de usuario: <?= $userId ?></span>
        </div>
      </div>

      <hr class="divisor">

      <!-- Estadísticas rápidas -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <?php foreach ($stats as $etiqueta => $valor):
          $labels = [
            'matriculas' => 'Matrículas',
            'aprobadas' => 'Aprobadas',
            'media' => 'Media',
            'mensajes' => 'Mensajes',
            'cursos' => 'Cursos',
            'alumnos' => 'Alumnos',
            'calificaciones' => 'Notas reg.',
            'usuarios' => 'Usuarios'
          ];
          ?>
          <div
            style="background:var(--bg);border:1px solid var(--borde);border-radius:8px;padding:10px;text-align:center">
            <div style="font-family:'Sora',sans-serif;font-size:1.3rem;font-weight:700;color:var(--azul)">
              <?= $valor ?? '—' ?>
            </div>
            <div
              style="font-size:.66rem;color:var(--texto-3);text-transform:uppercase;letter-spacing:.06em;margin-top:2px">
              <?= $labels[$etiqueta] ?? $etiqueta ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Actividad reciente -->
    <div class="tarjeta" style="margin-top:16px">
      <div class="tarjeta-titulo"><span>📬 Actividad reciente</span></div>
      <?php if ($actividad->num_rows == 0): ?>
        <p style="color:var(--texto-3);font-size:.82rem;text-align:center;padding:10px 0">Sin actividad</p>
      <?php else: ?>
        <?php while ($a = $actividad->fetch_assoc()): ?>
          <div style="display:flex;align-items:flex-start;gap:8px;padding:8px 0;border-bottom:1px solid var(--borde)">
            <span style="font-size:13px;margin-top:1px"><?= $a['tipo'] == 'enviado' ? '📤' : '📥' ?></span>
            <div style="min-width:0">
              <div
                style="font-size:.8rem;font-weight:500;color:var(--texto);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= htmlspecialchars($a['asunto'] ?: '(Sin asunto)') ?>
              </div>
              <div style="font-size:.72rem;color:var(--texto-3)"><?= htmlspecialchars($a['otro']) ?> ·
                <?= date('d/m/Y', strtotime($a['fecha_envio'])) ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── PANEL PRINCIPAL ── -->
  <div>
    <!-- Tabs de navegación -->
    <div
      style="display:flex;gap:4px;margin-bottom:18px;background:#fff;padding:4px;border-radius:10px;border:1px solid var(--borde);width:fit-content;box-shadow:var(--sombra-sm)">
      <a href="?s=info" class="btn <?= $seccion == 'info' ? 'btn-primario' : 'btn-borde' ?> btn-sm"
        style="border:none">👤
        Información</a>
      <a href="?s=password" class="btn <?= $seccion == 'password' ? 'btn-primario' : 'btn-borde' ?> btn-sm"
        style="border:none">🔒 Contraseña</a>
      <a href="?s=sesion" class="btn <?= $seccion == 'sesion' ? 'btn-primario' : 'btn-borde' ?> btn-sm"
        style="border:none">📋 Sesión</a>
    </div>

    <?php if ($msg): ?>
      <div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?>
      <div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <?php if ($seccion === 'info'): ?>
      <!-- ── INFORMACIÓN PERSONAL ── -->
      <div class="tarjeta">
        <div class="tarjeta-titulo"><span>👤 Información personal</span></div>
        <form method="POST">
          <input type="hidden" name="accion" value="info">
          <div class="fila-formulario">
            <div class="grupo-formulario">
              <label>Nombre completo</label>
              <input type="text" name="nombre_completo" required
                value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
            </div>
            <div class="grupo-formulario">
              <label>Nombre de usuario</label>
              <input type="text" value="<?= htmlspecialchars($usuario['usuario']) ?>" disabled
                style="background:var(--bg);color:var(--texto-3);cursor:not-allowed">
              <small style="color:var(--texto-3);font-size:.72rem">El nombre de usuario no se puede cambiar</small>
            </div>
          </div>
          <div class="fila-formulario">
            <div class="grupo-formulario">
              <label>Correo electrónico</label>
              <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email']) ?>">
            </div>
            <div class="grupo-formulario">
              <label>Rol en el sistema</label>
              <input type="text" value="<?= htmlspecialchars($usuario['rol_nombre']) ?>" disabled
                style="background:var(--bg);color:var(--texto-3);cursor:not-allowed">
              <small style="color:var(--texto-3);font-size:.72rem">Solo el administrador puede cambiar el rol</small>
            </div>
          </div>
          <button type="submit" class="btn btn-primario">💾 Guardar cambios</button>
        </form>
      </div>

    <?php elseif ($seccion === 'password'): ?>
      <!-- ── CAMBIAR CONTRASEÑA ── -->
      <div class="tarjeta">
        <div class="tarjeta-titulo"><span>🔒 Cambiar contraseña</span></div>
        <form method="POST" style="max-width:440px">
          <input type="hidden" name="accion" value="password">
          <div class="grupo-formulario">
            <label>Contraseña actual</label>
            <input type="password" name="password_actual" required placeholder="Tu contraseña actual">
          </div>
          <hr class="divisor">
          <div class="grupo-formulario">
            <label>Nueva contraseña</label>
            <input type="password" name="password_nueva" required placeholder="Mínimo 6 caracteres" id="nuevaPwd">
          </div>
          <div class="grupo-formulario">
            <label>Repetir nueva contraseña</label>
            <input type="password" name="password_repetir" required placeholder="Repite la nueva contraseña"
              id="repetirPwd">
          </div>

          <!-- Indicador de fuerza -->
          <div id="fuerza-wrap" style="margin-bottom:14px;display:none">
            <div style="font-size:.72rem;color:var(--texto-3);margin-bottom:4px">Seguridad de la contraseña</div>
            <div style="background:var(--borde);border-radius:20px;height:5px;overflow:hidden">
              <div id="fuerza-barra" style="height:100%;border-radius:20px;transition:width .3s,background .3s"></div>
            </div>
            <div id="fuerza-texto" style="font-size:.72rem;margin-top:3px"></div>
          </div>

          <div id="coincide-msg" style="font-size:.78rem;margin-bottom:10px;display:none"></div>

          <button type="submit" class="btn btn-primario">🔒 Actualizar contraseña</button>
        </form>
      </div>

      <div class="tarjeta" style="margin-top:16px">
        <div class="tarjeta-titulo"><span>💡 Consejos de seguridad</span></div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:10px">
          <li style="display:flex;gap:10px;align-items:flex-start">
            <span style="color:var(--verde);font-size:15px;margin-top:1px">✓</span>
            <span style="font-size:.84rem;color:var(--texto-2)">Usa al menos 8 caracteres mezclando letras, números y
              símbolos</span>
          </li>
          <li style="display:flex;gap:10px;align-items:flex-start">
            <span style="color:var(--verde);font-size:15px;margin-top:1px">✓</span>
            <span style="font-size:.84rem;color:var(--texto-2)">No uses la misma contraseña en varios sitios</span>
          </li>
          <li style="display:flex;gap:10px;align-items:flex-start">
            <span style="color:var(--verde);font-size:15px;margin-top:1px">✓</span>
            <span style="font-size:.84rem;color:var(--texto-2)">Evita información personal como tu nombre o fecha de
              nacimiento</span>
          </li>
          <li style="display:flex;gap:10px;align-items:flex-start">
            <span style="color:var(--naranja);font-size:15px;margin-top:1px">⚠</span>
            <span style="font-size:.84rem;color:var(--texto-2)">Cierra siempre sesión al terminar en ordenadores
              compartidos</span>
          </li>
        </ul>
      </div>

    <?php else: ?>
      <!-- ── INFO DE SESIÓN ── -->
      <div class="tarjeta">
        <div class="tarjeta-titulo"><span>📋 Información de sesión actual</span></div>
        <div style="display:flex;flex-direction:column;gap:0">
          <?php
          $campos = [
            ['🆔', 'ID de usuario', $userId],
            ['👤', 'Usuario', $usuario['usuario']],
            ['📛', 'Nombre completo', $usuario['nombre_completo']],
            ['✉️', 'Email', $usuario['email']],
            ['🎭', 'Rol', $usuario['rol_nombre']],
            ['📅', 'Fecha de registro', date('d/m/Y H:i', strtotime($usuario['fecha_creacion']))],
            ['🌐', 'IP de acceso', $_SERVER['REMOTE_ADDR'] ?? '—'],
            ['🖥️', 'Navegador', substr($_SERVER['HTTP_USER_AGENT'] ?? '—', 0, 60) . '...'],
            ['⏱️', 'Hora del servidor', date('d/m/Y H:i:s')],
          ];
          foreach ($campos as [$ico, $etiq, $val]):
            ?>
            <div style="display:flex;align-items:center;padding:11px 0;border-bottom:1px solid var(--borde)">
              <span style="font-size:14px;width:28px"><?= $ico ?></span>
              <span
                style="font-size:.8rem;font-weight:600;color:var(--texto-3);width:160px;flex-shrink:0"><?= $etiq ?></span>
              <span style="font-size:.84rem;color:var(--texto)"><?= htmlspecialchars($val) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:18px">
          <a href="<?= $base ?>/cerrar_sesion.php" class="btn btn-peligro-sm btn-sm"
            style="font-size:.82rem;padding:7px 14px">
            🚪 Cerrar sesión ahora
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  const nuevaPwd = document.getElementById('nuevaPwd');
  const repetirPwd = document.getElementById('repetirPwd');
  const fuerzaWrap = document.getElementById('fuerza-wrap');
  const fuerzaBarra = document.getElementById('fuerza-barra');
  const fuerzaTexto = document.getElementById('fuerza-texto');
  const coincideMsg = document.getElementById('coincide-msg');

  function calcularFuerza(pwd) {
    let puntos = 0;
    if (pwd.length >= 8) puntos++;
    if (pwd.length >= 12) puntos++;
    if (/[A-Z]/.test(pwd)) puntos++;
    if (/[0-9]/.test(pwd)) puntos++;
    if (/[^A-Za-z0-9]/.test(pwd)) puntos++;
    return puntos;
  }

  if (nuevaPwd) {
    nuevaPwd.addEventListener('input', function () {
      const v = this.value;
      if (!v) { fuerzaWrap.style.display = 'none'; return; }
      fuerzaWrap.style.display = 'block';
      const f = calcularFuerza(v);
      const config = [
        { pct: '20%', bg: '#dc2626', txt: 'Muy débil' },
        { pct: '40%', bg: '#f97316', txt: 'Débil' },
        { pct: '60%', bg: '#d97706', txt: 'Moderada' },
        { pct: '80%', bg: '#16a34a', txt: 'Fuerte' },
        { pct: '100%', bg: '#059669', txt: 'Muy fuerte' },
      ][Math.max(0, f - 1)] || { pct: '0%', bg: '#dc2626', txt: '' };
      fuerzaBarra.style.width = config.pct;
      fuerzaBarra.style.background = config.bg;
      fuerzaTexto.textContent = config.txt;
      fuerzaTexto.style.color = config.bg;
    });
  }

  function checkCoincide() {
    if (!repetirPwd.value) { coincideMsg.style.display = 'none'; return; }
    const ok = nuevaPwd.value === repetirPwd.value;
    coincideMsg.style.display = 'block';
    coincideMsg.textContent = ok ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden';
    coincideMsg.style.color = ok ? 'var(--verde)' : 'var(--rojo)';
  }
  if (repetirPwd) {
    repetirPwd.addEventListener('input', checkCoincide);
    nuevaPwd && nuevaPwd.addEventListener('input', checkCoincide);
  }
</script>

<?php require_once 'incluye/pie.php'; ?>