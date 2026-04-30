<?php
require_once '../incluye/autenticacion.php';
requiereRol(3);
require_once '../incluye/bd.php';

$userId = getUsuarioId();

$mats = $conn->query("
    SELECT m.id, a.nombre AS asignatura, a.codigo, a.creditos, a.descripcion,
           c.nombre_grupo, c.semestre, c.estado,
           u.nombre_completo AS profesor, m.nota_final
    FROM matriculas m
    JOIN cursos c      ON m.curso_id        = c.id
    JOIN asignaturas a ON c.asignatura_id   = a.id
    JOIN usuarios u    ON c.profesor_id     = u.id
    WHERE m.alumno_id = $userId
    ORDER BY a.nombre
");

$tituloPagina = 'Mis Cursos';
require_once '../incluye/cabecera.php';

function notaClass($n)
{
  if ($n === null)
    return '<span style="color:var(--muted)">Pendiente</span>';
  if ($n >= 7)
    return "<span class='nota-alta'>" . number_format($n, 2) . " ✓</span>";
  if ($n >= 5)
    return "<span class='nota-media'>" . number_format($n, 2) . "</span>";
  return "<span class='nota-baja'>" . number_format($n, 2) . " ✗</span>";
}
?>

<div class="tarjeta">
  <div class="tarjeta-titulo">📚 Mis Asignaturas Matriculadas</div>
  <div class="tabla-contenedor">
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Asignatura</th>
          <th>Profesor</th>
          <th>Grupo</th>
          <th>Créditos</th>
          <th>Semestre</th>
          <th>Estado</th>
          <th>Nota</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php while ($m = $mats->fetch_assoc()): ?>
          <tr>
            <td><span class="etiqueta etiqueta-amarilla"><?= htmlspecialchars($m['codigo']) ?></span></td>
            <td>
              <div style="font-weight:500"><?= htmlspecialchars($m['asignatura']) ?></div>
              <div style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars(substr($m['descripcion'] ?? '', 0, 50)) ?>
              </div>
            </td>
            <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($m['profesor']) ?></td>
            <td><?= htmlspecialchars($m['nombre_grupo']) ?></td>
            <td><span class="etiqueta etiqueta-azul"><?= $m['creditos'] ?> cr.</span></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($m['semestre']) ?></td>
            <td><span
                class="badge <?= $m['estado'] == 'Activo' ? 'etiqueta-verde' : 'etiqueta-gris' ?>"><?= $m['estado'] ?></span>
            </td>
            <td><?= notaClass($m['nota_final']) ?></td>
            <td>
              <a href="<?= $base ?>/alumno/calificaciones.php?mat_id=<?= $m['id'] ?>"
                class="btn btn-borde btn-sm">Detalle</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../incluye/pie.php'; ?>