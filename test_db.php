<?php
// Script de diagnóstico temporal — borrar después de verificar
echo "<h2>Diagnóstico de conexión a BD</h2><pre>";

// 1. Leer .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ Archivo .env encontrado en: $envPath\n";
    $lineas = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $l) {
        if (str_starts_with(trim($l), '#')) continue;
        if (!str_contains($l, '=')) continue;
        [$k, $v] = explode('=', $l, 2);
        $_ENV[trim($k)] = trim($v);
        echo "   " . trim($k) . " = " . trim($v) . "\n";
    }
} else {
    echo "❌ No se encontró .env en: $envPath\n";
}

echo "\n";

// 2. Intentar conexión
$host     = $_ENV['DB_HOST']     ?? 'localhost';
$usuario  = $_ENV['DB_USUARIO']  ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$nombre   = $_ENV['DB_NOMBRE']   ?? 'sistemaacademico';

echo "Intentando conectar a:\n";
echo "  Host:    $host\n";
echo "  Usuario: $usuario\n";
echo "  BD:      $nombre\n\n";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $usuario, $password, $nombre);

if ($conn->connect_error) {
    echo "❌ ERROR de conexión: " . $conn->connect_error . "\n";
    echo "   Código: " . $conn->connect_errno . "\n";
    
    // Intentar con 127.0.0.1
    echo "\nIntentando con 127.0.0.1...\n";
    $conn2 = new mysqli('127.0.0.1', $usuario, $password, $nombre);
    if ($conn2->connect_error) {
        echo "❌ También falló con 127.0.0.1: " . $conn2->connect_error . "\n";
    } else {
        echo "✅ Conexión exitosa con 127.0.0.1!\n";
        echo "   → Cambia DB_HOST=localhost a DB_HOST=127.0.0.1 en el .env\n";
        $conn2->close();
    }
} else {
    echo "✅ Conexión exitosa!\n";
    $res = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    if ($res) {
        $fila = $res->fetch_assoc();
        echo "✅ Tabla usuarios: " . $fila['total'] . " registros\n";
    }
    $conn->close();
}

echo "</pre>";
?>
