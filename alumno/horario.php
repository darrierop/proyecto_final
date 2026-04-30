<?php
require_once '../incluye/autenticacion.php';
requiereRol(3);
require_once '../incluye/bd.php';

$userId = getUsuarioId();

$horarios = $conn->query("
    SELECT h.dia_semana, h.hora_inicio, h.hora_fin,
           a.nombre AS asignatura, au.nombre AS aula, u.nombre_completo AS profesor,
           c.nombre_grupo
    FROM horarios h
    JOIN cursos c       ON h.curso_id        = c.id
    JOIN asignaturas a  ON c.asignatura_id   = a.id
    LEFT JOIN aulas au  ON h.aula_id         = au.id
    JOIN usuarios u     ON c.profesor_id     = u.id
    JOIN matriculas m   ON m.curso_id        = c.id AND m.alumno_id = $userId
    ORDER BY FIELD(h.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes'), h.hora_inicio
");

$byDay = [];
while ($h = $horarios->fetch_assoc()) {
    $byDay[$h['dia_semana']][] = $h;
}

$dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
$dayColors = ['Lunes'=>'#4f7cff','Martes'=>'#34d399','Miércoles'=>'#f4c842','Jueves'=>'#a78bfa','Viernes'=>'#f87171'];

$tituloPagina = 'Mi Horario';
require_once '../incluye/cabecera.php';
?>

<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px" class="horario-dias-grid">
  <?php foreach ($dias as $dia): ?>
    <div>
      <div style="text-align:center;padding:8px;border-radius:10px;margin-bottom:10px;
                  background:<?= $dayColors[$dia] ?>22;border:1px solid <?= $dayColors[$dia] ?>44;
                  color:<?= $dayColors[$dia] ?>;font-weight:600;font-size:.85rem">
        <?= $dia ?>
      </div>
      <?php if (!empty($byDay[$dia])): ?>
        <?php foreach ($byDay[$dia] as $h): ?>
          <div style="background:var(--card);border:1px solid <?= $dayColors[$dia] ?>33;
                      border-left:3px solid <?= $dayColors[$dia] ?>;
                      border-radius:10px;padding:12px;margin-bottom:10px">
            <div style="font-weight:600;font-size:.85rem;margin-bottom:4px">
              <?= htmlspecialchars($h['asignatura']) ?>
            </div>
            <div style="font-size:.78rem;color:var(--muted);margin-bottom:6px">
              <?= substr($h['hora_inicio'],0,5) ?> – <?= substr($h['hora_fin'],0,5) ?>
            </div>
            <div style="font-size:.75rem;color:var(--muted)">📍 <?= htmlspecialchars($h['aula'] ?? '—') ?></div>
            <div style="font-size:.75rem;color:var(--muted)">👤 <?= htmlspecialchars($h['profesor']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align:center;padding:20px;color:var(--border);font-size:.8rem">
          Sin clases
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once '../incluye/pie.php'; ?>
