<?php
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';

$msg = ''; $err = '';

if (($_POST['action'] ?? '') === 'create') {
    if (!validarCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = 'Petición no válida. Recarga la página.';
    } else {
    $co = trim($_POST['codigo']);
    $nm = trim($_POST['nombre']);
    $de = trim($_POST['descripcion']);
    $cr = (int)$_POST['creditos'];
    $stmt = $conn->prepare("INSERT INTO asignaturas (codigo,nombre,descripcion,creditos) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi', $co, $nm, $de, $cr);
    if ($stmt->execute()) $msg = 'Asignatura creada.';
    else $err = $conn->error;
    } // cierre CSRF
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM asignaturas WHERE id=".(int)$_GET['del']);
    $msg = 'Asignatura eliminada.';
}

$asigs = $conn->query("
    SELECT a.*, COUNT(c.id) AS total_cursos
    FROM asignaturas a LEFT JOIN cursos c ON c.asignatura_id = a.id
    GROUP BY a.id ORDER BY a.codigo
");

$tituloPagina = 'Asignaturas';
require_once '../incluye/cabecera.php';
?>

<?php if ($msg): ?><div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">➕ Nueva Asignatura</div>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="csrf_token" value="<?= generarCsrfToken() ?>">
    <div class="form-row">
      <div class="form-group">
        <label>Código</label>
        <input type="text" name="codigo" required placeholder="Ej. MAT101">
      </div>
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" required placeholder="Nombre de la asignatura">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="descripcion" placeholder="Breve descripción">
      </div>
      <div class="form-group">
        <label>Créditos</label>
        <input type="number" name="creditos" value="6" min="1" max="12">
      </div>
    </div>
    <button type="submit" class="btn btn-primario">Crear Asignatura</button>
  </form>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo">📚 Asignaturas</div>
  <div class="tabla-contenedor">
    <table>
      <thead><tr><th>Código</th><th>Nombre</th><th>Descripción</th><th>Créditos</th><th>Cursos</th><th></th></tr></thead>
      <tbody>
      <?php while ($a = $asigs->fetch_assoc()): ?>
        <tr>
          <td><span class="etiqueta etiqueta-amarilla"><?= htmlspecialchars($a['codigo']) ?></span></td>
          <td style="font-weight:500"><?= htmlspecialchars($a['nombre']) ?></td>
          <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($a['descripcion'] ?? '—') ?></td>
          <td><span class="etiqueta etiqueta-azul"><?= $a['creditos'] ?> cr.</span></td>
          <td><?= $a['total_cursos'] ?></td>
          <td>
            <?php if ($a['total_cursos'] == 0): ?>
              <a href="?del=<?= $a['id'] ?>" class="btn btn-peligro-sm"
                 onclick="return confirm('¿Eliminar?')">🗑</a>
            <?php else: ?>
              <span style="color:var(--muted);font-size:.78rem">En uso</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>
