<?php
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';

$msg = ''; $err = '';

if (($_POST['action'] ?? '') === 'create') {
    if (!validarCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = 'Petición no válida. Recarga la página.';
    } else {
    $nm = trim($_POST['nombre']);
    $ca = (int)$_POST['capacidad'];
    $ti = $_POST['tipo'];
    $stmt = $conn->prepare("INSERT INTO aulas (nombre,capacidad,tipo) VALUES (?,?,?)");
    $stmt->bind_param('sis', $nm, $ca, $ti);
    if ($stmt->execute()) $msg = 'Aula creada.';
    else $err = $conn->error;
    } // cierre CSRF
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM aulas WHERE id=".(int)$_GET['del']);
    $msg = 'Aula eliminada.';
}

$aulas = $conn->query("SELECT * FROM aulas ORDER BY nombre");

$tituloPagina = 'Aulas';
require_once '../incluye/cabecera.php';
?>

<?php if ($msg): ?><div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">➕ Nueva Aula</div>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="csrf_token" value="<?= generarCsrfToken() ?>">
    <div class="form-row">
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" required placeholder="Ej. Aula 301">
      </div>
      <div class="form-group">
        <label>Capacidad</label>
        <input type="number" name="capacidad" value="30" min="1">
      </div>
    </div>
    <div class="form-group" style="max-width:220px">
      <label>Tipo</label>
      <select name="tipo">
        <option>Teoría</option>
        <option>Laboratorio</option>
        <option>Taller</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primario">Crear Aula</button>
  </form>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo">🚪 Aulas disponibles</div>
  <div class="tabla-contenedor">
    <table>
      <thead><tr><th>#</th><th>Nombre</th><th>Capacidad</th><th>Tipo</th><th></th></tr></thead>
      <tbody>
      <?php while ($a = $aulas->fetch_assoc()):
        $tc = $a['tipo']=='Teoría'?'etiqueta-azul':($a['tipo']=='Laboratorio'?'etiqueta-verde':'etiqueta-amarilla');
      ?>
        <tr>
          <td style="color:var(--muted)"><?= $a['id'] ?></td>
          <td style="font-weight:500"><?= htmlspecialchars($a['nombre']) ?></td>
          <td><?= $a['capacidad'] ?> personas</td>
          <td><span class="badge <?= $tc ?>"><?= $a['tipo'] ?></span></td>
          <td>
            <a href="?del=<?= $a['id'] ?>" class="btn btn-peligro-sm"
               onclick="return confirm('¿Eliminar aula?')">🗑</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>
