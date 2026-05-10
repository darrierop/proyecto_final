<?php
require_once 'incluye/autenticacion.php';
requiereLogin();
require_once 'incluye/bd.php';

$userId = getUsuarioId();
$msg = '';
$err = '';
$view = $_GET['view'] ?? 'inbox';

// ── ENVIAR ──
if (($_POST['action'] ?? '') === 'send') {
  if (!validarCsrfToken($_POST['csrf_token'] ?? '')) {
    $err = 'Petición no válida. Recarga la página.';
  } else {
  $dest = (int) $_POST['destinatario_id'];
  $asunt = trim($_POST['asunto']);
  $cuerp = trim($_POST['cuerpo']);
  if ($dest && $cuerp) {
    $stmt = $conn->prepare("INSERT INTO mensajes (remitente_id,destinatario_id,asunto,cuerpo) VALUES (?,?,?,?)");
    $stmt->bind_param('iiss', $userId, $dest, $asunt, $cuerp);
    if ($stmt->execute()) {
      $msg = 'Mensaje enviado correctamente.';
      $view = 'inbox';
    } else
      $err = $conn->error;
  } else {
    $err = 'Selecciona un destinatario y escribe el mensaje.';
  }
  } // cierre CSRF
}

// ── MARCAR LEÍDO ──
if (isset($_GET['read'])) {
  $conn->query("UPDATE mensajes SET leido=1 WHERE id=" . (int) $_GET['read'] . " AND destinatario_id=$userId");
  header('Location: mensajes.php?view=inbox');
  exit;
}

// ── BORRAR ──
if (isset($_GET['del'])) {
  $id = (int) $_GET['del'];
  $conn->query("DELETE FROM mensajes WHERE id=$id AND (remitente_id=$userId OR destinatario_id=$userId)");
  header('Location: mensajes.php?view=' . ($view === 'sent' ? 'sent' : 'inbox'));
  exit;
}

