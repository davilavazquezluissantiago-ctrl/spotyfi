<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$servidor = "localhost";
$usuario_db = "root";
$contrasena_db = "";
$base_datos = "disquera_spotify";

$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Validar ID de álbum contenedor
$id_album = isset($_GET['id_album']) ? intval($_GET['id_album']) : 0;
if ($id_album === 0) { die("Error: No se especificó un álbum válido."); }

// Traer info del álbum para los títulos
$sql_album = "SELECT * FROM albumes WHERE id_album = $id_album";
$res_album = $conexion->query($sql_album);
$info_album = $res_album->fetch_assoc();
if (!$info_album) { die("Error: El álbum solicitado no existe."); }

// 2. PROCESAR FORMULARIO DE AGREGAR CANCIÓN (BACKEND)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_cancion') {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $genero = $conexion->real_escape_string($_POST['genero']);
    $duracion = $conexion->real_escape_string($_POST['duracion']);
    
    // Rutas relativas por defecto automatizadas
    $ruta_archivo = !empty($_POST['ruta_archivo']) ? $conexion->real_escape_string($_POST['ruta_archivo']) : 'audio/default.mp3';
    $ruta_portada = $info_album['ruta_portada']; // Hereda la misma portada de la carpeta

    $sql_insertar = "INSERT INTO multimedia (titulo, genero, anio_lanzamiento, tipo, duracion, ruta_archivo, ruta_portada, id_album) 
                     VALUES ('$titulo', '$genero', {$info_album['anio_lanzamiento']}, 'cancion', '$duracion', '$ruta_archivo', '$ruta_portada', $id_album)";

    if ($conexion->query($sql_insertar)) {
        $mensaje = "<div class='alert success'>¡Canción '$titulo' agregada a la carpeta con éxito!</div>";
    } else {
        $mensaje = "<div class='alert error'>Error al guardar canción: " . $conexion->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canciones de <?php echo $info_album['nombre_album']; ?> - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2 family=Orbitron:wght=700&family=Plus+Jakarta+Sans:wght=400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #030303; color: #ffffff; padding: 40px; }
        .crud-container { max-width: 1100px; margin: 0 auto; }
        .crud-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #222; padding-bottom: 20px; }
        .crud-header h1 { font-family: 'Orbitron', sans-serif; font-size: 24px; color: #1DB954; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .album-info-badge { background-color: #181818; padding: 10px 20px; border-radius: 20px; font-size: 14px; border: 1px solid #282828; color: #b3b3b3; }
        .album-info-badge strong { color: #fff; }
        
        .btn-add { background-color: #1DB954; color: #000; padding: 12px 24px; border-radius: 25px; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-add:hover { transform: scale(1.03); box-shadow: 0 0 15px rgba(29, 215, 96, 0.4); }
        
        .crud-table-wrapper { background-color: #121212; border-radius: 8px; overflow: hidden; border: 1px solid #222; }
        .crud-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .crud-table th { background-color: #1e1e1e; color: #b3b3b3; padding: 16px; font-weight: 700; text-transform: uppercase; font-size: 12px; }
        .crud-table td { padding: 16px; border-bottom: 1px solid #222; color: #e5e5e5; }
        .crud-table tr:hover td { background-color: #1a1a1a; }
        .btn-action { padding: 8px 12px; border-radius: 4px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; border: none; cursor: pointer; }
        .btn-edit { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .btn-delete { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
        .btn-back { color: #b3b3b3; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .success { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .error { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; }

        /* MODAL CANCIONES */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #121212; border: 1px solid #282828; padding: 30px; border-radius: 10px; width: 100%; max-width: 500px; }
        .modal-content h2 { font-family: 'Orbitron', sans-serif; color: #1DB954; margin-bottom: 20px; font-size: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #b3b3b3; margin-bottom: 5px; font-size: 13px; }
        .form-control { width: 100%; background: #181818; border: 1px solid #282828; padding: 10px; border-radius: 6px; color: #fff; outline: none; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel { background: #282828; color: #fff; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-save-form { background: #1DB954; color: #000; padding: 10px 20px; border-radius: 6px; font-weight: 700; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <div class="crud-container">
        <a href="admin_crud.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver a Álbumes</a>

        <header class="crud-header">
            <h1>Canciones del Álbum: <?php echo htmlspecialchars($info_album['nombre_album']); ?></h1>
            <span style="color: #b3b3b3;"><i class="fa-solid fa-compact-disc"></i> Modificando Contenido</span>
        </header>

        <?php echo $mensaje; ?>

        <div class="action-bar">
            <div class="album-info-badge">
                Artista: <strong><?php echo htmlspecialchars($info_album['artista']); ?></strong>
            </div>
            <button class="btn-add" onclick="abrirModal()">
                <i class="fa-solid fa-plus"></i> Agregar Nueva Canción
            </button>
        </div>

        <div class="crud-table-wrapper">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título de la Canción</th>
                        <th>Género</th>
                        <th>Duración</th>
                        <th>Ruta Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_canciones = "SELECT * FROM multimedia WHERE id_album = $id_album ORDER BY id_cancion ASC";
                    $resultado = $conexion->query($sql_canciones);

                    if ($resultado && $resultado->num_rows > 0) {
                        while ($cancion = $resultado->fetch_assoc()) {
                            ?>
                            <tr>
                                <td style="color: #646b75; font-weight: bold;"><?php echo $cancion['id_cancion']; ?></td>
                                <td style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($cancion['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($cancion['genero']); ?></td>
                                <td style="font-family: 'Orbitron', sans-serif; color: #1DB954;"><?php echo $cancion['duracion']; ?></td>
                                <td style="color: #888; font-size: 12px;"><?php echo htmlspecialchars($cancion['ruta_archivo']); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action btn-delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #b3b3b3; padding: 30px;'>Este álbum no tiene canciones registradas todavía.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="modalCancion">
        <div class="modal-content">
            <h2>Agregar Canción a <?php echo htmlspecialchars($info_album['nombre_album']); ?></h2>
            <form method="POST" action="admin_canciones.php?id_album=<?php echo $id_album; ?>">
                <input type="hidden" name="accion" value="crear_cancion">
                
                <div class="form-group">
                    <label>Título de la Canción *</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ej. Kill Bill">
                </div>
                <div class="form-group">
                    <label>Género Musical *</label>
                    <input type="text" name="genero" class="form-control" required placeholder="Ej. R&B / Soul">
                </div>
                <div class="form-group">
                    <label>Duración (Min:Seg) *</label>
                    <input type="text" name="duracion" class="form-control" required placeholder="Ej. 2:33">
                </div>
                <div class="form-group">
                    <label>Ruta del Archivo de Audio (Opcional)</label>
                    <input type="text" name="ruta_archivo" class="form-control" placeholder="Ej. audio/sza/kill_bill.mp3">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-save-form">Guardar Canción</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() { document.getElementById('modalCancion').style.display = 'flex'; }
        function cerrarModal() { document.getElementById('modalCancion').style.display = 'none'; }
    </script>
</body>
</html>
<?php $conexion->close(); ?>