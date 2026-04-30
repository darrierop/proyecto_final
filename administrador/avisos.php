<?php
// administrador/avisos.php — CRUD de avisos/anuncios
require_once '../incluye/autenticacion.php';
requiereRol(1);
require_once '../incluye/bd.php';

$msg = $err = '';

// CREAR
if ($_POST['accion'] ?? '' === 'crear') {
    $titulo = trim($_POST['titulo'] ?? '');
    $cuerpo = trim($_POST['cuerpo'] ?? '');
    $tipo = $_POST['tipo'] ?? 'Info';
    $expira = $_POST['expira'] ?? null;
    if ($titulo && $cuerpo) {
        $t = $conn->real_escape_string($titulo);
        $c = $conn->real_escape_string($cuerpo);
        $ti = $conn->real_escape_string($tipo);
        $e = $expira ? "'" . $conn->real_escape_string($expira) . "'" : 'NULL';
        $uid = $_SESSION['usuario_id'];
        $conn->query("INSERT INTO avisos (titulo, cuerpo, tipo, expira, creado_por) VALUES ('$t','$c','$ti',$e,$uid)");
        $msg = 'Aviso publicado correctamente.';
    } else {
        $err = 'Título y cuerpo son obligatorios.';
    }
}

// BORRAR
if (isset($_GET['borrar'])) {
    $id = (int) $_GET['borrar'];
    $conn->query("DELETE FROM avisos WHERE id=$id");
    $msg = 'Aviso eliminado.';
}

// ACTIVAR/DESACTIVAR
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $conn->query("UPDATE avisos SET activo = NOT activo WHERE id=$id");
    header('Location: avisos.php');
    exit;
}

$avisos = $conn->query("SELECT a.*, u.nombre_completo FROM avisos a LEFT JOIN usuarios u ON a.creado_por=u.id ORDER BY a.fecha_creacion DESC");

$tituloPagina = 'Gestión de Avisos';
require_once '../incluye/cabecera.php';

$tipoClase = ['Info' => 'etiqueta-azul', 'Alerta' => 'etiqueta-amarilla', 'Urgente' => 'etiqueta-roja', 'Exito' => 'etiqueta-verde'];
$tipoIco = ['Info' => 'ℹ️', 'Alerta' => '⚠️', 'Urgente' => '🚨', 'Exito' => '✅'];
?>
<?php if ($msg): ?>
    <div class="alerta alerta-exito">✅
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="alerta alerta-error">❌
        <?= htmlspecialchars($err) ?>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start">
    <!-- Lista de avisos -->
    <div class="tarjeta">
        <div class="tarjeta-titulo">📢 Avisos publicados</div>
        <?php
        $hayAvisos = false;
        while ($av = $avisos->fetch_assoc()):
            $hayAvisos = true;
            $cls = $tipoClase[$av['tipo']] ?? 'etiqueta-gris';
            $ico = $tipoIco[$av['tipo']] ?? '📌';
            $expirado = $av['expira'] && strtotime($av['expira']) < time();
            ?>
            <div
                style="border:1px solid var(--borde);border-radius:10px;padding:14px 16px;margin-bottom:12px;opacity:<?= $av['activo'] && !$expirado ? '1' : '.55' ?>">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <span>
                        <?= $ico ?>
                    </span>
                    <span style="font-weight:600;font-size:.9rem">
                        <?= htmlspecialchars($av['titulo']) ?>
                    </span>
                    <span class="etiqueta <?= $cls ?>">
                        <?= $av['tipo'] ?>
                    </span>
                    <?php if (!$av['activo']): ?><span class="etiqueta etiqueta-gris">Pausado</span>
                    <?php endif; ?>
                    <?php if ($expirado): ?><span class="etiqueta etiqueta-roja">Expirado</span>
                    <?php endif; ?>
                </div>
                <p style="font-size:.83rem;color:var(--texto-2);margin-bottom:10px">
                    <?= htmlspecialchars($av['cuerpo']) ?>
                </p>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:.72rem;color:var(--texto-3)">Por
                        <?= htmlspecialchars($av['nombre_completo'] ?? '') ?> ·
                        <?= date('d/m/Y H:i', strtotime($av['fecha_creacion'])) ?>
                        <?= $av['expira'] ? ' · Expira: ' . date('d/m/Y', strtotime($av['expira'])) : '' ?>
                    </span>
                    <div style="display:flex;gap:6px">
                        <a href="?toggle=<?= $av['id'] ?>" class="btn btn-borde btn-sm">
                            <?= $av['activo'] ? '⏸ Pausar' : '▶️ Activar' ?>
                        </a>
                        <button
                            onclick="confirmarBorrar('avisos.php?borrar=<?= $av['id'] ?>','<?= htmlspecialchars(addslashes($av['titulo'])) ?>')"
                            class="btn btn-peligro-sm btn-sm">🗑️ Borrar</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <?php if (!$hayAvisos): ?>
            <p style="text-align:center;color:var(--texto-3);padding:20px">No hay avisos publicados.</p>
        <?php endif; ?>
    </div>

    <!-- Formulario nuevo aviso -->
    <div class="tarjeta">
        <div class="tarjeta-titulo">➕ Nuevo aviso</div>
        <form method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="grupo-formulario">
                <label>Título</label>
                <input type="text" name="titulo" required placeholder="Título del aviso" maxlength="150">
            </div>
            <div class="grupo-formulario">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="Info">ℹ️ Información</option>
                    <option value="Alerta">⚠️ Alerta</option>
                    <option value="Urgente">🚨 Urgente</option>
                    <option value="Exito">✅ Éxito / Buenas noticias</option>
                </select>
            </div>
            <div class="grupo-formulario">
                <label>Mensaje</label>
                <textarea name="cuerpo" required rows="4" placeholder="Escribe el contenido del aviso..."></textarea>
            </div>
            <div class="grupo-formulario">
                <label>Fecha de expiración (opcional)</label>
                <input type="date" name="expira">
            </div>
            <button type="submit" class="btn btn-primario" style="width:100%">📢 Publicar aviso</button>
        </form>
    </div>
</div>

<?php require_once '../incluye/pie.php'; ?>