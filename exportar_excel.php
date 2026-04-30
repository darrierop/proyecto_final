<?php
// exportar_excel.php — Exporta datos como CSV (compatible con Excel)
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'incluye/bd.php';

$tipo = $_GET['tipo'] ?? 'expediente';
$uid = (int) ($_GET['alumno_id'] ?? $_SESSION['usuario_id']);
$rolSesion = $_SESSION['rol'];

// Protección: alumno solo puede ver sus datos
if ($rolSesion == 3 && $uid !== (int) $_SESSION['usuario_id']) {
    header('Location: panel.php');
    exit;
}

// Cabeceras para descarga
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="expediente_' . $uid . '_' . date('Ymd') . '.csv"');
header('Cache-Control: no-cache');

// BOM para que Excel abra el archivo con tildes bien
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

if ($tipo === 'expediente') {
    $alumno = $conn->query("SELECT nombre_completo, usuario FROM usuarios WHERE id=$uid")->fetch_assoc();
    fputcsv($out, ['Expediente académico: ' . $alumno['nombre_completo']], ';');
    fputcsv($out, ['Generado: ' . date('d/m/Y H:i')], ';');
    fputcsv($out, [], ';');
    fputcsv($out, ['Asignatura', 'Créditos', 'Semestre', 'Grupo', 'Profesor', 'Nota Final', 'Estado'], ';');

    $matriculas = $conn->query("
        SELECT a.nombre AS asignatura, a.creditos, c.semestre, c.nombre_grupo,
               u.nombre_completo AS profesor, m.nota_final
        FROM matriculas m
        JOIN cursos c      ON m.curso_id = c.id
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN usuarios u    ON c.profesor_id = u.id
        WHERE m.alumno_id = $uid
        ORDER BY c.semestre DESC, a.nombre
    ");
    while ($m = $matriculas->fetch_assoc()) {
        $estado = $m['nota_final'] === null ? 'Pendiente' : ($m['nota_final'] >= 5 ? 'Aprobada' : 'Suspensa');
        fputcsv($out, [
            $m['asignatura'],
            $m['creditos'],
            $m['semestre'],
            $m['nombre_grupo'],
            $m['profesor'],
            $m['nota_final'] !== null ? number_format($m['nota_final'], 2) : '—',
            $estado
        ], ';');
    }
} elseif ($tipo === 'usuarios' && $rolSesion == 1) {
    fputcsv($out, ['ID', 'Usuario', 'Nombre', 'Email', 'Rol', 'Fecha Registro'], ';');
    $res = $conn->query("SELECT u.id,u.usuario,u.nombre_completo,u.email,r.nombre,u.fecha_creacion FROM usuarios u JOIN roles r ON u.rol_id=r.id ORDER BY u.id");
    while ($r = $res->fetch_assoc())
        fputcsv($out, array_values($r), ';');
} elseif ($tipo === 'notas' && in_array($rolSesion, [1, 2])) {
    $cursoId = (int) ($_GET['curso_id'] ?? 0);
    fputcsv($out, ['Alumno', 'Asignatura', 'Actividad', 'Tipo', 'Nota', 'Peso', 'Fecha'], ';');
    $where = $cursoId ? "AND c.id=$cursoId" : '';
    $res = $conn->query("
        SELECT u.nombre_completo AS alumno, a.nombre AS asignatura,
               cal.nombre_actividad, cal.tipo, cal.nota, cal.peso, cal.fecha
        FROM calificaciones cal
        JOIN matriculas m  ON cal.matricula_id = m.id
        JOIN cursos c      ON m.curso_id = c.id
        JOIN asignaturas a ON c.asignatura_id = a.id
        JOIN usuarios u    ON m.alumno_id = u.id
        WHERE 1=1 $where
        ORDER BY u.nombre_completo, cal.fecha
    ");
    while ($r = $res->fetch_assoc())
        fputcsv($out, array_values($r), ';');
}

fclose($out);
exit;
