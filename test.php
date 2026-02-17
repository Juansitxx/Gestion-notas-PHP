<?php
// Archivo simple para verificar conexión a MySQL

// Configura tus datos
$host = 'localhost';
$user = 'root';
$pass = 'toor';
$db   = 'gestion_notas';

// Intentar conexión
$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo "❌ No se pudo conectar a la base de datos.\n";
    echo "Error: " . mysqli_connect_error();
} else {
    echo "✅ Conectado correctamente a la base de datos '$db'.";
}
?>