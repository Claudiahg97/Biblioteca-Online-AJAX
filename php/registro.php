<?php
session_start();

$comprobarPass = false;
$comprobarEmail = false;

$_SESSION['nombre'] = $_POST['name'];

// Validar contraseña
if ((strlen($q) >= 8)&&(preg_match('/[A-Z]/', $q))&&(preg_match('/[a-z]/', $q))&&(preg_match('/[0-9]/', $q))&&(preg_match('/[!@#$%^&*()_\+\-=\[\]{};:\'",.<>?\/\\|`~]/', $q))) {

    if ($_POST['password'] === $_POST['compPassword'] && $_POST['password'] != "") {
        $comprobarPass = true;
    } else {
        $_SESSION['error'] = "La contraseña no es válida";
        header('Location: http://localhost/Biblioteca-Online');
    } 
}

// Validar email
if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $comprobarEmail = true;
    $_SESSION['email'] = $_POST['email'];
} else {
    $_SESSION['error'] = "Formato de email incorrecto";
    header('Location: http://localhost/Biblioteca-Online');
}

// Si las validaciones pasaron, verificar si el usuario existe
try {
    $conn = require("conection.php");

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($usuario)) {
        // Usuario NO existe, podemos registrarlo
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, passw) VALUES (:nombre, :email, :passw)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passw', $password);

        $nombre = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_ARGON2ID);
        $stmt->execute();

        $_SESSION['error'] = "Se ha registrado correctamente";
        $_SESSION['nombre'] = "";
        $_SESSION['email'] = "";
    } else {
        // Usuario ya existe
        $_SESSION['email'] = "";
        $_SESSION['error'] = "Ya existe un usuario con ese email";
    }

} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

$conn = null;
header('Location: http://localhost/Biblioteca-Online');
