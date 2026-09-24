<?php
// Este archivo va en: SHMEBULOCK/views/crud_usuarios.php
// Tarea de Cristian: "CRUD de usuarios (back end)"
// Permite: Listar, Editar y Eliminar usuarios de la tabla `usuario`.

require_once __DIR__ . '/../includes/config.php';

$errores = [];
$mensaje_exito = '';


// ELIMINAR usuario (llega por GET desde el link "Eliminar" de la tabla)

if (isset($_GET['eliminar'])) {
    $id_a_eliminar = (int) $_GET['eliminar'];

    $stmt = $conexion->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_a_eliminar);

    if ($stmt->execute()) {
        $mensaje_exito = "Usuario eliminado correctamente.";
    } else {
        $errores[] = "No se pudo eliminar el usuario.";
    }
    $stmt->close();
}

// ACTUALIZAR usuario (llega por POST desde el formulario de edicion)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {

    $id_usuario     = (int) $_POST['id_usuario'];
    $nombre_usuario = trim($_POST['usuario'] ?? '');
    $correo         = trim($_POST['correo'] ?? '');
    $edad           = $_POST['edad'] !== '' ? (int) $_POST['edad'] : null;
    $estilo         = trim($_POST['estilo'] ?? '');
    $perfil         = trim($_POST['perfil'] ?? '');
    $rol            = $_POST['rol'] ?? 'cliente';

    // --- Validaciones basicas en el servidor ---
    if ($nombre_usuario === '') {
        $errores[] = "El nombre de usuario es obligatorio.";
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if (!in_array($rol, ['cliente', 'admin'], true)) {
        $errores[] = "Rol inválido.";
    }

    // Chequeamos que el correo no le pertenezca a OTRO usuario distinto
    if (empty($errores)) {
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = ? AND id_usuario != ?");
        $stmt->bind_param("si", $correo, $id_usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = "Ese correo ya lo está usando otro usuario.";
        }
        $stmt->close();
    }

    // Si no hay errores, actualizamos
    if (empty($errores)) {
        $stmt = $conexion->prepare(
            "UPDATE usuario
             SET nombre_usuario = ?, correo = ?, edad = ?, estilo = ?, perfil = ?, rol = ?
             WHERE id_usuario = ?"
        );
        $stmt->bind_param("ssisssi", $nombre_usuario, $correo, $edad, $estilo, $perfil, $rol, $id_usuario);

        if ($stmt->execute()) {
            $mensaje_exito = "Usuario actualizado correctamente.";
        } else {
            $errores[] = "No se pudo actualizar el usuario.";
        }
        $stmt->close();
    }
}

$usuario_a_editar = null;

if (isset($_GET['editar'])) {
    $id_a_editar = (int) $_GET['editar'];

    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario_a_editar = $resultado->fetch_assoc();
    $stmt->close();
}


$usuarios = [];
$resultado = $conexion->query("SELECT id_usuario, nombre_usuario, correo, edad, estilo, perfil, rol, fecha_registro FROM usuario ORDER BY id_usuario ASC");

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Usuarios - SHMEBULOCK</title>
    <link rel="stylesheet" href="../styles/SHMEBULOCK_styles_shembulock.css">
    <style>
        .crud-contenedor { max-width: 1000px; margin: 30px auto; padding: 0 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
        th { background-color: #222; color: #fff; }
        .acciones a { margin-right: 10px; text-decoration: none; }
        .editar { color: #0066cc; }
        .eliminar { color: #cc0000; }
        .mensaje-exito { color: green; font-weight: bold; }
        .lista-errores { color: red; }
        .form-editar { border: 1px solid #ccc; padding: 15px; margin-top: 20px; max-width: 400px; }
        .form-editar label { display: block; margin-top: 10px; font-weight: bold; }
        .form-editar input, .form-editar select { width: 100%; padding: 6px; margin-top: 4px; }
        .form-editar button { margin-top: 15px; }
    </style>
</head>

<body>

    <main class="crud-contenedor">

        <h1>Gestión de Usuarios</h1>
        <p>CRUD de usuarios: ver, editar y eliminar cuentas registradas.</p>

        <?php if ($mensaje_exito): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($mensaje_exito) ?></p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <ul class="lista-errores">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- TABLA DE USUARIOS -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Edad</th>
                    <th>Estilo</th>
                    <th>Rol</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="8">No hay usuarios cargados todavía.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= (int) $u['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td><?= htmlspecialchars($u['edad'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($u['estilo'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($u['rol']) ?></td>
                            <td><?= htmlspecialchars($u['fecha_registro']) ?></td>
                            <td class="acciones">
                                <a class="editar" href="?editar=<?= (int) $u['id_usuario'] ?>">Editar</a>
                                <a class="eliminar"
                                   href="?eliminar=<?= (int) $u['id_usuario'] ?>"
                                   onclick="return confirm('¿Seguro que querés eliminar a <?= htmlspecialchars($u['nombre_usuario']) ?>?');">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- FORMULARIO DE EDICION -->
        <?php if ($usuario_a_editar): ?>
            <div class="form-editar">
                <h2>Editar usuario #<?= (int) $usuario_a_editar['id_usuario'] ?></h2>

                <form method="POST" action="crud_usuarios.php">
                    <input type="hidden" name="id_usuario" value="<?= (int) $usuario_a_editar['id_usuario'] ?>">

                    <label for="usuario">Nombre de usuario</label>
                    <input type="text" id="usuario" name="usuario"
                           value="<?= htmlspecialchars($usuario_a_editar['nombre_usuario']) ?>" required>

                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo"
                           value="<?= htmlspecialchars($usuario_a_editar['correo']) ?>" required>

                    <label for="edad">Edad</label>
                    <input type="number" id="edad" name="edad" min="13"
                           value="<?= htmlspecialchars($usuario_a_editar['edad'] ?? '') ?>">

                    <label for="estilo">Estilo</label>
                    <input type="text" id="estilo" name="estilo" placeholder="Y2K, Alt, Streetwear..."
                           value="<?= htmlspecialchars($usuario_a_editar['estilo'] ?? '') ?>">

                    <label for="perfil">Perfil</label>
                    <input type="text" id="perfil" name="perfil"
                           value="<?= htmlspecialchars($usuario_a_editar['perfil'] ?? '') ?>">

                    <label for="rol">Rol</label>
                    <select id="rol" name="rol">
                        <option value="cliente" <?= $usuario_a_editar['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                        <option value="admin" <?= $usuario_a_editar['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>

                    <button type="submit">Guardar cambios</button>
                    <a href="crud_usuarios.php">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>

    </main>

</body>

</html>