<?php
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';

$msg = '';
$err = '';

if ($_POST['action'] ?? '' === 'create') {
  $asi = (int) $_POST['asignatura_id'];
  $pro = (int) $_POST['profesor_id'];
  $gr = trim($_POST['nombre_grupo']);
  $sem = trim($_POST['semestre']);
  $est = $_POST['estado'];
  $stmt = $conn->prepare("INSERT INTO cursos (asignatura_id,profesor_id,nombre_grupo,semestre,estado) VALUES (?,?,?,?,?)");
  $stmt->bind_param('iisss', $asi, $pro, $gr, $sem, $est);
  if ($stmt->execute())
    $msg = 'Curso creado.';
  else
    $err = $conn->error;
}
if (isset($_GET['del'])) {
  $conn->query("DELETE FROM cursos WHERE id=" . (int) $_GET['del']);
  $msg = 'Curso eliminado.';
}

$cursos = $conn->query("
    SELECT c.id, a.nombre AS asignatura, a.codigo, u.nombre_completo AS profesor,
           c.nombre_grupo, c.semestre, c.estado,
           COUNT(m.id) AS total_alumnos
    FROM cursos c
    JOIN asignaturas a ON c.asignatura_id = a.id
    LEFT JOIN usuarios u ON c.profesor_id = u.id
    LEFT JOIN matriculas m ON m.curso_id = c.id
    GROUP BY c.id ORDER BY c.id
");
$asignaturas = $conn->query("SELECT * FROM asignaturas ORDER BY nombre");
$profesores = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 ORDER BY nombre_completo");

$tituloPagina = 'Gestión de Cursos';
require_once '../incluye/cabecera.php';
?>

<?php if ($msg): ?>
  <div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?>
  <div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">➕ Nuevo Curso</div>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <div class="form-row">
      <div class="form-group">
        <label>Asignatura</label>
        <select name="asignatura_id" required>
          <?php while ($a = $asignaturas->fetch_assoc()): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Profesor</label>
        <select name="profesor_id" required>
          <?php while ($p = $profesores->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre_completo']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Nombre de grupo</label>
        <input type="text" name="nombre_grupo" placeholder="Ej. Grupo A">
      </div>
      <div class="form-group">
        <label>Semestre</label>
        <input type="text" name="semestre" value="2025-2026 S1">
      </div>
    </div>
    <div class="form-group" style="max-width:200px">
      <label>Estado</label>
      <select name="estado">
        <option value="Activo">Activo</option>
        <option value="Finalizado">Finalizado</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primario">Crear Curso</button>
  </form>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo">
    🏫 Todos los Cursos
    <div class="buscador-tabla"><input type="search" id="buscar-cursos" placeholder="Buscar curso..."></div>
  </div>
  <div class="tabla-contenedor">
    <table id="tbl-cursos">
      <thead>
        <tr>
          <th>#</th>
          <th>Asignatura</th>
          <th>Código</th>
          <th>Profesor</th>
          <th>Grupo</th>
          <th>Semestre</th>
          <th>Alumnos</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php while ($c = $cursos->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--muted)"><?= $c['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($c['asignatura']) ?></td>
            <td><span class="etiqueta etiqueta-amarilla"><?= $c['codigo'] ?></span></td>
            <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($c['profesor'] ?? '—') ?></td>
            <td><?= htmlspecialchars($c['nombre_grupo']) ?></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($c['semestre']) ?></td>
            <td><span class="etiqueta etiqueta-azul"><?= $c['total_alumnos'] ?></span></td>
            <td><span
                class="badge <?= $c['estado'] == 'Activo' ? 'etiqueta-verde' : 'etiqueta-gris' ?>"><?= $c['estado'] ?></span>
            </td>
            <td>
              <button
                onclick="confirmarBorrar('?del=<?= $c['id'] ?>','Curso: <?= htmlspecialchars(addslashes($c['asignatura'] . ' ' . $c['nombre_grupo'])) ?>')"
                class="btn btn-peligro-sm">🗑</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>
<script>filtrarTabla('buscar-cursos', 'tbl-cursos');</script>