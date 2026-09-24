<?php


// Este archivo va en: SHMEBULOCK/view/registro.php
// Necesita: SHMEBULOCK/includes/config.php (la conexion a la base)

require_once __DIR__ . '/../includes/config.php';

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibimos y limpiamos los datos del formulario
    $usuario_nombre = trim($_POST['usuario'] ?? '');
    $correo         = trim($_POST['correo'] ?? '');
    $contrasena     = $_POST['contrasena'] ?? '';
    $fecha_nac      = $_POST['fecha'] ?? '';


    if ($usuario_nombre === '') {
        $errores[] = "El nombre de usuario es obligatorio.";
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if (strlen($contrasena) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if ($fecha_nac === '') {
        $errores[] = "La fecha de nacimiento es obligatoria.";
    } else {
        // Calculamos la edad a partir de la fecha de nacimiento
        $nacimiento = new DateTime($fecha_nac);
        $hoy = new DateTime();
        $edad = $hoy->diff($nacimiento)->y;

        if ($edad < 13) {
            $errores[] = "Debés tener al menos 13 años para registrarte.";
        }
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = "Ese correo ya está registrado. Probá iniciar sesión.";
        }
        $stmt->close();
    }

    if (empty($errores)) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT); 

        $stmt = $conexion->prepare(
            "INSERT INTO usuario (nombre_usuario, correo, contrasena, edad, rol) VALUES (?, ?, ?, ?, 'cliente')"
        );
        $stmt->bind_param("sssi", $usuario_nombre, $correo, $hash, $edad);

        if ($stmt->execute()) {
            $exito = true;
        } else {
            $errores[] = "Ocurrió un error al guardar el usuario. Probá de nuevo.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear una cuenta</title>

    <link rel="stylesheet" href="../styles/SHMEBULOCK_styles_shembulock.css">
</head>

<body>

    <main class="contenedor">

        <section class="tarjeta">

            <div class="encabezado">
                <h1>Crear una cuenta</h1>
                <p>Regístrate para comenzar</p>
            </div>

            <?php if ($exito): ?>
                <!-- Mensaje de exito si el registro funciono -->
                <p style="color: green; font-weight: bold;">
                    ¡Cuenta creada con éxito! Ya podés iniciar sesión.
                </p>
                <p><a href="login.php">Ir a iniciar sesión</a></p>

            <?php else: ?>

                <?php if (!empty($errores)): ?>
                    <!-- Lista de errores, si los hay -->
                    <div style="color: red; margin-bottom: 15px;">
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="registro.php" method="POST">

                    <div class="campo">
                        <label for="usuario">Nombre de usuario</label>

                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            placeholder="Ingresa tu nombre de usuario"
                            value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                            required
                        >
                    </div>

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
                            minlength="6"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="fecha">Fecha de nacimiento</label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>"
                            required
                        >
                    </div>

                    <button type="submit">
                        Registrarse
                    </button>

                </form>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>