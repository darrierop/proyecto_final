<?php
require_once 'incluye/autenticacion.php';
requiereLogin();
require_once 'incluye/bd.php';

$rol = $_SESSION['rol'];
$userId = getUsuarioId();

// ── ADMINISTRADOR ──
if ($rol == 1) {
  $estadisticas = [];
  $estadisticas['usuarios'] = $conn->query("SELECT COUNT(*) AS c FROM usuarios")->fetch_assoc()['c'];
  $estadisticas['cursos'] = $conn->query("SELECT COUNT(*) AS c FROM cursos WHERE estado='Activo'")->fetch_assoc()['c'];
  $estadisticas['matriculas'] = $conn->query("SELECT COUNT(*) AS c FROM matriculas")->fetch_assoc()['c'];
  $estadisticas['asignaturas'] = $conn->query("SELECT COUNT(*) AS c FROM asignaturas")->fetch_assoc()['c'];

  $usuariosRecientes = $conn->query("
        SELECT u.nombre_completo, u.usuario, r.nombre AS rol, u.fecha_creacion
        FROM usuarios u JOIN roles r ON u.rol_id = r.id
        ORDER BY u.fecha_creacion DESC LIMIT 6
    ");
  $matriculasRecientes = $conn->query("
        SELECT u.nombre_completo, a.nombre AS asignatura, m.fecha_matricula, m.nota_final
        FROM matriculas m
        JOIN usuarios u    ON m.alumno_id      = u.id
        JOIN cursos c      ON m.curso_id        = c.id
        JOIN asignaturas a ON c.asignatura_id   = a.id
        ORDER BY m.fecha_matricula DESC LIMIT 6
    ");
}

// ── PROFESOR ──
if ($rol == 2) {
  $misCursos = $conn->query("
        SELECT c.id, a.nombre AS asignatura, c.nombre_grupo, c.semestre, c.estado,
               COUNT(m.id) AS total_alumnos
        FROM cursos c
        JOIN asignaturas a ON c.asignatura_id = a.id
        LEFT JOIN matriculas m ON m.curso_id = c.id
        WHERE c.profesor_id = $userId
        GROUP BY c.id
    ");
  $totalAlumnos = $conn->query("SELECT COUNT(DISTINCT m.alumno_id) AS c FROM matriculas m JOIN cursos c ON m.curso_id=c.id WHERE c.profesor_id=$userId")->fetch_assoc()['c'];
  $totalCursos = $conn->query("SELECT COUNT(*) AS c FROM cursos WHERE profesor_id=$userId")->fetch_assoc()['c'];
  $notasPendientes = $conn->query("SELECT COUNT(*) AS c FROM matriculas m JOIN cursos c ON m.curso_id=c.id WHERE c.profesor_id=$userId AND m.nota_final IS NULL")->fetch_assoc()['c'];
}

// ── ALUMNO ──
if ($rol == 3) {
  $misMatriculas = $conn->query("
        SELECT m.id, a.nombre AS asignatura, a.creditos, c.nombre_grupo, c.semestre,
               m.nota_final, u.nombre_completo AS profesor
        FROM matriculas m
        JOIN cursos c      ON m.curso_id        = c.id
        JOIN asignaturas a ON c.asignatura_id   = a.id
        JOIN usuarios u    ON c.profesor_id     = u.id
        WHERE m.alumno_id = $userId ORDER BY a.nombre
    ");
  $promedio = $conn->query("SELECT AVG(nota_final) AS avg FROM matriculas WHERE alumno_id=$userId AND nota_final IS NOT NULL")->fetch_assoc()['avg'];
  $totalMats = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$userId")->fetch_assoc()['c'];
  $aprobadas = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$userId AND nota_final >= 5")->fetch_assoc()['c'];
}

$tituloPagina = 'Panel Principal';
require_once 'incluye/cabecera.php';

// ── Avisos activos para mostrar en el panel ──
$avisos = null;
if (isset($conn)) {
  $avisos = $conn->query("SELECT titulo, cuerpo, tipo FROM avisos WHERE activo=1 AND (expira IS NULL OR expira >= CURDATE()) ORDER BY fecha_creacion DESC LIMIT 3");
}

function colorNota($n)
{
  if ($n === null)
    return '<span style="color:var(--muted)">—</span>';
  if ($n >= 7)
    return "<span class='nota-alta'>$n</span>";
  if ($n >= 5)
    return "<span class='nota-media'>$n</span>";
  return "<span class='nota-baja'>$n</span>";
}
?>

<?php
// ── Avisos ──
if ($avisos && $avisos->num_rows > 0):
  $tipoEstilo = ['Info' => 'alerta-info', 'Alerta' => 'alerta-aviso', 'Urgente' => 'alerta-error', 'Exito' => 'alerta-exito'];
  $tipoIco = ['Info' => '⬡', 'Alerta' => '◎', 'Urgente' => '⊗', 'Exito' => '⊙'];
  ?>
  <div style="margin-bottom:18px;display:flex;flex-direction:column;gap:8px">
    <?php while ($av = $avisos->fetch_assoc()):
      $cls = $tipoEstilo[$av['tipo']] ?? 'alerta-info';
      $ico = $tipoIco[$av['tipo']] ?? '◉';
      ?>
      <div class="alerta <?= $cls ?>"><?= $ico ?> <strong><?= htmlspecialchars($av['titulo']) ?></strong> —
        <?= htmlspecialchars($av['cuerpo']) ?>
      </div>
      <?php endwhile; ?>
  </div>
<?php endif; ?>

<?php if ($rol == 1): ?>
  <!-- ═══════════ PANEL ADMINISTRADOR ═══════════ -->
  <div class="rejilla-estadisticas">
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Usuarios totales</div>
      <div class="estadistica-valor" style="color:var(--accent)"><?= $estadisticas['usuarios'] ?></div>
      <div class="estadistica-icono">◈</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Cursos activos</div>
      <div class="estadistica-valor" style="color:var(--accent2)"><?= $estadisticas['cursos'] ?></div>
      <div class="estadistica-icono">⬡</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Matrículas</div>
      <div class="estadistica-valor" style="color:var(--success)"><?= $estadisticas['matriculas'] ?></div>
      <div class="estadistica-icono">◉</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Asignaturas</div>
      <div class="estadistica-valor" style="color:var(--gold)"><?= $estadisticas['asignaturas'] ?></div>
      <div class="estadistica-icono">≡</div>
    </div>
  </div>

  <div class="rejilla-2col">
    <div class="tarjeta">
      <div class="tarjeta-titulo">◈ Usuarios recientes</div>
      <div class="tabla-contenedor">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Rol</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($u = $usuariosRecientes->fetch_assoc()):
              $ce = $u['rol'] == 'Administrador' ? 'etiqueta-amarilla' : ($u['rol'] == 'Profesor' ? 'etiqueta-azul' : 'etiqueta-verde');
              ?>
              <tr>
                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                <td style="color:var(--muted)"><?= htmlspecialchars($u['usuario']) ?></td>
                <td><span class="etiqueta <?= $ce ?>"><?= htmlspecialchars($u['rol']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top:14px"><a href="<?= $base ?>/administrador/usuarios.php" class="btn btn-borde btn-sm">Ver
          todos →</a></div>
    </div>

    <div class="tarjeta">
      <div class="tarjeta-titulo">◉ Últimas matrículas</div>
      <div class="tabla-contenedor">
        <table>
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Asignatura</th>
              <th>Nota</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($m = $matriculasRecientes->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($m['nombre_completo']) ?></td>
                <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($m['asignatura']) ?></td>
                <td><?= colorNota($m['nota_final']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top:14px"><a href="<?= $base ?>/administrador/matriculas.php" class="btn btn-borde btn-sm">Ver
          todas →</a></div>
    </div>
  </div>

  <!-- ── GRÁFICAS ADMINISTRADOR ── -->
  <div class="rejilla-2col" style="margin-top:20px">
    <div class="tarjeta">
      <div class="tarjeta-titulo">◉ Distribución de notas</div>
      <div class="grafica-contenedor"><canvas id="chartNotas"></canvas></div>
    </div>
    <div class="tarjeta">
      <div class="tarjeta-titulo">⬡ Aprobados vs Suspensos</div>
      <div class="grafica-contenedor"><canvas id="chartAprobados"></canvas></div>
    </div>
  </div>

  <?php
  // Datos para gráficas admin
  $rangos = ['0–4' => 0, '5–6' => 0, '7–8' => 0, '9–10' => 0];
  $rNotas = $conn->query("SELECT nota_final FROM matriculas WHERE nota_final IS NOT NULL");
  while ($rn = $rNotas->fetch_assoc()) {
    $n = (float) $rn['nota_final'];
    if ($n < 5)
      $rangos['0–4']++;
    elseif ($n < 7)
      $rangos['5–6']++;
    elseif ($n < 9)
      $rangos['7–8']++;
    else
      $rangos['9–10']++;
  }
  $aprobados = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE nota_final>=5")->fetch_assoc()['c'];
  $suspensos = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE nota_final<5")->fetch_assoc()['c'];
  $pendientes = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE nota_final IS NULL")->fetch_assoc()['c'];
  ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    (function () {
      var isDark = document.documentElement.getAttribute('data-tema') === 'oscuro';
      var textColor = isDark ? '#cbd5e1' : '#475569';
      var gridColor = isDark ? '#334155' : '#e2e8f0';
      Chart.defaults.color = textColor;

      new Chart(document.getElementById('chartNotas'), {
        type: 'bar',
        data: {
          labels: ['0–4', '5–6', '7–8', '9–10'],
          datasets: [{
            label: 'Alumnos',
            data: [<?= implode(',', array_values($rangos)) ?>],
            backgroundColor: ['rgba(220,38,38,.7)', 'rgba(217,119,6,.7)', 'rgba(37,99,235,.7)', 'rgba(22,163,74,.7)'],
            borderRadius: 6
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
          scales: { y: { grid: { color: gridColor }, ticks: { color: textColor } }, x: { grid: { display: false }, ticks: { color: textColor } } }
        }
      });

      new Chart(document.getElementById('chartAprobados'), {
        type: 'doughnut',
        data: {
          labels: ['Aprobados', 'Suspensos', 'Pendientes'],
          datasets: [{
            data: [<?= $aprobados ?>, <?= $suspensos ?>, <?= $pendientes ?>],
            backgroundColor: ['rgba(22,163,74,.8)', 'rgba(220,38,38,.8)', 'rgba(148,163,184,.5)'],
            borderWidth: 0, hoverOffset: 6
          }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
      });
    })();
  </script>

<?php elseif ($rol == 2): ?>
  <!-- ═══════════ PANEL PROFESOR ═══════════ -->
  <div class="rejilla-estadisticas">
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Mis cursos</div>
      <div class="estadistica-valor" style="color:var(--accent)"><?= $totalCursos ?></div>
      <div class="estadistica-icono">⬡</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Alumnos totales</div>
      <div class="estadistica-valor" style="color:var(--success)"><?= $totalAlumnos ?></div>
      <div class="estadistica-icono">◈</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Notas pendientes</div>
      <div class="estadistica-valor" style="color:<?= $notasPendientes > 0 ? 'var(--warning)' : 'var(--success)' ?>">
        <?= $notasPendientes ?>
      </div>
      <div class="estadistica-icono">◎</div>
    </div>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-titulo" style="justify-content:space-between">
      ⬡ Mis Cursos
      <a href="<?= $base ?>/profesor/calificaciones.php" class="btn btn-primario btn-sm">◈ Gestionar Notas</a>
    </div>
    <div class="tabla-contenedor">
      <table>
        <thead>
          <tr>
            <th>Asignatura</th>
            <th>Grupo</th>
            <th>Semestre</th>
            <th>Alumnos</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($c = $misCursos->fetch_assoc()): ?>
            <tr>
              <td style="font-weight:500"><?= htmlspecialchars($c['asignatura']) ?></td>
              <td><?= htmlspecialchars($c['nombre_grupo']) ?></td>
              <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($c['semestre']) ?></td>
              <td><span class="etiqueta etiqueta-azul"><?= $c['total_alumnos'] ?> alumnos</span></td>
              <td><span
                  class="etiqueta <?= $c['estado'] == 'Activo' ? 'etiqueta-verde' : 'etiqueta-gris' ?>"><?= $c['estado'] ?></span>
              </td>
              <td><a href="<?= $base ?>/profesor/calificaciones.php?curso_id=<?= $c['id'] ?>"
                  class="btn btn-borde btn-sm">Ver notas</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── GRÁFICA PROFESOR ── -->
  <?php
  $cursoNombres = [];
  $cursoAlumnos = [];
  // Re-query para gráfica (misCursos ya fue consumido)
  $grafProfesor = $conn->query("SELECT a.nombre AS asignatura, COUNT(m.id) AS total FROM cursos c JOIN asignaturas a ON c.asignatura_id=a.id LEFT JOIN matriculas m ON m.curso_id=c.id WHERE c.profesor_id=$userId GROUP BY c.id");
  while ($gp = $grafProfesor->fetch_assoc()) {
    $cursoNombres[] = "'" . $gp['asignatura'] . "'";
    $cursoAlumnos[] = $gp['total'];
  }
  ?>
  <div class="tarjeta" style="margin-top:20px">
    <div class="tarjeta-titulo">◉ Alumnos por asignatura</div>
    <div class="grafica-contenedor"><canvas id="chartProf"></canvas></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    new Chart(document.getElementById('chartProf'), {
      type: 'bar',
      data: { labels: [<?= implode(',', $cursoNombres) ?>], datasets: [{ label: 'Alumnos', data: [<?= implode(',', $cursoAlumnos) ?>], backgroundColor: 'rgba(37,99,235,.7)', borderRadius: 6 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { ticks: { maxRotation: 30 } } } }
    });
  </script>

<?php else: ?>
  <!-- ═══════════ PANEL ALUMNO ═══════════ -->
  <div class="rejilla-estadisticas">
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Asignaturas</div>
      <div class="estadistica-valor" style="color:var(--accent)"><?= $totalMats ?></div>
      <div class="estadistica-icono">≡</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Media general</div>
      <div class="estadistica-valor" style="color:<?= $promedio >= 5 ? 'var(--success)' : 'var(--danger)' ?>">
        <?= $promedio ? number_format($promedio, 1) : '—' ?>
      </div>
      <div class="estadistica-icono">◉</div>
    </div>
    <div class="tarjeta-estadistica">
      <div class="estadistica-etiqueta">Aprobadas</div>
      <div class="estadistica-valor" style="color:var(--success)"><?= $aprobadas ?></div>
      <div class="estadistica-icono">⊙</div>
    </div>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-titulo">≡ Mis Asignaturas</div>
    <div class="tabla-contenedor">
      <table>
        <thead>
          <tr>
            <th>Asignatura</th>
            <th>Profesor</th>
            <th>Grupo</th>
            <th>Créditos</th>
            <th>Nota Final</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($m = $misMatriculas->fetch_assoc()): ?>
            <tr>
              <td style="font-weight:500"><?= htmlspecialchars($m['asignatura']) ?></td>
              <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($m['profesor']) ?></td>
              <td><?= htmlspecialchars($m['nombre_grupo']) ?></td>
              <td><span class="etiqueta etiqueta-azul"><?= $m['creditos'] ?> cr.</span></td>
              <td><?= colorNota($m['nota_final']) ?></td>
              <td><a href="<?= $base ?>/alumno/calificaciones.php?mat_id=<?= $m['id'] ?>"
                  class="btn btn-borde btn-sm">Detalle</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── GRÁFICA + EXPORTACIÓN ALUMNO ── -->
  <?php
  $labelsAlumno = [];
  $notasAlumno = [];
  $grafAlumno = $conn->query("SELECT a.nombre AS asignatura, m.nota_final FROM matriculas m JOIN cursos c ON m.curso_id=c.id JOIN asignaturas a ON c.asignatura_id=a.id WHERE m.alumno_id=$userId AND m.nota_final IS NOT NULL");
  while ($ga = $grafAlumno->fetch_assoc()) {
    $labelsAlumno[] = "'" . $ga['asignatura'] . "'";
    $notasAlumno[] = $ga['nota_final'];
  }
  ?>
  <?php if (count($labelsAlumno)): ?>
    <div class="tarjeta" style="margin-top:20px">
      <div class="tarjeta-titulo">
        ◉ Mis notas
        <div style="display:flex;gap:8px">
          <a href="<?= $base ?>/exportar_pdf.php" target="_blank" class="btn btn-borde btn-sm">◎ PDF</a>
          <a href="<?= $base ?>/exportar_excel.php?tipo=expediente" class="btn btn-borde btn-sm">≡ Excel</a>
        </div>
      </div>
      <div class="grafica-contenedor"><canvas id="chartAlumno"></canvas></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
      new Chart(document.getElementById('chartAlumno'), {
        type: 'bar',
        data: {
          labels: [<?= implode(',', $labelsAlumno) ?>],
          datasets: [{
            label: 'Nota',
            data: [<?= implode(',', $notasAlumno) ?>],
            backgroundColor: function (ctx) { var v = ctx.dataset.data[ctx.dataIndex]; return v >= 7 ? 'rgba(22,163,74,.7)' : v >= 5 ? 'rgba(37,99,235,.7)' : 'rgba(220,38,38,.7)'; },
            borderRadius: 6
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, indexAxis: 'y',
          plugins: { legend: { display: false } },
          scales: { x: { min: 0, max: 10, ticks: { stepSize: 1 } }, y: { ticks: { font: { size: 11 } } } }
        }
      });
    </script>
  <?php endif; ?>

<?php endif; ?>

<?php require_once 'incluye/pie.php'; ?>