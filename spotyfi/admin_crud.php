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

// 2. PROCESAR EL FORMULARIO DE AGREGAR ÁLBUM (BACKEND)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_album') {
    $nombre_album = $conexion->real_escape_string($_POST['nombre_album']);
    $artista = $conexion->real_escape_string($_POST['artista']);
    $anio = intval($_POST['anio_lanzamiento']);
    
    // Rutas por defecto si el usuario no escribe nada
    $ruta_portada = !empty($_POST['ruta_portada']) ? $conexion->real_escape_string($_POST['ruta_portada']) : 'img/image_3b01bc.jpg';

    $sql_insertar = "INSERT INTO albumes (nombre_album, artista, anio_lanzamiento, ruta_portada) 
                     VALUES ('$nombre_album', '$artista', $anio, '$ruta_portada')";

    if ($conexion->query($sql_insertar)) {
        $mensaje = "<div class='alert success'>¡Álbum '$nombre_album' agregado con éxito!</div>";
    } else {
        $mensaje = "<div class='alert error'>Error al guardar: " . $conexion->error . "</div>";
    }
}

// Capturar búsqueda rápido
$buscar = isset($_GET['buscar']) ? $conexion->real_escape_string($_GET['buscar']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CRUD - Administrador Álbumes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght=700&family=Plus+Jakarta+Sans:wght=400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #030303; color: #ffffff; padding: 40px; }
        .crud-container { max-width: 1100px; margin: 0 auto; }
        .crud-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #222; padding-bottom: 20px; }
        .crud-header h1 { font-family: 'Orbitron', sans-serif; font-size: 28px; color: #1DB954; text-shadow: 0 0 10px rgba(29, 215, 96, 0.3); }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; }
        .search-form { display: flex; flex: 1; }
        .search-input { width: 100%; max-width: 400px; background-color: #181818; border: 1px solid #282828; padding: 12px 20px; border-radius: 25px; color: #fff; font-size: 14px; outline: none; }
        .search-input:focus { border-color: #1DB954; }
        
        .btn-add { background-color: #1DB954; color: #000; padding: 12px 24px; border-radius: 25px; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-add:hover { transform: scale(1.03); box-shadow: 0 0 15px rgba(29, 215, 96, 0.4); }
        
        /* TABLA */
        .crud-table-wrapper { background-color: #121212; border-radius: 8px; overflow: hidden; border: 1px solid #222; }
        .crud-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .crud-table th { background-color: #1e1e1e; color: #b3b3b3; padding: 16px; font-weight: 700; text-transform: uppercase; font-size: 12px; }
        .crud-table td { padding: 16px; border-bottom: 1px solid #222; color: #e5e5e5; vertical-align: middle; }
        .crud-table tr:hover td { background-color: #1a1a1a; }
        .mini-cover { width: 45px; height: 45px; border-radius: 4px; object-fit: cover; }
        
        /* BOTONES ACCION */
        .actions-cell { display: flex; gap: 10px; }
        .btn-action { padding: 8px 12px; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; border: none; cursor: pointer; }
        .btn-view { background-color: rgba(0, 123, 255, 0.15); color: #007bff; }
        .btn-view:hover { background-color: #007bff; color: #fff; }
        .btn-edit { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .btn-delete { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
        .btn-back { color: #b3b3b3; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        
        /* ALERTAS */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .success { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .error { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; }

        /* MODAL FLOTANTE (Oculto por defecto) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #121212; border: 1px solid #282828; padding: 30px; border-radius: 10px; width: 100%; max-width: 500px; position: relative; }
        .modal-content h2 { font-family: 'Orbitron', sans-serif; color: #1DB954; margin-bottom: 20px; font-size: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #b3b3b3; margin-bottom: 5px; font-size: 13px; }
        .form-control { width: 100%; background: #181818; border: 1px solid #282828; padding: 10px; border-radius: 6px; color: #fff; outline: none; }
        .form-control:focus { border-color: #1DB954; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel { background: #282828; color: #fff; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-save-form { background: #1DB954; color: #000; padding: 10px 20px; border-radius: 6px; font-weight: 700; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <div class="crud-container">
        <a href="catalogo.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Catálogo</a>

        <header class="crud-header">
            <h1>Panel de Control Administrativo</h1>
            <span style="color: #b3b3b3; font-size: 14px;"><i class="fa-solid fa-user-shield"></i> Modo Admin</span>
        </header>

        <?php echo $mensaje; ?>

        <div class="action-bar">
            <form method="GET" action="admin_crud.php" class="search-form">
                <input type="text" name="buscar" class="search-input" placeholder="Buscar por álbum o artista..." value="<?php echo htmlspecialchars($buscar); ?>">
            </form>
            <button class="btn-add" onclick="abrirModal()">
                <i class="fa-solid fa-plus"></i> Agregar Nuevo Álbum
            </button>
        </div>

        <div class="crud-table-wrapper">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>ID</th>
                        <th>Nombre del Álbum</th>
                        <th>Artista</th>
                        <th>Año</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM albumes";
                    if (!empty($buscar)) { $sql .= " WHERE nombre_album LIKE '%$buscar%' OR artista LIKE '%$buscar%'"; }
                    $sql .= " ORDER BY id_album DESC";
                    $resultado = $conexion->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            $portada = !empty($row['ruta_portada']) ? $row['ruta_portada'] : 'img/image_3b01bc.jpg';
                            ?>
                            <tr>
                                <td><img src="<?php echo $portada; ?>" alt="Cover" class="mini-cover"></td>
                                <td style="color: #646b75; font-weight: bold;"><?php echo $row['id_album']; ?></td>
                                <td style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($row['nombre_album']); ?></td>
                                <td><?php echo htmlspecialchars($row['artista']); ?></td>
                                <td><?php echo $row['anio_lanzamiento']; ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="admin_canciones.php?id_album=<?php echo $row['id_album']; ?>" class="btn-action btn-view">
                                            <i class="fa-solid fa-list-ul"></i> Canciones
                                        </a>
                                        <button class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-action btn-delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #b3b3b3; padding: 30px;'>No se encontraron álbumes registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="modalAlbum">
        <div class="modal-content">
            <h2>Agregar Nuevo Álbum</h2>
            <form method="POST" action="admin_crud.php">
                <input type="hidden" name="accion" value="crear_album">
                
                <div class="form-group">
                    <label>Nombre del Álbum *</label>
                    <input type="text" name="nombre_album" class="form-control" required placeholder="Ej. SOS">
                </div>
                <div class="form-group">
                    <label>Artista / Banda *</label>
                    <input type="text" name="artista" class="form-control" required placeholder="Ej. SZA">
                </div>
                <div class="form-group">
                    <label>Año de Lanzamiento *</label>
                    <input type="number" name="anio_lanzamiento" class="form-control" required placeholder="Ej. 2022">
                </div>
                <div class="form-group">
                    <label>Ruta de la Portada (Opcional)</label>
                    <input type="text" name="ruta_portada" class="form-control" placeholder="Ej. img/portadas/sza_sos.jpg">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-save-form">Guardar Álbum</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() { document.getElementById('modalAlbum').style.display = 'flex'; }
        function cerrarModal() { document.getElementById('modalAlbum').style.display = 'none'; }
    </script>
</body>
</html>
<?php $conexion->close(); ?>