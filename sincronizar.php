<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar sincronización si se solicitó
if (isset($_POST['sincronizar'])) {
    $resultado = sincronizarLocalAAiven();
    
    if ($resultado['success']) {
        $mensaje = "Sincronización completada: {$resultado['sincronizados']} registros sincronizados";
        if ($resultado['errores'] > 0) {
            $mensaje .= ", {$resultado['errores']} errores";
        }
        $tipo_mensaje = $resultado['errores'] > 0 ? 'warning' : 'exito';
    } else {
        $mensaje = "Error en la sincronización: " . $resultado['message'];
        $tipo_mensaje = 'error';
    }
}

// Obtener estado de sincronización
$estado = obtenerEstadoSincronizacion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Sincronización</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .sync-card {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .sync-card.active {
            border-color: #28a745;
            background: #d4edda;
        }
        .sync-card.inactive {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .sync-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .sync-info {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔄 Panel de Sincronización de Bases de Datos</h1>
        </header>

        <div class="acciones">
            <a href="index.php" class="btn btn-secondary">⬅️ Volver al CRUD</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="estadisticas">
            <h3>📊 Modo de Operación Actual</h3>
            <p><strong>Configuración:</strong> 
                <span class="badge badge-info"><?php echo strtoupper($estado['modo']); ?></span>
            </p>
            <?php if ($estado['modo'] === 'local'): ?>
                <p>✅ Solo se usa la base de datos LOCAL</p>
            <?php elseif ($estado['modo'] === 'aiven'): ?>
                <p>✅ Solo se usa la base de datos AIVEN (Nube)</p>
            <?php else: ?>
                <p>✅ Se sincronizan automáticamente AMBAS bases de datos</p>
            <?php endif; ?>
        </div>

        <div class="sync-grid">
            <!-- Card Base de Datos Local -->
            <div class="sync-card <?php echo $estado['local']['activo'] ? 'active' : 'inactive'; ?>">
                <h3>💻 Base de Datos LOCAL</h3>
                <div class="sync-info">
                    <span>Estado:</span>
                    <span class="badge <?php echo $estado['local']['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $estado['local']['activo'] ? 'CONECTADA' : 'DESCONECTADA'; ?>
                    </span>
                </div>
                <div class="sync-info">
                    <span>Registros:</span>
                    <strong><?php echo $estado['local']['registros']; ?></strong>
                </div>
                <div class="sync-info">
                    <span>Host:</span>
                    <strong><?php echo DB_LOCAL_HOST; ?>:<?php echo DB_LOCAL_PORT; ?></strong>
                </div>
                <div class="sync-info">
                    <span>Base de datos:</span>
                    <strong><?php echo DB_LOCAL_NAME; ?></strong>
                </div>
            </div>

            <!-- Card Base de Datos Aiven -->
            <div class="sync-card <?php echo $estado['aiven']['activo'] ? 'active' : 'inactive'; ?>">
                <h3>☁️ Base de Datos AIVEN</h3>
                <div class="sync-info">
                    <span>Estado:</span>
                    <span class="badge <?php echo $estado['aiven']['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $estado['aiven']['activo'] ? 'CONECTADA' : 'DESCONECTADA'; ?>
                    </span>
                </div>
                <div class="sync-info">
                    <span>Registros:</span>
                    <strong><?php echo $estado['aiven']['registros']; ?></strong>
                </div>
                <div class="sync-info">
                    <span>Host:</span>
                    <strong><?php echo DB_AIVEN_HOST; ?>:<?php echo DB_AIVEN_PORT; ?></strong>
                </div>
                <div class="sync-info">
                    <span>Base de datos:</span>
                    <strong><?php echo DB_AIVEN_NAME; ?></strong>
                </div>
            </div>
        </div>

        <?php if ($estado['local']['activo'] && $estado['aiven']['activo']): ?>
            <!-- Mostrar diferencias -->
            <?php 
            $diferencia = abs($estado['local']['registros'] - $estado['aiven']['registros']);
            if ($diferencia > 0):
            ?>
                <div class="mensaje warning">
                    <strong>⚠️ Advertencia:</strong> Hay una diferencia de <?php echo $diferencia; ?> registros entre las bases de datos.
                    <?php if ($estado['local']['registros'] > $estado['aiven']['registros']): ?>
                        <br>LOCAL tiene más registros que AIVEN.
                    <?php else: ?>
                        <br>AIVEN tiene más registros que LOCAL.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="mensaje exito">
                    ✅ Las bases de datos están sincronizadas (mismo número de registros).
                </div>
            <?php endif; ?>

            <!-- Formulario de sincronización -->
            <div class="estadisticas">
                <h3>🔄 Sincronización Manual</h3>
                <p>Puedes copiar todos los registros de LOCAL a AIVEN. Esto sobrescribirá los datos en AIVEN.</p>
                <form method="POST" onsubmit="return confirm('¿Estás seguro de sincronizar LOCAL → AIVEN? Esto puede sobrescribir datos existentes.');">
                    <button type="submit" name="sincronizar" class="btn btn-primary">
                        🔄 Sincronizar LOCAL → AIVEN
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="estadisticas">
            <h3>ℹ️ Información</h3>
            <p><strong>Modo LOCAL:</strong> Todos los datos se guardan solo en tu servidor Kali Linux.</p>
            <p><strong>Modo AIVEN:</strong> Todos los datos se guardan solo en la nube de Aiven.</p>
            <p><strong>Modo BOTH:</strong> Cada operación (crear, editar, eliminar) se ejecuta automáticamente en ambas bases de datos simultáneamente.</p>
            
            <p style="margin-top: 20px;"><strong>Para cambiar el modo:</strong> Edita el archivo <code>config.php</code> y cambia la línea:</p>
            <pre style="background: #272822; color: #f8f8f2; padding: 10px; border-radius: 5px;">define('DB_TYPE', 'both'); // Opciones: 'local', 'aiven', 'both'</pre>
        </div>
    </div>
</body>
</html>