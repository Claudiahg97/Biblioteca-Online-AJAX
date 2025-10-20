<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$conn = require("conection.php");

// Obtener el usuario actual usando id_usuario
$stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt_usuario->bindParam(':id', $_SESSION['id_usuario']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el usuario
if (!$usuario) {
    die("Usuario no encontrado");
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $pasww_antigua = $_POST['pasww_antigua'] ?? '';
    $pasww_nueva = $_POST['pasww_nueva'] ?? '';
    $pasww_confirmar = $_POST['pasww_confirmar'] ?? '';

    $errores = [];

    // Validar email
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } else {
        // Verificar si el email ya existe (solo si es diferente al actual)
        if ($email !== $usuario['email']) {
            $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt_check->bindParam(':email', $email);
            $stmt_check->execute();
            
            if ($stmt_check->fetch()) {
                $errores[] = "El email ya está registrado por otro usuario";
            }
        }
    }

    // Validar cambio de contraseña si se intenta cambiar
    $cambiar_password = false;
    if (!empty($pasww_antigua) || !empty($pasww_nueva) || !empty($pasww_confirmar)) {
        // Si algún campo de contraseña tiene datos, todos deben tenerlos
        if (empty($pasww_antigua)) {
            $errores[] = "Debes proporcionar tu contraseña actual";
        } elseif (empty($pasww_nueva)) {
            $errores[] = "Debes proporcionar la nueva contraseña";
        } elseif (empty($pasww_confirmar)) {
            $errores[] = "Debes confirmar la nueva contraseña";
        } else {
            // Verificar que la contraseña antigua sea correcta
            if (!password_verify($pasww_antigua, $usuario['passw'])) {
                $errores[] = "La contraseña actual es incorrecta";
            }
            
            // Verificar que las contraseñas nuevas coincidan
            if ($pasww_nueva !== $pasww_confirmar) {
                $errores[] = "Las contraseñas nuevas no coinciden";
            }
            
            // Validar longitud de la nueva contraseña
            if (strlen($pasww_nueva) < 8) {
                $errores[] = "La nueva contraseña debe tener al menos 8 caracteres";
            }
            
            if (empty($errores)) {
                $cambiar_password = true;
            }
        }
    }

    // Si no hay errores, proceder con la actualización
    if (empty($errores)) {
        // Manejar la imagen si se sube una nueva
        $img_path = $usuario['img']; // Mantener la imagen anterior por defecto
        
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/users/';
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES['img']['name']);
            $target_file = $upload_dir . uniqid() . '_' . $file_name;
            
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['img']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['img']['tmp_name'], $target_file)) {
                    $img_path = str_replace('../', '', $target_file);
                    
                    // Eliminar la imagen anterior si existe
                    if (!empty($usuario['img']) && file_exists('../' . $usuario['img'])) {
                        unlink('../' . $usuario['img']);
                    }
                }
            }
        }
        
        // Actualizar el usuario en la base de datos
        if ($cambiar_password) {
            // Hashear la nueva contraseña con ARGON2ID
            $password_hash = password_hash($pasww_nueva, PASSWORD_ARGON2ID);
            
            $sql_update = "UPDATE usuarios SET 
                           nombre = :nombre,
                           email = :email, 
                           passw = :passw,
                           img = :img 
                           WHERE id = :id";
            
            $stmt_update = $conn->prepare($sql_update);
            $resultado = $stmt_update->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':passw' => $password_hash,
                ':img' => $img_path,
                ':id' => $_SESSION['id_usuario']
            ]);
        } else {
            // Si no hay cambio de contraseña, solo actualizar nombre, email e imagen
            $sql_update = "UPDATE usuarios SET 
                           nombre = :nombre,
                           email = :email,
                           img = :img 
                           WHERE id = :id";
            
            $stmt_update = $conn->prepare($sql_update);
            $resultado = $stmt_update->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':img' => $img_path,
                ':id' => $_SESSION['id_usuario']
            ]);
        }
        
        if ($resultado) {
            // Actualizar la sesión con los nuevos datos
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            
            // Establecer mensaje de éxito
            $_SESSION['exito'] = "Perfil actualizado correctamente";
            
            // Redirigir al perfil del usuario
            header("Location: mostrarUsuario.php");
            exit();
        } else {
            $errores[] = "Error al actualizar el perfil";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../style/style.css">
    <script>
        function showHint(str) {
            if (str.length == 0) {
                document.getElementById("txtHint").innerHTML = "";
                return;
            } else {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("txtHint").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET", "politicas.php?q=" + encodeURIComponent(str), true);
                xmlhttp.send();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Editar Perfil</h1>
        
        <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                <small class="info">Asegúrate de usar un email válido</small>
            </div>
            
            <hr style="margin: 30px 0;">
            <h3 style="margin-bottom: 20px; color: #8b5a2b;">Cambiar Contraseña (Opcional)</h3>
            
            <div class="form-group">
                <label for="pasww_antigua">Contraseña Actual</label>
                <input type="password" id="pasww_antigua" name="pasww_antigua" placeholder="Ingresa tu contraseña actual">
                <small class="info">Requerida solo si deseas cambiar la contraseña</small>
            </div>
            
            <div class="form-group">
                <label for="pasww_nueva">Nueva Contraseña</label>
                <input type="password" id="pasww_nueva" name="pasww_nueva" placeholder="Mínimo 8 caracteres" onkeyup="showHint(this.value)">
                <small class="info">Debe tener al menos 8 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="pasww_confirmar">Confirmar Nueva Contraseña</label>
                <input type="password" id="pasww_confirmar" name="pasww_confirmar" placeholder="Repite la nueva contraseña" onkeyup="showHint(this.value)">
                <small class="info">Debe coincidir con la nueva contraseña</small>
            </div>

            <div class="form-group">
                <label>Políticas de Contraseña:</label>
                <div id="txtHint" style="padding: 10px; background: #faf8f5; border: 1px solid #d4a574; min-height: 40px;"></div>
            </div>
            
            <hr style="margin: 30px 0;">
            
            <div class="form-group">
                <label for="img">Imagen de perfil</label>
                <?php if (!empty($usuario['img'])): ?>
                    <p>Imagen actual:</p>
                    <img src="<?php echo htmlspecialchars('../' . $usuario['img']); ?>" alt="Imagen de perfil actual" class="imagen-actual">
                <?php endif; ?>
                <input type="file" id="img" name="img" accept="image/*">
                <small class="info">Deja vacío si no deseas cambiar la imagen</small>
            </div>
            
            <button type="submit">Guardar cambios</button>
            <button type="button" class="btn-cancelar" onclick="window.location.href='mostrarUsuario.php'">Cancelar</button>
        </form>
        
        <br>
        <a href="biblioteca.php">← Volver a la página principal</a>
    </div>
</body>
</html>