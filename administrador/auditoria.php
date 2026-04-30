<?php
// administrador/auditoria.php — Historial de cambios del sistema
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';
require_once '../incluye/paginar.php';

// Filtros
$filtroAccion = $_GET['accion'] ?? '';
$filtroUsuario = $_GET['usuario'] ?? '';

$where = '1=1';
if ($filtroAccion)
    $where .= " AND a.accion = '" . $conn->real_escape_string($filtroAccion) . "'";
if ($filtroUsuario)
    $where .= " AND a.usuario_id = " . (int) $filtroUsuario;

$total = (int) $conn->query("SELECT COUNT(*) AS c FROM auditoria a WHERE $where")->fetch_assoc()['c'];
$pag = paginar($total, 20);

$registros = $conn->query("
    SELECT a.*, u.nombre_completo, u.usuario
    FROM auditoria a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE $where
    ORDER BY a.fecha DESC
    LIMIT {$pag['porPagina']} OFFSET {$pag['offset']}
");

$usuarios = $conn->query("SELECT id, nombre_completo, usuario FROM usuarios ORDER BY nombre_completo");

$tituloPagina = 'Auditoría del Sistema';
require_once '../incluye/cabecera.php';

$iconAccion = [
    'CREAR' => '✅',
    'EDITAR' => '✏️',
    'BORRAR' => '🗑️',
    'LOGIN' => '🔑',
    'NOTA' => '📝',
    'UPLOAD' => '📤',
];
?>
<div class="tarjeta" style="margin-bottom:18px">
    <div class="tarjeta-titulo">🔍 Filtros de auditoría</div>
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div class="grupo-formulario" style="margin:0;flex:1;min-width:160px">
            <label>Acción</label>
            <select name="accion">
                <option value="">Todas</option>
                <?php foreach (['CREAR', 'EDITAR', 'BORRAR', 'LOGIN', 'NOTA', 'UPLOAD'] as $a): ?>
                    <option value="<?= $a ?>" <?= $filtroAccion === $a ? 'selected' : '' ?>>
                        <?= $a ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grupo-formulario" style="margin:0;flex:2;min-width:200px">
            <label>Usuario</label>
            <select name="usuario">
                <option value="">Todos</option>
                <?php while ($u = $usuarios->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>" <?= $filtroUsuario == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nombre_completo']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primario" style="height:36px">Filtrar</button>
        <a href="auditoria.php" class="btn btn-borde" style="height:36px">Limpiar</a>
    </form>
</div>

<div class="tarjeta">
    <div class="tarjeta-titulo">
        📋 Registro de auditoría
        <span class="etiqueta etiqueta-azul">
            <?= $total ?> entradas
        </span>
    </div>
    <div class="tabla-contenedor">
        <table id="tbl-auditoria">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>ID</th>
                    <th>Detalle</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $registros->fetch_assoc()):
                    $ico = $iconAccion[$r['accion']] ?? '📌';
                    $clsMap = ['CREAR' => 'etiqueta-verde', 'EDITAR' => 'etiqueta-azul', 'BORRAR' => 'etiqueta-roja', 'LOGIN' => 'etiqueta-amarilla', 'NOTA' => 'etiqueta-morada', 'UPLOAD' => 'etiqueta-gris'];
                    $cls = $clsMap[$r['accion']] ?? 'etiqueta-gris';
                    ?>
                    <tr>
                        <td style="white-space:nowrap;font-size:.78rem;color:var(--texto-3)">
                            <?= date('d/m/Y H:i:s', strtotime($r['fecha'])) ?>
                        </td>
                        <td><span style="font-weight:500">
                                <?= htmlspecialchars($r['nombre_completo'] ?? '—') ?>
                            </span><br><span style="font-size:.72rem;color:var(--texto-3)">@
                                <?= htmlspecialchars($r['usuario'] ?? '') ?>
                            </span></td>
                        <td>
                            <?= $ico ?> <span class="etiqueta <?= $cls ?>">
                                <?= $r['accion'] ?>
                            </span>
                        </td>
                        <td><code
                                style="font-size:.75rem;background:var(--bg);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($r['tabla_afectada'] ?? '—') ?></code>
                        </td>
                        <td>
                            <?= $r['registro_id'] ?? '—' ?>
                        </td>
                        <td style="font-size:.82rem;color:var(--texto-2);max-width:260px">
                            <?= htmlspecialchars($r['detalle'] ?? '') ?>
                        </td>
                        <td style="font-size:.72rem;color:var(--texto-3)">
                            <?= htmlspecialchars($r['ip'] ?? '') ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?= renderPaginacion($pag, 'auditoria.php') ?>
</div>

<?php require_once '../incluye/pie.php'; ?>