<?php
session_start();

// 1. CONTROL DE ACCESO SEGURÓ: Si el usuario no se ha logueado, se regresa al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// 2. CONEXIÓN A LA BASE DE DATOS
$servidor = "localhost";
$usuario_db = "root";
$contrasena_db = "";
$base_datos = "disquera_spotify";
$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $base_datos);

if ($conexion->connect_error) { 
    die("Error de conexión: " . $conexion->connect_error); 
}

// 3. VARIABLES REALES DE LA SESIÓN DE USUARIO
$rol_usuario_actual = $_SESSION['rol']; 
$nombre_usuario_actual = $_SESSION['nombre_usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Disco Spotify</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght=700&family=Plus+Jakarta+Sans:wght=400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="catalogo.css">
    <style>
        /* Pequeñas clases de apoyo para el dinamismo de la Single Page Application sin romper nada */
        .btn-ajax-nav { cursor: pointer; }
        .track-item-row { display: flex; justify-content: space-between; padding: 12px 10px; border-bottom: 1px solid #1a1a1a; cursor: pointer; border-radius: 4px; transition: background 0.2s; }
        .track-item-row:hover { background: #282828; }
        .right-section-visible { display: block !important; }
    </style>
</head>
<body class="spotify-layout">

    <audio id="audio-principal-player" src=""></audio>

    <aside class="sidebar">
        <div class="logo-area">
            <img src="WhatsApp Image 2026-06-05 at 5.08.49 PM.jpeg" alt="Logo" class="mini-logo">
            <span>SPOTIFY</span>
        </div>
        <nav class="menu">
            <a href="#" class="active btn-ajax-nav" onclick="cargarSeccion('inicio')"><i class="fa-solid fa-house"></i> Inicio</a>
            <a href="#" class="btn-ajax-nav" onclick="cargarSeccion('buscar')"><i class="fa-solid fa-magnifying-glass"></i> Buscar</a>
        </nav>
        
        <?php if ($rol_usuario_actual === 'admin'): ?>
            <div class="admin-zone">
                <p class="zone-title">Gestión</p>
                <a href="admin_crud.php" class="btn-crud-nav"><i class="fa-solid fa-sliders-h"></i> Panel CRUD Admin</a>
            </div>
        <?php endif; ?>
    </aside>

    <main class="main-content" id="contenedor-central-dinamico">
        
        <header class="top-header">
            <div class="search-bar-mock">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="¿Qué quieres escuchar?" disabled>
            </div>
            <div class="user-profile">
                <span class="user-badge"><?php echo htmlspecialchars($nombre_usuario_actual); ?></span>
                <div class="avatar"><?php echo strtoupper(substr($nombre_usuario_actual, 0, 1)); ?></div>
            </div>
        </header>

        <section class="catalog-section">
            <h2>Álbumes Recomendados</h2>
            <div class="cards-grid">
                <?php
                // Jalar los álbumes creados en la base de datos de manera dinámica
                $resultado = $conexion->query("SELECT * FROM albumes ORDER BY id_album DESC");
                if ($resultado && $resultado->num_rows > 0) {
                    while ($album = $resultado->fetch_assoc()) {
                        // Si no hay portada guardada, usa la de por defecto del logo
                        $portada = !empty($album['ruta_portada']) ? $album['ruta_portada'] : 'img/image_3b01bc.jpg';
                        ?>
                        <div class="music-card" onclick="cargarCancionesAlbum(<?php echo $album['id_album']; ?>, '<?php echo addslashes($album['nombre_album']); ?>', '<?php echo $portada; ?>')">
                            <div class="card-cover">
                                <img src="<?php echo $portada; ?>" alt="Cover">
                                <button class="play-hover-btn"><i class="fa-solid fa-play"></i></button>
                            </div>
                            <h3><?php echo htmlspecialchars($album['nombre_album']); ?></h3>
                            <p><?php echo htmlspecialchars($album['artista']); ?></p>
                            <span class="type-tag tag-music">Álbum</span>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #b3b3b3; padding: 20px;'>No hay álbumes en el catálogo actualmente.</p>";
                }
                ?>
            </div>
        </section>
    </main>

    <aside class="right-sidebar" id="panel-derecho-canciones">
        <div class="sidebar-header">
            <h3 id="album-titulo-derecho">Selecciona un álbum</h3>
        </div>
        <div class="current-track-large">
            <img id="album-portada-derecha" src="img/image_3b01bc.jpg" alt="Portada">
        </div>
        
        <div id="lista-canciones-ajax" style="padding: 10px; max-height: 350px; overflow-y: auto;">
            <p style="color: #888; text-align: center; font-size: 13px; margin-top: 20px;">Haz clic en un álbum del catálogo para desplegar sus canciones aquí al lado.</p>
        </div>
    </aside>

    <footer class="player-bar">
        <div class="now-playing">
            <img id="player-mini-cover" src="img/image_3b01bc.jpg" alt="Mini">
            <div class="track-info">
                <h5 id="player-track-title">Ninguna canción sonando</h5>
                <p id="player-track-artist">Disco Spotify</p>
            </div>
            <i class="fa-regular fa-heart heart-icon"></i>
        </div>

        <div class="player-controls">
            <div class="control-buttons">
                <i class="fa-solid fa-shuffle"></i>
                <i class="fa-solid fa-backward-step"></i>
                <i class="fa-solid fa-circle-play main-play" id="btn-play-reproductor" onclick="conmutarAudio()" style="cursor:pointer;"></i>
                <i class="fa-solid fa-forward-step"></i>
                <i class="fa-solid fa-repeat"></i>
            </div>
            <div class="progress-container">
                <span id="tiempo-actual">0:00</span>
                <div class="progress-bar"><div class="progress-current" id="barra-progreso" style="width: 0%;"></div></div>
                <span id="tiempo-total">0:00</span>
            </div>
        </div>

        <div class="player-utilities">
            <i class="fa-solid fa-microphone"></i>
            <i class="fa-solid fa-list-ul"></i>
            <i class="fa-solid fa-volume-high"></i>
            <div class="volume-bar"><div class="volume-current" style="width: 80%;"></div></div>
        </div>
    </footer>

    <script>
        const audioNode = document.getElementById('audio-principal-player');
        const btnPlay = document.getElementById('btn-play-reproductor');

        // Función AJAX para traer las canciones de la base de datos sin recargar la pantalla
        function cargarCancionesAlbum(idAlbum, nombreAlbum, rutaPortada) {
            document.getElementById('album-titulo-derecho').innerText = nombreAlbum;
            document.getElementById('album-portada-derecha').src = rutaPortada;
            
            const listaContenedor = document.getElementById('lista-canciones-ajax');
            listaContenedor.innerHTML = "<p style='color:#1DB954; font-size:13px;'>Cargando tracks...</p>";

            fetch(`get_canciones.php?id_album=${idAlbum}`)
                .then(response => response.json())
                .then(data => {
                    listaContenedor.innerHTML = "";
                    if(data.length === 0) {
                        listaContenedor.innerHTML = "<p style='color:#888; font-size:12px; text-align:center;'>Este álbum no tiene canciones.</p>";
                        return;
                    }
                    data.forEach(track => {
                        const row = document.createElement('div');
                        row.className = 'track-item-row';
                        row.onclick = () => reproducirTrack(track.titulo, track.artista, track.ruta_archivo, rutaPortada);
                        row.innerHTML = `
                            <div>
                                <span style="color:#fff; font-weight:600; display:block;">${track.titulo}</span>
                                <span style="color:#888; font-size:12px;">${track.genero}</span>
                            </div>
                            <span style="color:#1DB954; font-family:'Orbitron'; font-size:13px;">${track.duracion}</span>
                        `;
                        listaContenedor.appendChild(row);
                    });
                });
        }

        // Ejecutar reproducción real en el player fijo
        function reproducirTrack(titulo, artista, rutaAudio, rutaPortada) {
            audioNode.src = rutaAudio;
            audioNode.play();
            
            // Actualizar datos del footer inferior
            document.getElementById('player-track-title').innerText = titulo;
            document.getElementById('player-track-artist').innerText = artista;
            document.getElementById('player-mini-cover').src = rutaPortada;
            
            btnPlay.className = "fa-solid fa-circle-pause main-play";
        }

        // Play y pausa manual desde el reproductor inferior
        function conmutarAudio() {
            if(!audioNode.src) return;
            if(audioNode.paused) {
                audioNode.play();
                btnPlay.className = "fa-solid fa-circle-pause main-play";
            } else {
                audioNode.pause();
                btnPlay.className = "fa-solid fa-circle-play main-play";
            }
        }

        // Manejo automático de los tiempos de reproducción (Barrita de carga)
        audioNode.ontimeupdate = function() {
            if(!audioNode.duration) return;
            const pct = (audioNode.currentTime / audioNode.duration) * 100;
            document.getElementById('barra-progreso').style.width = pct + "%";
            
            let mins = Math.floor(audioNode.currentTime / 60);
            let secs = Math.floor(audioNode.currentTime % 60);
            if(secs < 10) secs = '0' + secs;
            document.getElementById('tiempo-actual').innerText = mins + ":" + secs;
        };

        audioNode.onloadedmetadata = function() {
            let mins = Math.floor(audioNode.duration / 60);
            let secs = Math.floor(audioNode.duration % 60);
            if(secs < 10) secs = '0' + secs;
            document.getElementById('tiempo-total').innerText = mins + ":" + secs;
        };

        // Navegación asíncrona por las secciones de la barra izquierda
        function cargarSeccion(seccion) {
            const central = document.getElementById('contenedor-central-dinamico');
            if(seccion === 'buscar') {
                central.innerHTML = `
                    <header class="top-header">
                        <div class="search-bar-mock"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="¿Qué quieres escuchar, <?php echo htmlspecialchars($nombre_usuario_actual); ?>?" style="color:#fff;" disabled></div>
                    </header>
                    <section class="catalog-section">
                        <h2>Explorar todo</h2>
                        <p style="color:#b3b3b3;">Sección de exploración adaptada al diseño global.</p>
                    </section>
                `;
            } else if(seccion === 'inicio') {
                window.location.reload(); 
            }
        }
    </script>
</body>
</html>
<?php $conexion->close(); ?>