// ── DATOS ──
$inbox = $conn->query("
    SELECT m.*, u.nombre_completo AS remitente_nombre, u.usuario AS remitente_usuario
    FROM mensajes m JOIN usuarios u ON m.remitente_id = u.id
    WHERE m.destinatario_id = $userId
    ORDER BY m.fecha_envio DESC
");
$sent = $conn->query("
    SELECT m.*, u.nombre_completo AS dest_nombre
    FROM mensajes m JOIN usuarios u ON m.destinatario_id = u.id
    WHERE m.remitente_id = $userId
    ORDER BY m.fecha_envio DESC
");
$users = $conn->query("SELECT id, nombre_completo, usuario FROM usuarios WHERE id != $userId ORDER BY nombre_completo");

// Preseleccionar al responder
$replyTo = (int) ($_GET['reply'] ?? 0);

$sinLeerTotal = (int) $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE destinatario_id=$userId AND leido=0")->fetch_assoc()['c'];
$enviadosTotal = (int) $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE remitente_id=$userId")->fetch_assoc()['c'];

$tituloPagina = 'Mensajes';
require_once 'incluye/cabecera.php';
?>

<?php if ($msg): ?>
  <div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?>
  <div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="mensajes-layout">

  <!-- ══ SIDEBAR MENSAJES ══ -->
  <aside class="mensajes-sidebar">
    <a href="?view=compose" class="btn btn-primario"
      style="width:100%;justify-content:center;margin-bottom:18px;padding:11px">
      ✏️ Nuevo mensaje
    </a>

    <nav class="mensajes-nav">
      <a href="?view=inbox" class="mensajes-nav-item <?= $view === 'inbox' ? 'activo' : '' ?>">
        <span>📥</span>
        <span>Bandeja de entrada</span>
        <?php if ($sinLeerTotal > 0): ?>
          <span class="nav-badge"><?= $sinLeerTotal ?></span>
        <?php endif; ?>
      </a>
      <a href="?view=sent" class="mensajes-nav-item <?= $view === 'sent' ? 'activo' : '' ?>">
        <span>📤</span>
        <span>Enviados</span>
        <?php if ($enviadosTotal > 0): ?>
          <span style="margin-left:auto;font-size:.72rem;color:var(--texto-3)"><?= $enviadosTotal ?></span>
        <?php endif; ?>
      </a>
    </nav>

    <div class="mensajes-stat">
      <div><?= $sinLeerTotal ?> sin leer</div>
      <div><?= $inbox->num_rows ?> recibidos · <?= $enviadosTotal ?> enviados</div>
    </div>
  </aside>

  <!-- ══ PANEL PRINCIPAL ══ -->
  <div class="mensajes-panel">

    <?php if ($view === 'compose'): ?>
      <!-- COMPOSE -->
      <div class="tarjeta mensajes-compose">
        <div class="tarjeta-titulo">
          <span>✏️ Redactar nuevo mensaje</span>
        </div>

        <form method="POST" class="compose-form">
          <input type="hidden" name="action" value="send">
          <input type="hidden" name="csrf_token" value="<?= generarCsrfToken() ?>">

          <div class="compose-campo">
            <label class="compose-label">Para</label>
            <select name="destinatario_id" required class="compose-input">
              <option value="">— Selecciona destinatario —</option>
              <?php while ($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>" <?= $replyTo === $u['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['nombre_completo']) ?>
                  <span style="color:var(--texto-3)"> · @<?= htmlspecialchars($u['usuario']) ?></span>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="compose-separador"></div>

          <div class="compose-campo">
            <label class="compose-label">Asunto</label>
            <input type="text" name="asunto" placeholder="Escribe el asunto del mensaje" class="compose-input"
              value="<?= $replyTo ? 'Re: ' : '' ?>">
          </div>

          <div class="compose-separador"></div>

          <textarea name="cuerpo" required rows="10" placeholder="Escribe tu mensaje aquí..."
            class="compose-textarea"></textarea>

          <div class="compose-pie">
            <button type="submit" class="btn btn-primario" style="padding:10px 24px">
              📨 Enviar mensaje
            </button>
            <a href="?view=inbox" class="btn btn-borde">Cancelar</a>
          </div>
        </form>
      </div>

    <?php elseif ($view === 'sent'): ?>
      <!-- SENT -->
      <div class="tarjeta">
        <div class="tarjeta-titulo">
          📤 Mensajes enviados
          <span class="etiqueta etiqueta-gris"><?= $enviadosTotal ?></span>
        </div>
        <?php if ($sent->num_rows === 0): ?>
          <div class="mensajes-vacio">
            <div style="font-size:3rem;margin-bottom:12px">📭</div>
            <div style="font-weight:600;color:var(--texto-2);margin-bottom:6px">Sin mensajes enviados</div>
            <div style="font-size:.83rem;color:var(--texto-3)">Cuando envíes un mensaje aparecerá aquí.</div>
            <a href="?view=compose" class="btn btn-primario" style="margin-top:16px">✏️ Redactar mensaje</a>
          </div>
        <?php else: ?>
          <div class="lista-mensajes">
            <?php while ($m = $sent->fetch_assoc()): ?>
              <div class="mensaje-item">
                <div class="mensaje-avatar sent-avatar"><?= strtoupper(substr($m['dest_nombre'], 0, 1)) ?></div>
                <div class="mensaje-contenido">
                  <div class="mensaje-cabecera">
                    <span class="mensaje-nombre">Para: <?= htmlspecialchars($m['dest_nombre']) ?></span>
                    <span class="mensaje-fecha"><?= date('d/m/Y H:i', strtotime($m['fecha_envio'])) ?></span>
                  </div>
                  <div class="mensaje-asunto"><?= htmlspecialchars($m['asunto'] ?: '(Sin asunto)') ?></div>
                  <?php if ($m['cuerpo']): ?>
                    <div class="mensaje-preview">
                      <?= htmlspecialchars(substr(strip_tags($m['cuerpo']), 0, 120)) ?>        <?= strlen($m['cuerpo']) > 120 ? '…' : '' ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="mensaje-acciones">
                  <button
                    onclick="confirmarBorrar('?del=<?= $m['id'] ?>&view=sent','mensaje a <?= htmlspecialchars(addslashes($m['dest_nombre'])) ?>')"
                    class="btn btn-peligro-sm btn-sm">🗑</button>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <!-- INBOX -->
      <div class="tarjeta">
        <div class="tarjeta-titulo">
          📥 Bandeja de entrada
          <?php if ($sinLeerTotal): ?>
            <span class="etiqueta etiqueta-azul"><?= $sinLeerTotal ?> nuevos</span>
          <?php else: ?>
            <span class="etiqueta etiqueta-gris"><?= $inbox->num_rows ?> mensajes</span>
          <?php endif; ?>
        </div>
        <?php if ($inbox->num_rows === 0): ?>
          <div class="mensajes-vacio">
            <div style="font-size:3rem;margin-bottom:12px">📭</div>
            <div style="font-weight:600;color:var(--texto-2);margin-bottom:6px">Sin mensajes</div>
            <div style="font-size:.83rem;color:var(--texto-3)">Cuando recibas un mensaje aparecerá aquí.</div>
          </div>
        <?php else: ?>
          <div class="lista-mensajes">
            <?php while ($m = $inbox->fetch_assoc()):
              $inicialEnv = strtoupper(mb_substr($m['remitente_nombre'], 0, 1));
              $noLeido = !$m['leido'];
              ?>
              <div class="mensaje-item <?= $noLeido ? 'mensaje-nuevo' : '' ?>">
                <div class="mensaje-avatar <?= $noLeido ? 'avatar-activo' : '' ?>"><?= $inicialEnv ?></div>
                <div class="mensaje-contenido">
                  <div class="mensaje-cabecera">
                    <span class="mensaje-nombre"
                      style="<?= $noLeido ? 'font-weight:700' : '' ?>"><?= htmlspecialchars($m['remitente_nombre']) ?></span>
                    <span class="mensaje-fecha"><?= date('d/m/Y H:i', strtotime($m['fecha_envio'])) ?></span>
                  </div>
                  <div class="mensaje-asunto" style="<?= $noLeido ? 'font-weight:600' : '' ?>">
                    <?= $noLeido ? '<span class="punto-nuevo"></span>' : '' ?>
                    <?= htmlspecialchars($m['asunto'] ?: '(Sin asunto)') ?>
                  </div>
                  <?php if ($m['cuerpo']): ?>
                    <div class="mensaje-preview">
                      <?= htmlspecialchars(substr(strip_tags($m['cuerpo']), 0, 140)) ?>        <?= strlen($m['cuerpo']) > 140 ? '…' : '' ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="mensaje-acciones">
                  <?php if ($noLeido): ?>
                    <a href="?read=<?= $m['id'] ?>" class="btn btn-exito-sm btn-sm" title="Marcar como leído">✓</a>
                  <?php endif; ?>
                  <a href="?view=compose&reply=<?= $m['remitente_id'] ?>" class="btn btn-borde btn-sm" title="Responder">↩</a>
                  <button
                    onclick="confirmarBorrar('?del=<?= $m['id'] ?>','mensaje de <?= htmlspecialchars(addslashes($m['remitente_nombre'])) ?>')"
                    class="btn btn-peligro-sm btn-sm" title="Eliminar">🗑</button>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div><!-- /mensajes-panel -->
</div><!-- /mensajes-layout -->

<?php require_once 'incluye/pie.php'; ?>