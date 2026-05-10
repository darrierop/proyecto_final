<?php
// incluye/cabecera.php
$base = rtrim(str_replace("\\", "/", substr(
  str_replace("\\", "/", realpath(__DIR__ . "/..")),
  strlen(str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"])))
)), "/");

// Timeout de inactividad (30 min) — redirige al login si expira
comprobarTimeout(30);

// Enviar cabeceras de seguridad HTTP en todas las páginas autenticadas
enviarCabecerasSeguridad();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? "Sistema Academico") ?> — AcademiSys</title>
  <link rel="stylesheet" href="<?= $base ?>/estilos/global.css?v=20260510b">
  <script>(function(){var t=localStorage.getItem("tema");if(t==="oscuro")document.documentElement.setAttribute("data-tema","oscuro");})();</script>
</head>
<body>
<?php
$rol = $_SESSION["rol"];
$nombre = $_SESSION["nombre_completo"];
$rolNombre = $_SESSION["rol_nombre"];
$foto = $_SESSION["foto"] ?? null;
$iniciales = implode("", array_map(fn($p) => strtoupper($p[0]), array_slice(explode(" ", trim($nombre)), 0, 2)));
$claseRol = $rol == 1 ? "rol-admin" : ($rol == 2 ? "rol-prof" : "rol-alumno");
$userId = $_SESSION["usuario_id"];
$sinLeer = 0;
if (isset($conn)) {
  $r = $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE destinatario_id=$userId AND leido=0");
  if ($r) $sinLeer = (int) $r->fetch_assoc()["c"];
}
$paginaActual = basename($_SERVER["PHP_SELF"]);
$navLinks = [];
$navLinks[] = ["url" => "$base/panel.php",    "ico" => "🏠", "txt" => "Panel",    "p" => "panel.php"];
$navLinks[] = ["url" => "$base/mensajes.php",  "ico" => "✉️", "txt" => "Mensajes", "p" => "mensajes.php", "badge" => $sinLeer];
if ($rol == 1) {
  $navLinks[] = ["sep" => true];
  $navLinks[] = ["url" => "$base/administrador/usuarios.php",    "ico" => "👥", "txt" => "Usuarios",    "p" => "usuarios.php"];
  $navLinks[] = ["url" => "$base/administrador/asignaturas.php", "ico" => "📚", "txt" => "Asignaturas", "p" => "asignaturas.php"];
  $navLinks[] = ["url" => "$base/administrador/cursos.php",      "ico" => "🏫", "txt" => "Cursos",      "p" => "cursos.php"];
  $navLinks[] = ["url" => "$base/administrador/aulas.php",       "ico" => "🚪", "txt" => "Aulas",       "p" => "aulas.php"];
  $navLinks[] = ["url" => "$base/administrador/matriculas.php",  "ico" => "📋", "txt" => "Matriculas",  "p" => "matriculas.php"];
  $navLinks[] = ["url" => "$base/administrador/avisos.php",      "ico" => "📢", "txt" => "Avisos",      "p" => "avisos.php"];
  $navLinks[] = ["url" => "$base/administrador/auditoria.php",   "ico" => "🔍", "txt" => "Auditoria",   "p" => "auditoria.php"];
} elseif ($rol == 2) {
  $navLinks[] = ["sep" => true];
  $navLinks[] = ["url" => "$base/profesor/mis_cursos.php",      "ico" => "🏫", "txt" => "Mis Cursos",      "p" => "mis_cursos.php"];
  $navLinks[] = ["url" => "$base/profesor/calificaciones.php",  "ico" => "📝", "txt" => "Calificaciones", "p" => "calificaciones.php"];
  $navLinks[] = ["url" => "$base/profesor/horarios.php",        "ico" => "📅", "txt" => "Horarios",       "p" => "horarios.php"];
} else {
  $navLinks[] = ["sep" => true];
  $navLinks[] = ["url" => "$base/alumno/mis_cursos.php",      "ico" => "📚", "txt" => "Mis Cursos", "p" => "mis_cursos.php"];
  $navLinks[] = ["url" => "$base/alumno/calificaciones.php",  "ico" => "📊", "txt" => "Mis Notas",  "p" => "calificaciones.php"];
  $navLinks[] = ["url" => "$base/alumno/horario.php",         "ico" => "📅", "txt" => "Horario",    "p" => "horario.php"];
}
?>

<!-- TOAST -->
<div id="toast-contenedor" style="position:fixed;top:106px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

