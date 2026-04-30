<?php
// incluye/cabecera.php — Cabecera y barra lateral compartida

// Calcula la ruta base del proyecto relativa al document root
$base = rtrim(str_replace('\\', '/', substr(
  str_replace('\\', '/', realpath(__DIR__ . '/..')),
  strlen(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])))
)), '/');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? 'Sistema Académico') ?> — AcademiSys</title>
  <link rel="stylesheet" href="<?= $base ?>/estilos/global.css?v=20260306">
  <script>
    // Aplica tema guardado antes de pintar (evita parpadeo)
    (function(){
      var t = localStorage.getItem('tema');
      if (t === 'oscuro') document.documentElement.setAttribute('data-tema','oscuro');
    })();
  </script>
</head>

<body>
  <?php
  $rol = $_SESSION['rol'];
  $nombre = $_SESSION['nombre_completo'];
  $rolNombre = $_SESSION['rol_nombre'];
  $foto = $_SESSION['foto'] ?? null;
  $iniciales = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice(explode(' ', trim($nombre)), 0, 2)));
  $claseRol = $rol == 1 ? 'rol-admin' : ($rol == 2 ? 'rol-prof' : 'rol-alumno');
  $userId = $_SESSION['usuario_id'];

  $sinLeer = 0;
  if (isset($conn)) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM mensajes WHERE destinatario_id=$userId AND leido=0");
    if ($r)
      $sinLeer = (int) $r->fetch_assoc()['c'];
  }
  $paginaActual = basename($_SERVER['PHP_SELF']);
  ?>

  <!-- ═══ TOAST de notificaciones ═══ -->
  <div id="toast-contenedor"
    style="position:fixed;top:18px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;">
  </div>

  <!-- ═══ MODAL de confirmación de borrado ═══ -->
  <div id="modal-confirmar"
    style="display:none;position:fixed;inset:0;z-index:8000;align-items:center;justify-content:center;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);">
    <div
      style="background:var(--card-bg);border:1px solid var(--borde);border-radius:16px;padding:32px 36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);text-align:center;animation:modalEntrada .2s ease;">
      <div style="font-size:2.5rem;margin-bottom:12px">🗑️</div>
      <h3 style="font-family:'Sora',sans-serif;font-size:1.1rem;color:var(--texto);margin-bottom:8px">¿Eliminar este
        registro?</h3>
      <p id="modal-msg" style="color:var(--texto-3);font-size:.85rem;margin-bottom:24px"></p>
      <div style="display:flex;gap:10px;justify-content:center">
        <button onclick="cerrarModal()" class="btn btn-borde">Cancelar</button>
        <a id="modal-btn-ok" href="#" class="btn" style="background:var(--rojo);color:#fff;border-color:var(--rojo)">Sí,
          eliminar</a>
      </div>
    </div>
  </div>

  <!-- ── Overlay oscuro al abrir sidebar en móvil ── -->
  <div id="sidebar-overlay" onclick="cerrarSidebar()"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-marca">
      <div class="logo-icono">🎓</div>
      <div class="logo-nombre">AcademiSys<span>Gestión Académica</span></div>
    </div>

    <div class="sidebar-perfil">
      <?php if ($foto): ?>
        <img src="<?= $base ?>/uploads/avatares/<?= htmlspecialchars($foto) ?>" class="perfil-avatar"
          style="object-fit:cover;" alt="avatar">
      <?php else: ?>
        <div class="perfil-avatar"><?= $iniciales ?></div>
      <?php endif; ?>
      <div style="min-width:0">
        <div class="perfil-nombre"><?= htmlspecialchars($nombre) ?></div>
        <div class="perfil-rol"><span class="etiqueta-rol <?= $claseRol ?>"><?= htmlspecialchars($rolNombre) ?></span>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-grupo-titulo">General</div>
      <a href="<?= $base ?>/panel.php" class="nav-enlace <?= $paginaActual == 'panel.php' ? 'activo' : '' ?>"><span
          class="nav-icono">🏠</span>Panel Principal</a>
      <a href="<?= $base ?>/mensajes.php" class="nav-enlace <?= $paginaActual == 'mensajes.php' ? 'activo' : '' ?>"
        id="nav-mensajes">
        <span class="nav-icono">✉️</span>Mensajes
        <?php if ($sinLeer > 0): ?><span class="nav-badge" id="badge-msgs"><?= $sinLeer ?></span><?php else: ?><span
            class="nav-badge" id="badge-msgs" style="display:none">0</span><?php endif; ?>
      </a>
      <a href="<?= $base ?>/perfil.php" class="nav-enlace <?= $paginaActual == 'perfil.php' ? 'activo' : '' ?>"><span
          class="nav-icono">👤</span>Mi Perfil</a>

      <?php if ($rol == 1): ?>
        <div class="nav-grupo-titulo">Administración</div>
        <a href="<?= $base ?>/administrador/usuarios.php"
          class="nav-enlace <?= $paginaActual == 'usuarios.php' ? 'activo' : '' ?>"><span
            class="nav-icono">👥</span>Usuarios</a>
        <a href="<?= $base ?>/administrador/asignaturas.php"
          class="nav-enlace <?= $paginaActual == 'asignaturas.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📚</span>Asignaturas</a>
        <a href="<?= $base ?>/administrador/cursos.php"
          class="nav-enlace <?= $paginaActual == 'cursos.php' ? 'activo' : '' ?>"><span
            class="nav-icono">🏫</span>Cursos</a>
        <a href="<?= $base ?>/administrador/aulas.php"
          class="nav-enlace <?= $paginaActual == 'aulas.php' ? 'activo' : '' ?>"><span
            class="nav-icono">🚪</span>Aulas</a>
        <a href="<?= $base ?>/administrador/matriculas.php"
          class="nav-enlace <?= $paginaActual == 'matriculas.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📋</span>Matrículas</a>
        <a href="<?= $base ?>/administrador/avisos.php"
          class="nav-enlace <?= $paginaActual == 'avisos.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📢</span>Avisos</a>
        <a href="<?= $base ?>/administrador/auditoria.php"
          class="nav-enlace <?= $paginaActual == 'auditoria.php' ? 'activo' : '' ?>"><span
            class="nav-icono">🔍</span>Auditoría</a>

      <?php elseif ($rol == 2): ?>
        <div class="nav-grupo-titulo">Docencia</div>
        <a href="<?= $base ?>/profesor/mis_cursos.php"
          class="nav-enlace <?= $paginaActual == 'mis_cursos.php' ? 'activo' : '' ?>"><span class="nav-icono">🏫</span>Mis
          Cursos</a>
        <a href="<?= $base ?>/profesor/calificaciones.php"
          class="nav-enlace <?= $paginaActual == 'calificaciones.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📝</span>Calificaciones</a>
        <a href="<?= $base ?>/profesor/horarios.php"
          class="nav-enlace <?= $paginaActual == 'horarios.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📅</span>Horarios</a>

      <?php else: ?>
        <div class="nav-grupo-titulo">Mi Académico</div>
        <a href="<?= $base ?>/alumno/mis_cursos.php"
          class="nav-enlace <?= $paginaActual == 'mis_cursos.php' ? 'activo' : '' ?>"><span class="nav-icono">📚</span>Mis
          Cursos</a>
        <a href="<?= $base ?>/alumno/calificaciones.php"
          class="nav-enlace <?= $paginaActual == 'calificaciones.php' ? 'activo' : '' ?>"><span
            class="nav-icono">📊</span>Mis
          Notas</a>
        <a href="<?= $base ?>/alumno/horario.php"
          class="nav-enlace <?= $paginaActual == 'horario.php' ? 'activo' : '' ?>"><span class="nav-icono">📅</span>Mi
          Horario</a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-pie">
      <a href="<?= $base ?>/perfil.php">⚙️ Configuración</a>
      <a href="<?= $base ?>/cerrar_sesion.php">🚪 Cerrar Sesión</a>
    </div>
  </aside>

  <div class="contenedor-principal">
    <header class="barra-superior">
      <div class="barra-titulo"><?= htmlspecialchars($tituloPagina ?? '') ?></div>
      <div class="barra-derecha">
        <span class="barra-fecha" id="reloj"></span>
        <button id="btn-tema" title="Cambiar tema" aria-label="Cambiar entre modo día y noche">
          <span class="icono-sol">☀️</span>
          <span class="icono-luna">🌙</span>
        </button>
        <!-- Botón hamburguesa (solo visible en móvil/tablet) -->
        <button id="btn-menu" aria-label="Abrir menú" onclick="toggleSidebar()">
          <span></span><span></span><span></span>
        </button>
      </div>
    </header>
    <div class="contenido-pagina">

      <script>
        // ── Tema ──
        (function(){
          var btn = document.getElementById('btn-tema');
          if (!btn) return;
          btn.addEventListener('click', function(){
            var osc = document.documentElement.getAttribute('data-tema') === 'oscuro';
            if (osc) {
              document.documentElement.removeAttribute('data-tema');
              localStorage.removeItem('tema');
            } else {
              document.documentElement.setAttribute('data-tema','oscuro');
              localStorage.setItem('tema','oscuro');
            }
          });
        })();

        // ── Reloj ──
        function actualizarReloj() {
          var d = new Date(), r = document.getElementById('reloj');
          if (r) r.textContent = [d.getHours(), d.getMinutes(), d.getSeconds()].map(n => String(n).padStart(2, '0')).join(':');
        }
        actualizarReloj(); setInterval(actualizarReloj, 1000);

        // ── Modal de confirmación de borrado ──
        function confirmarBorrar(url, nombre) {
          var m = document.getElementById('modal-confirmar');
          document.getElementById('modal-msg').textContent = 'Se eliminará: "' + nombre + '". Esta acción no se puede deshacer.';
          document.getElementById('modal-btn-ok').href = url;
          m.style.display = 'flex';
        }
        function cerrarModal() {
          document.getElementById('modal-confirmar').style.display = 'none';
        }
        document.getElementById('modal-confirmar').addEventListener('click', function (e) {
          if (e.target === this) cerrarModal();
        });

        // ── Toast helper ──
        function mostrarToast(texto, tipo, duracion) {
          var tc = document.getElementById('toast-contenedor');
          var t = document.createElement('div');
          t.className = 'toast toast-' + tipo;
          t.innerHTML = '<span>' + texto + '</span><button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;line-height:1;margin-left:8px">×</button>';
          t.style.cssText = 'background:var(--card-bg);border:1px solid var(--borde);border-radius:10px;padding:12px 16px;box-shadow:0 8px 24px rgba(0,0,0,.15);pointer-events:all;display:flex;align-items:center;gap:4px;font-size:.84rem;color:var(--texto);min-width:260px;max-width:340px;animation:toastIn .3s ease;';
          if (tipo === 'exito') t.style.borderLeftColor = 'var(--verde)';
          if (tipo === 'aviso') t.style.borderLeftColor = 'var(--naranja)';
          if (tipo === 'info') t.style.borderLeftColor = 'var(--azul)';
          t.style.borderLeftWidth = '4px';
          tc.appendChild(t);
          setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); }, duracion || 4000);
        }

        // ── Filtrar tabla en tiempo real (Mejora 3) ──
        function filtrarTabla(inputId, tablaId) {
          var inp = document.getElementById(inputId), tbl = document.getElementById(tablaId);
          if (!inp || !tbl) return;
          inp.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            tbl.querySelectorAll('tbody tr').forEach(function (tr) {
              tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
          });
        }

        // ── Sidebar móvil ──
        function toggleSidebar() {
          var abierto = document.body.classList.toggle('sidebar-abierto');
          document.getElementById('sidebar').setAttribute('aria-expanded', abierto);
        }
        function cerrarSidebar() {
          document.body.classList.remove('sidebar-abierto');
          document.getElementById('sidebar').setAttribute('aria-expanded', 'false');
        }
        // Cerrar sidebar con Escape
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') cerrarSidebar();
        });
        // Cerrar sidebar al hacer clic en un enlace de nav (en móvil)
        document.querySelectorAll('.nav-enlace').forEach(function(a) {
          a.addEventListener('click', function() {
            if (window.innerWidth <= 768) cerrarSidebar();
          });
        });

        // ── Notificaciones polling (Mejora 1) ──
        var _ultimaConsulta = new Date().toISOString();
        function consultarNotificaciones() {
          fetch('<?= $base ?>/api/notificaciones.php?desde=' + _ultimaConsulta)
            .then(r => r.ok ? r.json() : null)
            .then(function (data) {
              if (!data) return;
              _ultimaConsulta = new Date().toISOString();
              // Actualizar badge de mensajes
              var badge = document.getElementById('badge-msgs');
              if (badge) {
                if (data.sin_leer > 0) { badge.style.display = ''; badge.textContent = data.sin_leer; }
                else { badge.style.display = 'none'; }
              }
              // Mostrar toast por cada mensaje nuevo
              if (data.nuevos && data.nuevos.length) {
                data.nuevos.forEach(function (n) {
                  mostrarToast('✉️ Nuevo mensaje de <strong>' + n.remitente + '</strong>: ' + n.asunto, 'info', 6000);
                });
              }
            }).catch(() => { });
        }
        // Arrancar polling a los 5s de cargar y luego cada 30s
        setTimeout(function () { consultarNotificaciones(); setInterval(consultarNotificaciones, 30000); }, 5000);
      </script>