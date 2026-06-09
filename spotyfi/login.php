<?php
session_start();

// 1. CONEXIÓN A LA BASE DE DATOS
$servidor = "localhost";
$usuario_db = "root";
$contrasena_db = "";
$base_datos = "disquera_spotify";

$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $base_datos);

if ($conexion->connect_error) { 
    die("Error de conexión: " . $conexion->connect_error); 
}

$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? '';

    // A. LOGIN: OYENTE NORMAL
    if ($accion == 'login_user') {
        $correo = $conexion->real_escape_string($_POST['correo']);
        $contrasena = $_POST['contrasena'];

        if (!empty($correo) && !empty($contrasena)) {
            $consulta = $conexion->query("SELECT * FROM usuarios WHERE correo = '$correo' AND rol = 'user' LIMIT 1");
            if ($consulta && $consulta->num_rows > 0) {
                $usuario = $consulta->fetch_assoc();
                if ($contrasena === $usuario['contrasena']) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    $_SESSION['rol'] = 'user';
                    header("Location: catalogo.php");
                    exit();
                } else { $error = "Contraseña incorrecta."; }
            } else { $error = "El correo no está registrado como oyente."; }
        } else { $error = "Llena todos los campos."; }
    }
    
    // B. LOGIN: ADMINISTRATIVO
    if ($accion == 'login_admin') {
        $correo = $conexion->real_escape_string($_POST['correo_admin']);
        $num_empleado = $conexion->real_escape_string($_POST['num_empleado']);
        $contrasena = $_POST['contrasena_admin'];

        if (!empty($correo) && !empty($num_empleado) && !empty($contrasena)) {
            $consulta = $conexion->query("SELECT * FROM usuarios WHERE correo = '$correo' AND num_empleado = '$num_empleado' AND rol = 'admin' LIMIT 1");
            if ($consulta && $consulta->num_rows > 0) {
                $usuario = $consulta->fetch_assoc();
                if ($contrasena === $usuario['contrasena']) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    $_SESSION['rol'] = 'admin';
                    header("Location: catalogo.php");
                    exit();
                } else { $error = "Contraseña incorrecta."; }
            } else { $error = "Credenciales inválidas o número de empleado incorrecto."; }
        } else { $error = "Por favor, llena todos los campos."; }
    }
    
    // C. REGISTRO INTELIGENTE
    if ($accion == 'registro') {
        $nombre = $conexion->real_escape_string($_POST['nombre_usuario']);
        $correo = $conexion->real_escape_string($_POST['correo']);
        $contrasena = $conexion->real_escape_string($_POST['contrasena']);
        
        $es_admin = isset($_POST['es_admin_check']) ? true : false;
        $num_empleado = $es_admin ? $conexion->real_escape_string($_POST['num_empleado_reg']) : null;
        $rol = $es_admin ? 'admin' : 'user';

        if (!empty($nombre) && !empty($correo) && !empty($contrasena)) {
            if ($es_admin && empty($num_empleado)) {
                $error = "Los administradores requieren un número de empleado.";
            } else {
                $verificar = $conexion->query("SELECT id_usuario FROM usuarios WHERE correo = '$correo'");
                if ($verificar->num_rows > 0) {
                    $error = "Este correo ya se encuentra registrado.";
                } else {
                    if ($es_admin) {
                        $insertar = $conexion->query("INSERT INTO usuarios (nombre, correo, contrasena, rol, num_empleado) VALUES ('$nombre', '$correo', '$contrasena', '$rol', '$num_empleado')");
                    } else {
                        $insertar = $conexion->query("INSERT INTO usuarios (nombre, correo, contrasena, rol, num_empleado) VALUES ('$nombre', '$correo', '$contrasena', '$rol', NULL)");
                    }
                    
                    if ($insertar) { 
                        $_SESSION['id_usuario'] = $conexion->insert_id;
                        $_SESSION['nombre_usuario'] = $nombre;
                        $_SESSION['rol'] = $rol;
                        header("Location: catalogo.php");
                        exit();
                    } else { $error = "Error al registrar: " . $conexion->error; }
                }
            }
        } else { $error = "Por favor, llena todos los campos."; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght=700;900&family=Plus+Jakarta+Sans:wght=400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #0d0e12; color: #ffffff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
        /* CONTENEDOR EXACTO A TU FIGMA */
        .login-container { 
            background-color: #13151a; 
            width: 100%; 
            max-width: 440px; 
            padding: 45px 35px; 
            border-radius: 16px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.6); 
            border: 1px solid #22252d; 
            text-align: center; 
        }
        
        /* LOGO BOLA DISCO CON EL NEÓN VERDE REVERSIONADO */
        .logo-container { position: relative; display: inline-block; margin-bottom: 5px; }
        .bola-disco-logo { 
            width: 90px; 
            height: 90px; 
            border-radius: 50%; 
            object-fit: cover; 
            box-shadow: 0 0 30px rgba(30, 215, 96, 0.4); 
        }
        
        /* TEXTO SPOTIFY ESTILO GLITCH / FUTURISTA */
        .brand-title { 
            font-family: 'Orbitron', sans-serif; 
            font-weight: 900; 
            font-size: 26px; 
            letter-spacing: 4px; 
            color: #ffffff; 
            margin-bottom: 25px;
            text-shadow: -2px -2px 0 #ff0055, 2px 2px 0 #00ffaa;
        }
        
        h1 { 
            font-size: 20px; 
            font-weight: 700; 
            margin-bottom: 25px; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.5px;
        }
        
        /* CÁPSULA SELECTORA DE ROLES */
        .selector-capsula { 
            display: flex; 
            background-color: #1c1f26; 
            padding: 5px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
        }
        .tab-btn { 
            flex: 1; 
            background: none; 
            border: none; 
            color: #696f7c; 
            font-size: 13px; 
            font-weight: 700; 
            padding: 12px; 
            cursor: pointer; 
            border-radius: 6px; 
            transition: all 0.2s ease;
        }
        .tab-btn.active { 
            background-color: #1ed760; 
            color: #000000; 
            box-shadow: 0 4px 15px rgba(30, 215, 96, 0.3);
        }

        /* DISEÑO DE ENTRADAS FORMULARIO */
        .form-group { text-align: left; margin-bottom: 18px; }
        .form-group label { 
            display: block; 
            font-size: 11px; 
            font-weight: 700; 
            margin-bottom: 8px; 
            color: #696f7c; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i { 
            position: absolute; 
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #696f7c; 
            font-size: 14px; 
        }
        .form-group input { 
            width: 100%; 
            padding: 14px 14px 14px 42px; 
            background-color: #1c1f26; 
            border: 1px solid #22252d; 
            border-radius: 8px; 
            color: #ffffff; 
            font-size: 14px; 
            outline: none; 
            transition: border 0.2s;
        }
        .form-group input:focus { border-color: #1ed760; }
        
        .checkbox-group { display: flex; align-items: center; gap: 8px; margin: 15px 0; text-align: left; }
        .checkbox-group input { width: 16px; height: 16px; accent-color: #1ed760; cursor: pointer; }
        .checkbox-group label { font-size: 13px; font-weight: 600; color: #1ed760; cursor: pointer; }

        /* BOTÓN PRINCIPAL CON TU HOVER Y ESTILO */
        .btn-login { 
            width: 100%; 
            padding: 16px; 
            background-color: #1ed760; 
            color: #000000; 
            border: none; 
            border-radius: 8px; 
            font-size: 13px; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            cursor: pointer; 
            margin-top: 10px; 
            box-shadow: 0 4px 20px rgba(30, 215, 96, 0.2);
            transition: all 0.2s ease;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 25px rgba(30, 215, 96, 0.4); }
        
        .alert-error { background-color: rgba(233, 20, 41, 0.1); color: #e91429; font-size: 13px; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e91429; text-align: left; }
        
        .footer-text { color: #696f7c; font-size: 13px; margin-top: 25px; }
        .footer-text a { color: #1ed760; text-decoration: none; font-weight: 700; cursor: pointer; }
        .footer-text a:hover { text-decoration: underline; }
        
        .oculto { display: none; }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- ÁREA DEL LOGO IDÉNTICA A TU MAQUETA -->
        <div class="logo-container">
            <img src="WhatsApp Image 2026-06-05 at 5.08.49 PM.jpeg" alt="Bola Disco" class="bola-disco-logo">
        </div>
        <div class="brand-title">SPOTIFY</div>
        
        <h1 id="titulo-pantalla">Iniciar Sesión</h1>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Cápsula selectora de pestañas -->
        <div class="selector-capsula" id="contenedor-tabs">
            <button class="tab-btn active" id="btn-tab-user" onclick="seleccionarModo('user')">Oyente</button>
            <button class="tab-btn" id="btn-tab-admin" onclick="seleccionarModo('admin')">Administrativo</button>
        </div>

        <!-- FORMULARIO: LOGIN OYENTE -->
        <form id="form-login-user" action="login.php" method="POST">
            <input type="hidden" name="accion" value="login_user">
            <div class="form-group">
                <label><i class="fa-regular fa-envelope"></i> Correo Electrónico</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="correo" placeholder="nombre@correo.com" required>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Contraseña</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="contrasena" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Entrar a la plataforma</button>
        </form>

        <!-- FORMULARIO: LOGIN ADMINISTRATIVO -->
        <form id="form-login-admin" action="login.php" method="POST" class="oculto">
            <input type="hidden" name="accion" value="login_admin">
            <div class="form-group">
                <label>Correo Administrativo</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-envelope-shield"></i>
                    <input type="email" name="correo_admin" placeholder="admin@disquera.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Número de Empleado</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="num_empleado" placeholder="Ej: EMP-2026" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña Privada</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" name="contrasena_admin" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-login" style="background-color: #ffffff; color: #000000; box-shadow: 0 4px 15px rgba(255,255,255,0.1);">Verificar Personal</button>
        </form>

        <!-- FORMULARIO: REGISTRO ADAPTABLE -->
        <form id="form-registro" action="login.php" method="POST" class="oculto">
            <input type="hidden" name="accion" value="registro">
            <div class="form-group">
                <label>Nombre Completo</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="nombre_usuario" placeholder="Tu nombre" required>
                </div>
            </div>
            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="contrasena" placeholder="Crea una contraseña" required>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="es_admin_check" name="es_admin_check" onchange="conmutarCamposAdmin(this)">
                <label for="es_admin_check">Registrar como cuenta administrativa</label>
            </div>

            <div class="form-group oculto" id="grupo-num-empleado-reg">
                <label style="color: #1ed760;">Número de Empleado Requerido</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-id-badge" style="color: #1ed760;"></i>
                    <input type="text" id="input-emp-reg" name="num_empleado_reg" placeholder="Ej: EMP-9988">
                </div>
            </div>

            <button type="submit" class="btn-login">Finalizar Registro</button>
        </form>

        <p class="footer-text" id="texto-cambio">
            ¿No tienes cuenta? <a onclick="mostrarRegistro()">Regístrate aquí</a>
        </p>
    </div>

    <script>
        const fUser = document.getElementById('form-login-user');
        const fAdmin = document.getElementById('form-login-admin');
        const fReg = document.getElementById('form-registro');
        const tabs = document.getElementById('contenedor-tabs');
        const titulo = document.getElementById('titulo-pantalla');
        const txtCambio = document.getElementById('texto-cambio');

        function seleccionarModo(modo) {
            if(modo === 'user') {
                fUser.classList.remove('oculto');
                fAdmin.classList.add('oculto');
                document.getElementById('btn-tab-user').classList.add('active');
                document.getElementById('btn-tab-admin').classList.remove('active');
            } else {
                fAdmin.classList.remove('oculto');
                fUser.classList.add('oculto');
                document.getElementById('btn-tab-admin').classList.add('active');
                document.getElementById('btn-tab-user').classList.remove('active');
            }
        }

        function mostrarRegistro() {
            fUser.classList.add('oculto');
            fAdmin.classList.add('oculto');
            tabs.classList.add('oculto');
            fReg.classList.remove('oculto');
            titulo.innerText = "Crear nueva cuenta";
            txtCambio.innerHTML = '¿Ya tienes cuenta? <a onclick="mostrarLogin()">Inicia sesión aquí</a>';
        }

        function mostrarLogin() {
            fReg.classList.add('oculto');
            tabs.classList.remove('oculto');
            titulo.innerText = "Iniciar Sesión";
            seleccionarModo('user');
            txtCambio.innerHTML = '¿No tienes cuenta? <a onclick="mostrarRegistro()">Regístrate aquí</a>';
        }

        function conmutarCamposAdmin(checkbox) {
            const grupoEmp = document.getElementById('grupo-num-empleado-reg');
            const inputEmp = document.getElementById('input-emp-reg');
            if (checkbox.checked) {
                grupoEmp.classList.remove('oculto');
                inputEmp.required = true;
            } else {
                grupoEmp.classList.add('oculto');
                inputEmp.required = false;
                inputEmp.value = "";
            }
        }
    </script>
</body>
</html>