<!-- MODAL borrado -->
<div id="modal-confirmar" style="display:none;position:fixed;inset:0;z-index:8000;align-items:center;justify-content:center;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);">
  <div style="background:var(--card-bg,#fff);border:1px solid var(--borde,#e2e8f0);border-radius:16px;padding:32px 36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);text-align:center;animation:modalEntrada .2s ease;">
    <div style="font-size:2.5rem;margin-bottom:12px">🗑️</div>
    <h3 style="font-size:1.1rem;color:var(--texto,#0f172a);margin-bottom:8px">¿Eliminar este registro?</h3>
    <p id="modal-msg" style="color:var(--texto3,#94a3b8);font-size:.85rem;margin-bottom:24px"></p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button onclick="cerrarModal()" class="btn btn-borde">Cancelar</button>
      <a id="modal-btn-ok" href="#" class="btn" style="background:#dc2626;color:#fff;border-color:#dc2626">Sí, eliminar</a>
    </div>
  </div>
</div>

<!-- MOBILE NAV OVERLAY -->
<div class="nav-mobile-overlay" id="nav-overlay" onclick="toggleNav()">
  <div class="nav-mobile-panel" onclick="event.stopPropagation()">
<?php foreach ($navLinks as $nl): ?>
<?php if (!empty($nl["sep"])): ?>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:4px 0">
<?php else: ?>
    <a href="<?= $nl["url"] ?>" class="nav-pill <?= $paginaActual == $nl["p"] ? "activo" : "" ?>">
      <span class="nav-icono"><?= $nl["ico"] ?></span><?= $nl["txt"] ?>
      <?php if (!empty($nl["badge"]) && $nl["badge"] > 0): ?><span class="nav-badge" id="badge-msgs-mob"><?= $nl["badge"] ?></span><?php endif; ?>
    </a>
<?php endif; ?>
<?php endforeach; ?>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:4px 0">
    <a href="<?= $base ?>/perfil.php" class="nav-pill <?= $paginaActual=="perfil.php"?"activo":"" ?>">👤 Mi Perfil</a>
    <a href="<?= $base ?>/cerrar_sesion.php" class="nav-pill" style="color:#dc2626">🚪 Cerrar Sesión</a>
  </div>
</div>

<!-- TOPBAR -->
<header class="topbar" id="topbar">
  <a href="<?= $base ?>/panel.php" class="topbar-logo">
    <div class="logo-icono">🎓</div>
    <div class="logo-nombre">AcademiSys<span>Gestión Académica</span></div>
  </a>

  <nav class="topbar-nav" id="topbar-nav">
<?php foreach ($navLinks as $nl): ?>
<?php if (!empty($nl["sep"])): ?>
    <span class="nav-separador"></span>
<?php else: ?>
    <a href="<?= $nl["url"] ?>" class="nav-pill <?= $paginaActual == $nl["p"] ? "activo" : "" ?>">
      <span class="nav-icono"><?= $nl["ico"] ?></span><?= $nl["txt"] ?>
      <?php if (!empty($nl["badge"]) && $nl["badge"] > 0): ?><span class="nav-badge" id="badge-msgs"><?= $nl["badge"] ?></span><?php endif; ?>
    </a>
<?php endif; ?>
<?php endforeach; ?>
  </nav>

  <div class="topbar-acciones">
    <button id="btn-tema" title="Cambiar tema" aria-label="Modo claro/oscuro">
      <span class="icono-sol">☀️</span><span class="icono-luna">🌙</span>
    </button>
    <div class="user-pill-wrap">
      <div class="user-pill" id="user-pill" onclick="toggleUserMenu()">
        <div class="user-avatar"><?php if($foto):?><img src="<?= $base ?>/uploads/avatares/<?= htmlspecialchars($foto) ?>" alt="avatar"><?php else:?><?= $iniciales ?><?php endif;?></div>
        <span class="user-nombre"><?= htmlspecialchars(explode(" ", $nombre)[0]) ?></span>
        <span class="user-chevron">▾</span>
      </div>
      <div class="user-menu" id="user-menu">
        <div style="padding:8px 12px 6px;border-bottom:1px solid #e2e8f0;margin-bottom:4px">
          <div style="font-size:.82rem;font-weight:600;color:#0f172a"><?= htmlspecialchars($nombre) ?></div>
          <div style="margin-top:3px"><span class="etiqueta-rol <?= $claseRol ?>"><?= htmlspecialchars($rolNombre) ?></span></div>
        </div>
        <a href="<?= $base ?>/perfil.php">👤 Mi Perfil</a>
        <hr class="user-menu-divider">
        <a href="<?= $base ?>/cerrar_sesion.php" class="menu-peligro">🚪 Cerrar Sesión</a>
      </div>
    </div>
    <button id="btn-menu" aria-label="Abrir menú" onclick="toggleNav()">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- CONTEXTO BAR -->
