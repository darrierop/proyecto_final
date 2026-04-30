<?php
require_once '../incluye/autenticacion.php';
requiereRol(2);
require_once '../incluye/bd.php';

$userId = getUsuarioId();

$cursos = $conn->query("
    SELECT c.id, a.nombre AS asignatura, a.codigo, c.nombre_grupo, c.semestre, c.estado,
           COUNT(m.id) AS total_alumnos,
           AVG(m.nota_final) AS media
    FROM cursos c
    JOIN asignaturas a ON c.asignatura_id = a.id
    LEFT JOIN matriculas m ON m.curso_id = c.id
    WHERE c.profesor_id = $userId
    GROUP BY c.id ORDER BY c.estado DESC, a.nombre
");

$tituloPagina = 'Mis Cursos';
require_once '../incluye/cabecera.php';

function notaClass($n)
{
  if ($n === null)
    return '<span style="color:var(--muted)">—</span>';
  if ($n >= 7)
    return "<span class='nota-alta'>" . number_format($n, 1) . "</span>";
  if ($n >= 5)
    return "<span class='nota-media'>" . number_format($n, 1) . "</span>";
  return "<span class='nota-baja'>" . number_format($n, 1) . "</span>";
}
?>

<div class="tarjeta">
  <div class="tarjeta-titulo" style="justify-content:space-between">
    🏫 Mis Cursos Asignados
    <a href="<?= $base ?>/profesor/calificaciones.php" class="btn btn-primario btn-sm">📝 Gestionar Notas</a>
  </div>
  <div class="tabla-contenedor">
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Asignatura</th>
          <th>Grupo</th>
          <th>Semestre</th>
          <th>Alumnos</th>
          <th>Media notas</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($c = $cursos->fetch_assoc()): ?>
          <tr>
            <td><span class="etiqueta etiqueta-amarilla"><?= htmlspecialchars($c['codigo']) ?></span></td>
            <td style="font-weight:500"><?= htmlspecialchars($c['asignatura']) ?></td>
            <td><?= htmlspecialchars($c['nombre_grupo']) ?></td>
            <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($c['semestre']) ?></td>
            <td><span class="etiqueta etiqueta-azul"><?= $c['total_alumnos'] ?></span></td>
            <td><?= notaClass($c['media']) ?></td>
            <td><span
                class="badge <?= $c['estado'] == 'Activo' ? 'etiqueta-verde' : 'etiqueta-gris' ?>"><?= $c['estado'] ?></span>
            </td>
            <td>
              <a href="<?= $base ?>/profesor/calificaciones.php?curso_id=<?= $c['id'] ?>" class="btn btn-borde btn-sm">Ver
                notas</a>
              <a href="<?= $base ?>/profesor/horarios.php?curso_id=<?= $c['id'] ?>"
                class="btn btn-borde btn-sm">Horario</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>