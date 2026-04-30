<?php
// exportar_pdf.php — Genera expediente académico en formato imprimible
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'incluye/bd.php';

$uid = (int) ($_GET['alumno_id'] ?? $_SESSION['usuario_id']);
$rolSesion = $_SESSION['rol'];

// Solo el propio alumno o un admin/profesor puede ver
if ($rolSesion == 3 && $uid !== (int) $_SESSION['usuario_id']) {
    header('Location: panel.php');
    exit;
}

$alumno = $conn->query("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id=r.id WHERE u.id=$uid")->fetch_assoc();
if (!$alumno) {
    echo 'Alumno no encontrado';
    exit;
}

$matriculas = $conn->query("
    SELECT a.nombre AS asignatura, a.creditos, c.semestre, c.nombre_grupo,
           u.nombre_completo AS profesor, m.nota_final, m.fecha_matricula
    FROM matriculas m
    JOIN cursos c      ON m.curso_id        = c.id
    JOIN asignaturas a ON c.asignatura_id   = a.id
    JOIN usuarios u    ON c.profesor_id     = u.id
    WHERE m.alumno_id = $uid
    ORDER BY c.semestre DESC, a.nombre
");

$promedio = $conn->query("SELECT AVG(nota_final) AS avg FROM matriculas WHERE alumno_id=$uid AND nota_final IS NOT NULL")->fetch_assoc()['avg'];
$total = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$uid")->fetch_assoc()['c'];
$aprobadas = $conn->query("SELECT COUNT(*) AS c FROM matriculas WHERE alumno_id=$uid AND nota_final>=5")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente —
        <?= htmlspecialchars($alumno['nombre_completo']) ?>
    </title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            padding: 30px 40px
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2563eb
        }

        .logo span {
            display: block;
            font-size: .75rem;
            font-weight: 400;
            color: #64748b
        }

        .alumno-info {
            text-align: right;
            font-size: .8rem;
            color: #475569
        }

        .alumno-info strong {
            display: block;
            font-size: 1rem;
            color: #1e293b;
            font-weight: 700
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px
        }

        .stat {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center
        }

        .stat-v {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2563eb
        }

        .stat-l {
            font-size: .68rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .08em
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px
        }

        th {
            background: #f8fafc;
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: .82rem
        }

        .nota-a {
            color: #16a34a;
            font-weight: 600
        }

        .nota-m {
            color: #d97706;
            font-weight: 600
        }

        .nota-b {
            color: #dc2626;
            font-weight: 600
        }

        .pie {
            margin-top: 20px;
            font-size: .72rem;
            color: #94a3b8;
            text-align: center
        }

        @media print {
            body {
                padding: 10px 15px
            }

            .no-print {
                display: none
            }

            button {
                display: none
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom:20px">
        <button onclick="window.print()"
            style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:8px;cursor:pointer;font-size:.85rem">🖨️
            Imprimir / Guardar PDF</button>
        <button onclick="window.history.back()"
            style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:9px 20px;border-radius:8px;cursor:pointer;font-size:.85rem;margin-left:8px">←
            Volver</button>
    </div>

    <div class="cabecera">
        <div class="logo">🎓 AcademiSys<span>Sistema de Gestión Académica</span></div>
        <div class="alumno-info">
            <strong>
                <?= htmlspecialchars($alumno['nombre_completo']) ?>
            </strong>
            @
            <?= htmlspecialchars($alumno['usuario']) ?><br>
            <?= htmlspecialchars($alumno['email']) ?><br>
            Expediente generado:
            <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-v">
                <?= $total ?>
            </div>
            <div class="stat-l">Asignaturas</div>
        </div>
        <div class="stat">
            <div class="stat-v">
                <?= $aprobadas ?>
            </div>
            <div class="stat-l">Aprobadas</div>
        </div>
        <div class="stat">
            <div class="stat-v">
                <?= $total - $aprobadas ?>
            </div>
            <div class="stat-l">Pendientes</div>
        </div>
        <div class="stat">
            <div class="stat-v">
                <?= $promedio ? number_format($promedio, 2) : '—' ?>
            </div>
            <div class="stat-l">Media</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Asignatura</th>
                <th>Créditos</th>
                <th>Semestre</th>
                <th>Grupo</th>
                <th>Profesor</th>
                <th>Nota Final</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($m = $matriculas->fetch_assoc()):
                $nc = $m['nota_final'] !== null ? ($m['nota_final'] >= 7 ? 'nota-a' : ($m['nota_final'] >= 5 ? 'nota-m' : 'nota-b')) : '';
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($m['asignatura']) ?>
                    </td>
                    <td>
                        <?= $m['creditos'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($m['semestre']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($m['nombre_grupo']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($m['profesor']) ?>
                    </td>
                    <td class="<?= $nc ?>">
                        <?= $m['nota_final'] !== null ? number_format($m['nota_final'], 2) : '—' ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pie">Documento generado automáticamente por AcademiSys ·
        <?= date('Y') ?> · Sólo válido como referencia interna
    </div>
</body>

</html>