<div class="contexto-bar">
  <div class="contexto-ruta">
    <span>📍</span>
    <strong><?= htmlspecialchars($tituloPagina ?? "") ?></strong>
  </div>
</div>

<div class="contenedor-principal">
  <div class="contenido-pagina">

<script>
// Tema
(function(){
  var btn = document.getElementById("btn-tema");
  if (!btn) return;
  btn.addEventListener("click", function(){
    var osc = document.documentElement.getAttribute("data-tema") === "oscuro";
    if (osc) { document.documentElement.removeAttribute("data-tema"); localStorage.removeItem("tema"); }
    else { document.documentElement.setAttribute("data-tema","oscuro"); localStorage.setItem("tema","oscuro"); }
  });
})();

// User menu
function toggleUserMenu() {
  var pill = document.getElementById("user-pill");
  var menu = document.getElementById("user-menu");
  pill.classList.toggle("abierto");
  menu.classList.toggle("abierto");
}
document.addEventListener("click", function(e) {
  var wrap = document.querySelector(".user-pill-wrap");
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById("user-pill").classList.remove("abierto");
    document.getElementById("user-menu").classList.remove("abierto");
  }
});

// Mobile nav
function toggleNav() {
  document.body.classList.toggle("nav-abierto");
}

// Modal borrado
function confirmarBorrar(url, nombre) {
  var m = document.getElementById("modal-confirmar");
  document.getElementById("modal-msg").textContent = "Se eliminara: \"" + nombre + "\". Esta accion no se puede deshacer.";
  document.getElementById("modal-btn-ok").href = url;
  m.style.display = "flex";
}
function cerrarModal() { document.getElementById("modal-confirmar").style.display = "none"; }
document.getElementById("modal-confirmar").addEventListener("click", function(e){ if(e.target===this) cerrarModal(); });

// Toast
function mostrarToast(texto, tipo, duracion) {
  var tc = document.getElementById("toast-contenedor");
  var t = document.createElement("div");
  t.className = "toast toast-" + tipo;
  t.innerHTML = "<span>" + texto + "</span><button onclick=\"this.parentElement.remove()\" style=\"background:none;border:none;color:inherit;cursor:pointer;font-size:16px;margin-left:8px\">×</button>";
  t.style.cssText = "background:var(--card-bg,#fff);border:1px solid var(--borde,#e2e8f0);border-radius:10px;padding:12px 16px;box-shadow:0 8px 24px rgba(0,0,0,.15);pointer-events:all;display:flex;align-items:center;gap:4px;font-size:.84rem;color:var(--texto,#0f172a);min-width:260px;max-width:340px;animation:toastIn .3s ease;";
  if (tipo==="exito") t.style.borderLeftColor="var(--verde,#16a34a)";
  if (tipo==="aviso") t.style.borderLeftColor="var(--naranja,#d97706)";
  if (tipo==="info")  t.style.borderLeftColor="var(--azul,#2563eb)";
  t.style.borderLeftWidth="4px";
  tc.appendChild(t);
  setTimeout(function(){ t.style.opacity="0"; t.style.transition="opacity .4s"; setTimeout(function(){ t.remove(); },400); }, duracion||4000);
}

// Filtrar tabla
function filtrarTabla(inputId, tablaId) {
  var inp = document.getElementById(inputId), tbl = document.getElementById(tablaId);
  if (!inp || !tbl) return;
  inp.addEventListener("input", function(){
    var q = this.value.toLowerCase();
    tbl.querySelectorAll("tbody tr").forEach(function(tr){ tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none"; });
  });
}

// Notificaciones polling
var _ultimaConsulta = new Date().toISOString();
function consultarNotificaciones() {
  fetch("<?= $base ?>/api/notificaciones.php?desde=" + _ultimaConsulta)
    .then(function(r){ return r.ok ? r.json() : null; })
    .then(function(data){
      if (!data) return;
      _ultimaConsulta = new Date().toISOString();
      var badge = document.getElementById("badge-msgs");
      var badgeMob = document.getElementById("badge-msgs-mob");
      if (badge) { if(data.sin_leer>0){badge.style.display="";badge.textContent=data.sin_leer;}else{badge.style.display="none";} }
      if (badgeMob) { if(data.sin_leer>0){badgeMob.style.display="";badgeMob.textContent=data.sin_leer;}else{badgeMob.style.display="none";} }
      if (data.nuevos && data.nuevos.length) data.nuevos.forEach(function(n){ mostrarToast("✉️ Mensaje de <strong>" + n.remitente + "</strong>: " + n.asunto,"info",6000); });
    }).catch(function(){});
}
setTimeout(function(){ consultarNotificaciones(); setInterval(consultarNotificaciones, 30000); }, 5000);
</script>
