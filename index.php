<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: panel.php');
} else {
    header('Location: login.php');
}
exit;
?>
