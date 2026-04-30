<?php
require_once '../incluye/autenticacion.php';
requiereRol(2);
require_once '../incluye/bd.php';

$userId  = getUsuarioId();
$cursoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

$myCursos = $conn->query("
    SELECT c.id, a.nombre AS asignatura, c.nombre_grupo
    FROM cursos c JOIN asignaturas a ON c.asignatura_id = a.id
    WHERE c.profesor_id = $userId ORDER BY a.nombre
");

$horarios = [];
if ($cursoId) {
    $res = $conn->query("
        SELECT h.*, au.nombre AS aula, c.nombre_grupo,
               a.nombre AS asignatura
        FROM horarios h
        JOIN cursos c      ON h.curso_id  = c.id
        JOIN asignaturas a ON c.asignatura_id = a.id
        LEFT JOIN aulas au ON h.aula_id   = au.id
        WHERE h.curso_id = $cursoId AND c.profesor_id = $userId
        ORDER BY FIELD(h.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), h.hora_inicio
    ");
    while ($r = $res->fetch_assoc()) $horarios[] = $r;
}

$tituloPagina = 'Horarios';
require_once '../incluye/cabecera.php';

$dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
$dayColors = [
    'Lunes'=>'etiqueta-azul','Martes'=>'etiqueta-verde','Miércoles'=>'etiqueta-amarilla',
    'Jueves'=>'etiqueta-roja','Viernes'=>'etiqueta-gris','Sábado'=>'etiqueta-gris'
];
?>

<div class="tarjeta" style="margin-bottom:24px">
  <div class="tarjeta-titulo">📅 Seleccionar Curso</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <?php $myCursos->data_seek(0); while ($c = $myCursos->fetch_assoc()): ?>
      <a href="?curso_id=<?= $c['id'] ?>"
         class="btn <?= $cursoId==$c['id']?'btn-primario':'btn-borde' ?>">
        <?= htmlspecialchars($c['asignatura'].' — '.$c['nombre_grupo']) ?>
      </a>
    <?php endwhile; ?>
  </div>
</div>

<?php if ($cursoId): ?>
  <?php if (count($horarios)): ?>
    <div class="tarjeta">
      <div class="tarjeta-titulo">📅 Horario del curso</div>
      <div class="tabla-contenedor">
        <table>
          <thead><tr><th>Día</th><th>Hora inicio</th><th>Hora fin</th><th>Duración</th><th>Aula</th></tr></thead>
          <tbody>
          <?php foreach ($horarios as $h): ?>
            <?php
              $ini = strtotime($h['hora_inicio']);
              $fin = strtotime($h['hora_fin']);
              $dur = round(($fin - $ini)/3600, 1);
            ?>
            <tr>
              <td><span class="badge <?= $dayColors[$h['dia_semana']] ?>"><?= $h['dia_semana'] ?></span></td>
              <td style="font-weight:500"><?= substr($h['hora_inicio'],0,5) ?></td>
              <td><?= substr($h['hora_fin'],0,5) ?></td>
              <td style="color:var(--muted)"><?= $dur ?> h</td>
              <td><?= htmlspecialchars($h['aula'] ?? 'Sin asignar') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="alerta alerta-info">ℹ️ Este curso no tiene horarios registrados.</div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once '../incluye/pie.php'; ?>
