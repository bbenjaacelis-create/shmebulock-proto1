<?php
// Este archivo va en: SHMEBULOCK/views/perfil.php
// Necesita: SHMEBULOCK/includes/config.php (la conexion a la base)
//
// Tarea de Benjamin: "Mi perfil (back end)"
// Muestra los datos del usuario logueado, permite cerrar sesion
// y eliminar la cuenta propia.

session_start();

require_once __DIR__ . '/../includes/config.php';

// CONTROL DE ACCESO: hay que estar logueado para ver esta pagina

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$errores = [];

// CERRAR SESION

if (isset($_GET['logout'])) {
    session_unset();   // borra todas las variables de la sesion
    session_destroy(); // destruye la sesion en el servidor
    header("Location: login.php");
    exit;
}

// ELIMINAR CUENTA

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cuenta'])) {

    $stmt = $conexion->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        $stmt->close();
        session_unset();
        session_destroy();
        header("Location: login.php?cuenta_eliminada=1");
        exit;
    } else {
        $errores[] = "No se pudo eliminar la cuenta. Probá de nuevo.";
        $stmt->close();
    }
}

$stmt = $conexion->prepare(
    "SELECT nombre_usuario, correo, edad, estilo, perfil, rol, fecha_registro FROM usuario WHERE id_usuario = ?"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../styles/SHMEBULOCK_styles_shembulock.css">
    <style>
        .perfil-contenedor { max-width: 500px; margin: 30px auto; padding: 0 15px; }
        .dato { margin-bottom: 12px; }
        .dato strong { display: inline-block; width: 140px; }
        .acciones-perfil { margin-top: 25px; display: flex; gap: 10px; flex-wrap: wrap; }
        .boton-cerrar-sesion { background: #444; color: #fff; padding: 8px 14px; text-decoration: none; border-radius: 4px; }
        .boton-eliminar-cuenta { background: #cc0000; color: #fff; padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; }
        .caja-peligro { border: 1px solid #cc0000; padding: 15px; margin-top: 30px; border-radius: 4px; }
        .caja-peligro h2 { color: #cc0000; margin-top: 0; font-size: 16px; }
    </style>
</head>

<body>

    <main class="perfil-contenedor">

        <h1>Mi perfil</h1>

        <?php if (!empty($errores)): ?>
            <div style="color: red; margin-bottom: 15px;">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- DATOS DEL USUARIO -->
        <div class="dato"><strong>Usuario:</strong> <?= htmlspecialchars($usuario['nombre_usuario']) ?></div>
        <div class="dato"><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></div>
        <div class="dato"><strong>Edad:</strong> <?= htmlspecialchars($usuario['edad'] ?? '-') ?></div>
        <div class="dato"><strong>Estilo:</strong> <?= htmlspecialchars($usuario['estilo'] ?? '-') ?></div>
        <div class="dato"><strong>Perfil:</strong> <?= htmlspecialchars($usuario['perfil'] ?? '-') ?></div>
        <div class="dato"><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></div>
        <div class="dato"><strong>Miembro desde:</strong> <?= htmlspecialchars($usuario['fecha_registro']) ?></div>

        <!-- ACCIONES PRINCIPALES -->
        <div class="acciones-perfil">
            <a class="boton-cerrar-sesion" href="perfil.php?logout=1">Cerrar sesión</a>

            <?php if ($usuario['rol'] === 'admin'): ?>
                <a class="boton-cerrar-sesion" href="crud_usuarios.php">Ir al panel de administración</a>
            <?php endif; ?>
        </div>

        <!-- ZONA DE PELIGRO: ELIMINAR CUENTA -->
        <div class="caja-peligro">
            <h2>Eliminar mi cuenta</h2>
            <p>Esta acción no se puede deshacer. Se va a borrar tu cuenta y no vas a poder recuperarla.</p>

            <form method="POST" action="perfil.php"
                  onsubmit="return confirm('¿Estás seguro de que querés eliminar tu cuenta? Esta acción no se puede deshacer.');">
                <input type="hidden" name="eliminar_cuenta" value="1">
                <button type="submit" class="boton-eliminar-cuenta">Eliminar mi cuenta</button>
            </form>
        </div>

    </main>

</body>

</html>