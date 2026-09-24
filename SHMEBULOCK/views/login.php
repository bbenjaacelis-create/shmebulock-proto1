<?php
// Este archivo va en: SHMEBULOCK/views/login.php
// Tarea de Benjamin: "Iniciar sesión (back end)"
// Valida correo + contraseña contra la tabla `usuario` y arranca

session_start(); 

require_once __DIR__ . '/../includes/config.php';

$errores = [];

// Si ya está logueado, no tiene sentido ver el formulario de nuevo
if (isset($_SESSION['id_usuario'])) {
    header("Location: perfil.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo     = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if ($contrasena === '') {
        $errores[] = "Ingresá tu contraseña.";
    }

    if (empty($errores)) {
        // Buscamos al usuario por correo
        $stmt = $conexion->prepare(
            "SELECT id_usuario, nombre_usuario, contrasena, rol FROM usuario WHERE correo = ?"
        );
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
            $errores[] = "Correo o contraseña incorrectos.";
        } else {
            // Login correcto: guardamos los datos clave en la sesion
            $_SESSION['id_usuario']     = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol']            = $usuario['rol'];

            header("Location: perfil.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../styles/SHMEBULOCK_styles_shembulock.css">
</head>

<body>

    <main class="contenedor">

        <section class="tarjeta">

            <div class="encabezado">
                <h1>Iniciar sesión</h1>
                <p>Ingresá con tu correo y contraseña</p>
            </div>

            <?php if (!empty($errores)): ?>
                <div style="color: red; margin-bottom: 15px;">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">

                <div class="campo">
                    <label for="correo">Correo electrónico</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="ejemplo@correo.com"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="contrasena">Contraseña</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>

                <button type="submit">Iniciar sesión</button>

            </form>

            <p>¿No tenés cuenta? <a href="registro.php">Registrate acá</a></p>

        </section>

    </main>

</body>

</html>