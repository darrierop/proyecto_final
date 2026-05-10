<?php
require_once 'incluye/autenticacion.php'; // inicia sesión con params seguros
if (estaAutenticado()) {
    header('Location: ' . getBase() . '/panel.php');
} else {
    header('Location: ' . getBase() . '/login.php');
}
exit;
