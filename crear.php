<?php
require_once 'config.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $identificacion = trim($_POST['identificacion'] ?? '');
    $nota1 = floatval($_POST['nota1'] ?? 0);
    $nota2 = floatval($_POST['nota2'] ?? 0);
    $nota3 = floatval($_POST['nota3'] ?? 0);

    // Validaciones
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($apellido)) $errores[] = "El apellido es obligatorio";
    if (empty($identificacion)) $errores[] = "La identificación es obligatoria";
    if ($nota1 < 0 || $nota1 > 5) $errores[] = "La nota 1 debe estar entre 0 y 5";
    if ($nota2 < 0 || $nota2 > 5) $errores[] = "La nota 2 debe estar entre 0 y 5";
    if ($nota3 < 0 || $nota3 > 5) $errores[] = "La nota 3 debe estar entre 0 y 5";

    if (empty($errores)) {
        // Insertar en todas las bases de datos configuradas
        $resultado = insertarEstudiante($nombre, $apellido, $identificacion, $nota1, $nota2, $nota3);
        
        if ($resultado['success']) {
            $mensaje = "Estudiante creado exitosamente";
            
            // Añadir información sobre dónde se guardó
            if (DB_TYPE === 'both') {
                $dbs_guardadas = [];
                foreach ($resultado['resultados'] as $tipo => $res) {
                    if ($res['success']) {
                        $dbs_guardadas[] = strtoupper($tipo);
                    }
                }
                if (!empty($dbs_guardadas)) {
                    $mensaje .= " en: " . implode(' y ', $dbs_guardadas);
                }
            }
            
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = "exito";
            header('Location: index.php');
            exit;
        } else {
            // Hubo errores en alguna BD
            $errores[] = "Error al crear el estudiante en algunas bases de datos:";
            foreach ($resultado['errores'] as $tipo => $error) {
                if (strpos($error, 'Duplicate entry') !== false) {
                    $errores[] = "La identificación ya existe en " . strtoupper($tipo);
                } else {
                    $errores[] = strtoupper($tipo) . ": " . $error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Estudiante</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>➕ Agregar Nuevo Estudiante</h1>
            <p class="db-info">Guardando en: <strong><?php 
                if (DB_TYPE === 'both') {
                    echo 'LOCAL + AIVEN';
                } else {
                    echo strtoupper(DB_TYPE);
                }
            ?></strong></p>
        </header>

        <?php if (!empty($errores)): ?>
            <div class="mensaje error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="formulario">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="identificacion">Identificación:</label>
                <input type="text" id="identificacion" name="identificacion" value="<?php echo htmlspecialchars($_POST['identificacion'] ?? ''); ?>" required>
            </div>

            <div class="notas-group">
                <div class="form-group">
                    <label for="nota1">Nota 1 (30%):</label>
                    <input type="number" id="nota1" name="nota1" min="0" max="5" step="0.1" value="<?php echo $_POST['nota1'] ?? '0'; ?>" required>
                </div>

                <div class="form-group">
                    <label for="nota2">Nota 2 (30%):</label>
                    <input type="number" id="nota2" name="nota2" min="0" max="5" step="0.1" value="<?php echo $_POST['nota2'] ?? '0'; ?>" required>
                </div>

                <div class="form-group">
                    <label for="nota3">Nota 3 (40%):</label>
                    <input type="number" id="nota3" name="nota3" min="0" max="5" step="0.1" value="<?php echo $_POST['nota3'] ?? '0'; ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar</button>
                <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>