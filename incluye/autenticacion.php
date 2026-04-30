<?php
session_start();

// Calcula la ruta base del proyecto relativa al document root
function getBase()
{
    $proyectoDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $htdocsDir = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return rtrim(substr($proyectoDir, strlen($htdocsDir)), '/');
}

function estaAutenticado()
{
    return isset($_SESSION['usuario_id']);
}

function requiereLogin()
{
    if (!estaAutenticado()) {
        header('Location: ' . getBase() . '/login.php');
        exit;
    }
}

function requiereRol($roles)
{
    requiereLogin();
    if (!in_array($_SESSION['rol'], (array) $roles)) {
        header('Location: ' . getBase() . '/panel.php');
        exit;
    }
}

function getNombreRol()
{
    return $_SESSION['rol_nombre'] ?? '';
}

function getUsuarioId()
{
    return $_SESSION['usuario_id'] ?? null;
}

function getNombreUsuario()
{
    return $_SESSION['nombre_completo'] ?? '';
}

function cerrarSesion()
{
    session_destroy();
    header('Location: ' . getBase() . '/login.php');
    exit;
}
?>