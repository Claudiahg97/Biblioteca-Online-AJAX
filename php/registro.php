<?php
session_start();

$comprobarPass = false;
$comprobarEmail = false;
$q = $_POST['password'];

$_SESSION['nombre'] = $_POST['name'];

// Validar contraseña
if ((strlen($q) >= 8)&&(preg_match('/[A-Z]/', $q))&&(preg_match('/[a-z]/', $q))&&(preg_match('/[0-9]/', $q))&&(preg_match('/[!@#$%^&*()_\+\-=\[\]{};:\'",.<>?\/\\|`~]/', $q))) {
    
    if ($_POST['password'] === $_POST['compPassword'] && $_POST['password'] != "") {
        $comprobarPass = true;
    } else {
        echo "<script>alert('La contraseña no es valida'); window.history.back();</script>";
        $_SESSION['error'] = "La contraseña no es válida";
        header('Location: http://localhost/Biblioteca-Online');
    } 
}

// Validar email
if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $comprobarEmail = true;
    $_SESSION['email'] = $_POST['email'];
} else {
    echo "<script>alert('Formato de correo incorrecto'); window.history.back();</script>";
    $_SESSION['error'] = "Formato de email incorrecto";
    header('Location: http://localhost/Biblioteca-Online');
}

// Si las validaciones pasaron, verificar si el usuario existe
try {
    $conn = require("conection.php");

    $target_dir = "../uploads/users/";
    $target_file_db = "";

    // Manejo de la imagen
    if (empty($_FILES["fileToUpload"]["name"])) {
        $target_file_db = "uploads/users/default.png"; // Ruta relativa para guardar en BD
    } else {
        $nameOriginal = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        // Hash único: sha256(nombreArchivo_nombreUsuario)
        $hashNombre = hash('sha256', $nameOriginal . '_' . $_SESSION['nombre']);
        $newFileName = $hashNombre . '.' . $extension; // Nombre único
        $target_file = $target_dir . $newFileName;
        $target_file_db = "uploads/users/" . $newFileName; // Ruta para BD

        // Verificar que es una imagen
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check === false) {
            echo "<script>alert('El archivo no es una imagen válida'); window.history.back();</script>";
            exit;
        }

        // Verificar tamaño (5MB máximo)
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            echo "<script>alert('El archivo es demasiado grande'); window.history.back();</script>";
            exit;
        }

        // Permitir ciertos formatos
        if(!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<script>alert('Solo se permiten archivos JPG, JPEG, PNG y GIF'); window.history.back();</script>";
            exit;
        }

        // Intentar subir el archivo
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "<script>alert('Error al subir la imagen'); window.history.back();</script>";
            exit;
        }
    }

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($usuario)) {
        // Usuario NO existe, podemos registrarlo
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, passw, img) VALUES (:nombre, :email, :passw, :img)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passw', $password);
        $stmt->bindParam(':img', $img);

        $nombre = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_ARGON2ID);
        $img = $target_file_db;
        $stmt->execute();

        $_SESSION['error'] = "Se ha registrado correctamente";
        $_SESSION['nombre'] = "";
        $_SESSION['email'] = "";
    } else {
        // Usuario ya existe
        echo "<script>alert('Correo en uso'); window.history.back();</script>";
        $_SESSION['email'] = "";
        $_SESSION['error'] = "Ya existe un usuario con ese email";
    }

} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

$conn = null;
header('Location: http://localhost/Biblioteca-Online/php/login.php');
