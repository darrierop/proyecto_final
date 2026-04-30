<?php
require_once '../incluye/autenticacion.php';
requiereRol(2);
require_once '../incluye/bd.php';

$userId = getUsuarioId();
$msg = ''; $err = '';
$cursoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

// ── UPDATE nota_final ──
if ($_POST['action'] ?? '' === 'nota') {
    $matId = (int)$_POST['mat_id'];
    $nota  = $_POST['nota_final'] === '' ? 'NULL' : (float)str_replace(',','.',$_POST['nota_final']);
    // Verify this matricula belongs to the teacher's course
    $check = $conn->query("
        SELECT m.id FROM matriculas m
        JOIN cursos c ON m.curso_id = c.id
        WHERE m.id = $matId AND c.profesor_id = $userId
    ");
    if ($check->num_rows) {
        $conn->query("UPDATE matriculas SET nota_final = $nota WHERE id = $matId");
        $msg = 'Nota actualizada correctamente.';
    } else {
        $err = 'No tienes permiso para editar esta matrícula.';
    }
}

// ── ADD actividad ──
if ($_POST['action'] ?? '' === 'add_cal') {
    $matId   = (int)$_POST['mat_id'];
    $nombre  = trim($_POST['nombre_actividad']);
    $nota    = (float)$_POST['nota'];
    $peso    = (float)$_POST['peso'];
    $fecha   = $_POST['fecha'];
    $stmt = $conn->prepare("INSERT INTO calificaciones (matricula_id,nombre_actividad,nota,peso,fecha) VALUES (?,?,?,?,?)");
    $stmt->bind_param('isdds', $matId, $nombre, $nota, $peso, $fecha);
    if ($stmt->execute()) $msg = 'Calificación registrada.';
    else $err = $conn->error;
}

// ── My courses ──
$myCursos = $conn->query("
    SELECT c.id, a.nombre AS asignatura, c.nombre_grupo
    FROM cursos c JOIN asignaturas a ON c.asignatura_id = a.id
    WHERE c.profesor_id = $userId ORDER BY a.nombre
");

// ── Alumnos of selected course ──
$alumnos = [];
if ($cursoId) {
    $check = $conn->query("SELECT id FROM cursos WHERE id=$cursoId AND profesor_id=$userId");
    if ($check->num_rows) {
        $res = $conn->query("
            SELECT m.id AS mat_id, u.nombre_completo, u.usuario, m.nota_final
            FROM matriculas m
            JOIN usuarios u ON m.alumno_id = u.id
            WHERE m.curso_id = $cursoId
            ORDER BY u.nombre_completo
        ");
        while ($r = $res->fetch_assoc()) $alumnos[] = $r;
    }
}

$tituloPagina = 'Calificaciones';
require_once '../incluye/cabecera.php';

function notaClass($n) {
    if ($n === null) return '<span style="color:var(--muted)">—</span>';
    if ($n >= 7) return "<span class='nota-alta'>".number_format($n,2)."</span>";
    if ($n >= 5) return "<span class='nota-media'>".number_format($n,2)."</span>";
    return "<span class='nota-baja'>".number_format($n,2)."</span>";
}
?>

<?php if ($msg): ?><div class="alerta alerta-exito">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alerta alerta-error">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- Course selector -->
<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">🏫 Seleccionar Curso</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <?php $myCursos->data_seek(0); while ($c = $myCursos->fetch_assoc()): ?>
      <a href="?curso_id=<?= $c['id'] ?>"
         class="btn <?= $cursoId==$c['id']?'btn-primario':'btn-borde' ?>">
        <?= htmlspecialchars($c['asignatura'].' — '.$c['nombre_grupo']) ?>
      </a>
    <?php endwhile; ?>
  </div>
</div>

<?php if ($cursoId && count($alumnos)): ?>

<!-- Alumnos table -->
<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">👩‍🎓 Alumnos matriculados</div>
  <div class="tabla-contenedor">
    <table>
      <thead><tr><th>Alumno</th><th>Usuario</th><th>Nota Final</th><th>Actualizar</th><th>Actividades</th></tr></thead>
      <tbody>
      <?php foreach ($alumnos as $al): ?>
        <tr>
          <td style="font-weight:500"><?= htmlspecialchars($al['nombre_completo']) ?></td>
          <td style="color:var(--muted)"><?= htmlspecialchars($al['usuario']) ?></td>
          <td><?= notaClass($al['nota_final']) ?></td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="action" value="nota">
              <input type="hidden" name="mat_id" value="<?= $al['mat_id'] ?>">
              <input type="hidden" name="curso_id_redirect" value="<?= $cursoId ?>">
              <input type="number" name="nota_final" min="0" max="10" step="0.01"
                     value="<?= $al['nota_final'] ?? '' ?>"
                     style="width:80px;padding:5px 8px;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);font-size:.85rem">
              <button type="submit" class="btn btn-exito-sm">Guardar</button>
            </form>
          </td>
          <td>
            <button onclick="toggleActividades(<?= $al['mat_id'] ?>)" class="btn btn-borde btn-sm">
              📝 Ver/Añadir
            </button>
          </td>
        </tr>
        <!-- Actividades sub-panel -->
        <tr id="act-<?= $al['mat_id'] ?>" style="display:none">
          <td colspan="5" style="background:rgba(0,0,0,.2);padding:16px">
            <?php
              $cals = $conn->query("SELECT * FROM calificaciones WHERE matricula_id={$al['mat_id']} ORDER BY fecha");
            ?>
            <div style="margin-bottom:12px">
              <strong style="font-size:.85rem;color:var(--accent)">Actividades registradas:</strong>
              <?php if ($cals->num_rows == 0): ?>
                <span style="color:var(--muted);font-size:.82rem"> — Sin actividades</span>
              <?php else: ?>
                <table style="width:100%;margin-top:8px">
                  <thead><tr><th>Actividad</th><th>Nota</th><th>Peso %</th><th>Fecha</th></tr></thead>
                  <tbody>
                  <?php while ($cal = $cals->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($cal['nombre_actividad']) ?></td>
                      <td><?= notaClass($cal['nota']) ?></td>
                      <td style="color:var(--muted)"><?= $cal['peso'] ?>%</td>
                      <td style="color:var(--muted);font-size:.8rem"><?= $cal['fecha'] ? date('d/m/Y', strtotime($cal['fecha'])) : '—' ?></td>
                    </tr>
                  <?php endwhile; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
            <!-- Add actividad form -->
            <form method="POST" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end">
              <input type="hidden" name="action" value="add_cal">
              <input type="hidden" name="mat_id" value="<?= $al['mat_id'] ?>">
              <input type="hidden" name="curso_id_redirect" value="<?= $cursoId ?>">
              <div>
                <label style="font-size:.7rem;color:var(--muted);display:block;margin-bottom:3px">Actividad</label>
                <input type="text" name="nombre_actividad" required placeholder="Ej. Examen Parcial"
                       style="width:160px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);font-size:.82rem">
              </div>
              <div>
                <label style="font-size:.7rem;color:var(--muted);display:block;margin-bottom:3px">Nota</label>
                <input type="number" name="nota" min="0" max="10" step="0.1" required
                       style="width:70px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);font-size:.82rem">
              </div>
              <div>
                <label style="font-size:.7rem;color:var(--muted);display:block;margin-bottom:3px">Peso %</label>
                <input type="number" name="peso" value="30" min="0" max="100" step="0.1"
                       style="width:70px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);font-size:.82rem">
              </div>
              <div>
                <label style="font-size:.7rem;color:var(--muted);display:block;margin-bottom:3px">Fecha</label>
                <input type="date" name="fecha"
                       style="padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);font-size:.82rem">
              </div>
              <button type="submit" class="btn btn-primario btn-sm">➕ Añadir</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleActividades(id) {
  const el = document.getElementById('act-' + id);
  el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}
// Keep curso_id in URL after POST
document.querySelectorAll('form').forEach(f => {
  const hi = f.querySelector('input[name="curso_id_redirect"]');
  if (hi) {
    f.action = '?curso_id=' + hi.value;
  }
});
</script>

<?php elseif ($cursoId): ?>
  <div class="alerta alerta-info">ℹ️ Este curso no tiene alumnos matriculados aún.</div>
<?php endif; ?>

<?php require_once '../incluye/pie.php'; ?>
