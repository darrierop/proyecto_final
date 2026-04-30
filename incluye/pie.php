</div><!-- /contenido-pagina -->
</div><!-- /contenedor-principal -->

<script>
  // Auto-ocultar alertas
  document.querySelectorAll('.alerta').forEach(el => {
    el.style.transition = 'opacity .4s ease';
    setTimeout(() => el.style.opacity = '0', 4000);
    setTimeout(() => el.remove(), 4400);
  });
</script>
</body>

</html>