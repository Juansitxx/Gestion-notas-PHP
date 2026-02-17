<?php
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['mensaje'] = "ID de estudiante inválido";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: index.php');
    exit;
}

// Verificar que el estudiante existe (en la BD principal)
$conexion = conectarDB();
$sql = "SELECT * FROM estudiantes WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id]);
$estudiante = $stmt->fetch();

if (!$estudiante) {
    $_SESSION['mensaje'] = "Estudiante no encontrado";
    $_SESSION['tipo_mensaje'] = "error";
    header('Location: index.php');
    exit;
}

// Eliminar en todas las bases de datos configuradas
$resultado = eliminarEstudiante($id);

if ($resultado['success']) {
    $mensaje = "Estudiante eliminado exitosamente";
    
    // Añadir información sobre dónde se eliminó
    if (DB_TYPE === 'both') {
        $dbs_eliminadas = [];
        foreach ($resultado['resultados'] as $tipo => $res) {
            if ($res['success']) {
                $dbs_eliminadas[] = strtoupper($tipo);
            }
        }
        if (!empty($dbs_eliminadas)) {
            $mensaje .= " de: " . implode(' y ', $dbs_eliminadas);
        }
    }
    
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = "exito";
} else {
    // Hubo errores en alguna BD
    $errores_msg = "Error al eliminar el estudiante: ";
    $errores_detalles = [];
    foreach ($resultado['errores'] as $tipo => $error) {
        $errores_detalles[] = strtoupper($tipo) . ": " . $error;
    }
    $_SESSION['mensaje'] = $errores_msg . implode('; ', $errores_detalles);
    $_SESSION['tipo_mensaje'] = "error";
}

header('Location: index.php');
exit;
?>