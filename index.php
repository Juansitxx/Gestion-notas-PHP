<?php
require_once 'config.php';

// Obtener todos los estudiantes
$conexion = conectarDB();
$sql = "SELECT * FROM estudiantes ORDER BY apellido, nombre";
$stmt = $conexion->query($sql);
$estudiantes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Notas - Estudiantes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 Sistema de Gestión de Notas</h1>
            <p class="db-info">Conectado a: <strong><?php echo strtoupper(DB_TYPE); ?></strong></p>
        </header>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje <?php echo $_SESSION['tipo_mensaje']; ?>">
                <?php 
                    echo $_SESSION['mensaje']; 
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['tipo_mensaje']);
                ?>
            </div>
        <?php endif; ?>

        <div class="acciones">
            <a href="crear.php" class="btn btn-primary">➕ Agregar Estudiante</a>
            <?php if (DB_TYPE === 'both'): ?>
                <a href="sincronizar.php" class="btn btn-secondary">🔄 Panel de Sincronización</a>
            <?php endif; ?>
        </div>

        <div class="tabla-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Identificación</th>
                        <th>Nota 1 (30%)</th>
                        <th>Nota 2 (30%)</th>
                        <th>Nota 3 (40%)</th>
                        <th>Nota Final</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($estudiantes) > 0): ?>
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <?php $estado = obtenerEstado($estudiante['nota_final']); ?>
                            <tr>
                                <td><?php echo $estudiante['id']; ?></td>
                                <td><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['identificacion']); ?></td>
                                <td><?php echo number_format($estudiante['nota1'], 1); ?></td>
                                <td><?php echo number_format($estudiante['nota2'], 1); ?></td>
                                <td><?php echo number_format($estudiante['nota3'], 1); ?></td>
                                <td><strong><?php echo number_format($estudiante['nota_final'], 1); ?></strong></td>
                                <td><span class="estado <?php echo $estado['clase']; ?>"><?php echo $estado['estado']; ?></span></td>
                                <td class="acciones-tabla">
                                    <a href="editar.php?id=<?php echo $estudiante['id']; ?>" class="btn btn-editar">✏️ Editar</a>
                                    <a href="eliminar.php?id=<?php echo $estudiante['id']; ?>" 
                                       class="btn btn-eliminar" 
                                       onclick="return confirm('¿Estás seguro de eliminar este estudiante?')">
                                        🗑️ Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="texto-centrado">No hay estudiantes registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="estadisticas">
            <h3>Estadísticas</h3>
            <p>Total de estudiantes: <strong><?php echo count($estudiantes); ?></strong></p>
            <?php if (count($estudiantes) > 0): ?>
                <?php 
                    $aprobados = count(array_filter($estudiantes, function($e) { return $e['nota_final'] >= 3.0; }));
                    $reprobados = count($estudiantes) - $aprobados;
                ?>
                <p>Aprobados: <strong class="aprobado"><?php echo $aprobados; ?></strong></p>
                <p>Reprobados: <strong class="reprobado"><?php echo $reprobados; ?></strong></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>