<?php
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';

$msg = ''; $err = '';

if ($_POST['action'] ?? '' === 'create') {
    $al = (int)$_POST['alumno_id'];
    $cu = (int)$_POST['curso_id'];
    $stmt = $conn->prepare("INSERT INTO matriculas (alumno_id, curso_id) VALUES (?,?)");
    $stmt->bind_param('ii', $al, $cu);
    if ($stmt->execute()) $msg = 'Matrícula registrada.';
    else $err = 'Error (¿ya matriculado?): ' . $conn->error;
}
if ($_POST['action'] ?? '' === 'nota') {
    $id = (int)$_POST['mat_id'];
    $nota = $_POST['nota_final'] === '' ? 'NULL' : (float)$_POST['nota_final'];
    $conn->query("UPDATE matriculas SET nota_final = $nota WHERE id = $id");
    $msg = 'Nota actualizada.';
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM matriculas WHERE id=" . (int)$_GET['del']);
    $msg = 'Matrícula eliminada.';
}

$matriculas = $conn->query("
    SELECT m.id, u.nombre_completo AS alumno, a.nombre AS asignatura,
           c.nombre_grupo, m.fecha_matricula, m.nota_final
    FROM matriculas m
    JOIN usuarios u     ON m.alumno_id      = u.id
    JOIN cursos c       ON m.curso_id        = c.id
    JOIN asignaturas a  ON c.asignatura_id   = a.id
    ORDER BY m.fecha_matricula DESC
");
$alumnos = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 3 ORDER BY nombre_completo");
$cursos  = $conn->query("
    SELECT c.id, a.nombre AS asignatura, c.nombre_grupo
    FROM cursos c JOIN asignaturas a ON c.asignatura_id = a.id
    ORDER BY a.nombre
");

$tituloPagina = 'Gestión de Matrículas';
require_once '../incluye/cabecera.php';

function notaClass($n) {
    if ($n === null) return '<span style="color:var(--muted)">Sin nota</span>';
    if ($n >= 7) return "<span class='nota-alta'>$n</span>";
    if ($n >= 5) return "<span class='nota-media'>$n</span>";
    return "<span class='nota-baja'>$n</span>";
}
?>

<?php if ($msg): ?><div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">➕ Nueva Matrícula</div>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <div class="form-row">
      <div class="form-group">
        <label>Alumno</label>
        <select name="alumno_id" required>
          <?php while ($a = $alumnos->fetch_assoc()): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre_completo']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Curso</label>
        <select name="curso_id" required>
          <?php while ($c = $cursos->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['asignatura'].' — '.$c['nombre_grupo']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primario">Matricular</button>
  </form>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo">📋 Todas las Matrículas</div>
  <div class="tabla-contenedor">
    <table>
      <thead><tr><th>#</th><th>Alumno</th><th>Asignatura</th><th>Grupo</th><th>Fecha</th><th>Nota Final</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php while ($m = $matriculas->fetch_assoc()): ?>
        <tr>
          <td style="color:var(--muted)"><?= $m['id'] ?></td>
          <td style="font-weight:500"><?= htmlspecialchars($m['alumno']) ?></td>
          <td><?= htmlspecialchars($m['asignatura']) ?></td>
          <td style="color:var(--muted)"><?= htmlspecialchars($m['nombre_grupo']) ?></td>
          <td style="font-size:.82rem;color:var(--muted)"><?= date('d/m/Y', strtotime($m['fecha_matricula'])) ?></td>
          <td><?= notaClass($m['nota_final']) ?></td>
          <td style="display:flex;gap:6px;align-items:center">
            <!-- Quick-nota form -->
            <form method="POST" style="display:flex;gap:4px">
              <input type="hidden" name="action" value="nota">
              <input type="hidden" name="mat_id" value="<?= $m['id'] ?>">
              <input type="number" name="nota_final" min="0" max="10" step="0.1"
                     value="<?= $m['nota_final'] ?? '' ?>"
                     style="width:70px;padding:4px 8px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:.8rem">
              <button type="submit" class="btn btn-exito-sm">✓</button>
            </form>
            <a href="?del=<?= $m['id'] ?>" class="btn btn-peligro-sm"
               onclick="return confirm('¿Eliminar matrícula?')">🗑</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>
