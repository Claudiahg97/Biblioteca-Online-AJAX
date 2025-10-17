<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$conn = require("conection.php");

// Obtener el usuario actual
$stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt_usuario->bindParam(':email', $_SESSION['email']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el usuario
if (!$usuario) {
    die("Usuario no encontrado");
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $pasww = $_POST['pasww'] ?? '';

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
    // Si la contraseña está vacía, no la actualizamos
    if (!empty($pasww)) {
        $sql_update = "UPDATE usuarios SET 
                       nombre = :nombre, 
                       pasww = :pasww,
                       img = :img 
                       WHERE email = :email";
        
        $stmt_update = $conn->prepare($sql_update);
        $resultado = $stmt_update->execute([
            ':nombre' => $nombre,
            ':pasww' => $pasww,
            ':img' => $img_path,
            ':email' => $_SESSION['email']
        ]);
    } else {
        // Si no hay contraseña, solo actualizar nombre e imagen
        $sql_update = "UPDATE usuarios SET 
                       nombre = :nombre,
                       img = :img 
                       WHERE email = :email";
        
        $stmt_update = $conn->prepare($sql_update);
        $resultado = $stmt_update->execute([
            ':nombre' => $nombre,
            ':img' => $img_path,
            ':email' => $_SESSION['email']
        ]);
    }
    
    if ($resultado) {
        // Actualizar la sesión con el nuevo nombre
        $_SESSION['nombre'] = $nombre;
        
        // Establecer mensaje de éxito
        $_SESSION['exito'] = "Perfil actualizado correctamente";
        
        // Redirigir al perfil del usuario
        header("Location: mostrarUsuario.php");
        exit();
    } else {
        $error = "Error al actualizar el perfil";
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
</head>
<body>
    <div class="container">
        <h1>Editar Perfil</h1>
        
        <?php if (isset($error)): ?>
            <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="pasww">Contraseña</label>
                <input type="password" id="pasww" name="pasww" placeholder="Dejar vacío si no deseas cambiarla">
                <small class="info">Solo completa este campo si deseas cambiar tu contraseña</small>
            </div>
            
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