<?php
require_once '../incluye/autenticacion.php';
requiereRol(3);
require_once '../incluye/bd.php';

$userId = getUsuarioId();
$matId  = isset($_GET['mat_id']) ? (int)$_GET['mat_id'] : 0;

// My enrollments
$mats = $conn->query("
    SELECT m.id, a.nombre AS asignatura, a.codigo, c.nombre_grupo, m.nota_final
    FROM matriculas m
    JOIN cursos c      ON m.curso_id        = c.id
    JOIN asignaturas a ON c.asignatura_id   = a.id
    WHERE m.alumno_id = $userId
    ORDER BY a.nombre
");

// Selected enrollment details
$detalle = [];
$matInfo = null;
if ($matId) {
    $chk = $conn->query("SELECT m.id, a.nombre AS asignatura, m.nota_final FROM matriculas m JOIN cursos c ON m.curso_id=c.id JOIN asignaturas a ON c.asignatura_id=a.id WHERE m.id=$matId AND m.alumno_id=$userId");
    if ($chk->num_rows) {
        $matInfo = $chk->fetch_assoc();
        $res = $conn->query("SELECT * FROM calificaciones WHERE matricula_id=$matId ORDER BY fecha");
        while ($r = $res->fetch_assoc()) $detalle[] = $r;
    }
}

$tituloPagina = 'Mis Calificaciones';
require_once '../incluye/cabecera.php';

function notaClass($n) {
    if ($n === null) return '<span style="color:var(--muted)">—</span>';
    if ($n >= 7) return "<span class='nota-alta'>".number_format($n,2)."</span>";
    if ($n >= 5) return "<span class='nota-media'>".number_format($n,2)."</span>";
    return "<span class='nota-baja'>".number_format($n,2)."</span>";
}
?>

<!-- Asignatura tabs -->
<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">📚 Mis Asignaturas — selecciona para ver detalle</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <?php $mats->data_seek(0); while ($m = $mats->fetch_assoc()): ?>
      <a href="?mat_id=<?= $m['id'] ?>"
         class="btn <?= $matId==$m['id']?'btn-primario':'btn-borde' ?>"
         style="position:relative">
        <?= htmlspecialchars($m['asignatura']) ?>
        <?php if ($m['nota_final'] !== null): ?>
          <span style="margin-left:6px;font-weight:700;color:<?= $m['nota_final']>=5?'var(--success)':'var(--danger)' ?>">
            <?= number_format($m['nota_final'],1) ?>
          </span>
        <?php endif; ?>
      </a>
    <?php endwhile; ?>
  </div>
</div>

<?php if ($matId && $matInfo): ?>
<div class="tarjeta">
  <div class="tarjeta-titulo">
    📝 <?= htmlspecialchars($matInfo['asignatura']) ?>
    — Nota Final: <?= notaClass($matInfo['nota_final']) ?>
  </div>

  <?php if (count($detalle)): ?>
    <!-- Weighted chart bar -->
    <?php
    $totalPeso = array_sum(array_column($detalle, 'peso'));
    $weighted  = 0;
    if ($totalPeso > 0) {
        foreach ($detalle as $d) {
            $weighted += ($d['nota'] * $d['peso']);
        }
        $weighted = $weighted / $totalPeso;
    }
    ?>
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:20px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <span style="font-size:.85rem;color:var(--muted)">Media ponderada de actividades</span>
        <span style="font-size:1.4rem;font-weight:700;color:<?= $weighted>=5?'var(--success)':'var(--danger)' ?>">
          <?= number_format($weighted, 2) ?>
        </span>
      </div>
      <div style="background:var(--border);border-radius:20px;height:8px;overflow:hidden">
        <div style="height:100%;width:<?= min(100,$weighted*10) ?>%;background:linear-gradient(90deg,var(--accent),var(--success));border-radius:20px;transition:width .6s ease"></div>
      </div>
    </div>

    <div class="tabla-contenedor">
      <table>
        <thead><tr><th>Actividad</th><th>Nota</th><th>Peso</th><th>Contribución</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php foreach ($detalle as $d): ?>
          <?php $contrib = $totalPeso > 0 ? ($d['nota'] * $d['peso']) / $totalPeso : 0; ?>
          <tr>
            <td style="font-weight:500"><?= htmlspecialchars($d['nombre_actividad']) ?></td>
            <td><?= notaClass($d['nota']) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:50px;background:var(--border);border-radius:10px;height:5px">
                  <div style="width:<?= min(100,$d['peso']) ?>%;height:100%;background:var(--accent);border-radius:10px"></div>
                </div>
                <span style="color:var(--muted);font-size:.82rem"><?= $d['peso'] ?>%</span>
              </div>
            </td>
            <td><?= notaClass(round($contrib,2)) ?></td>
            <td style="color:var(--muted);font-size:.82rem"><?= $d['fecha'] ? date('d/m/Y', strtotime($d['fecha'])) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alerta alerta-info">ℹ️ El profesor aún no ha registrado actividades para esta asignatura.</div>
  <?php endif; ?>
</div>
<?php elseif ($matId): ?>
  <div class="alerta alerta-error">❌ Matrícula no encontrada.</div>
<?php endif; ?>

<?php require_once '../incluye/pie.php'; ?>
