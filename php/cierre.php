<?php
session_start();

$conn = require("conection.php");

// Si el usuario tiene una cookie de "recordarme", eliminarla
if (isset($_COOKIE['remember_user'])) {
    $token = $_COOKIE['remember_user'];
    
    // Eliminar el token de la base de datos
    if (isset($_SESSION['id_usuario'])) {
        $stmt = $conn->prepare("DELETE FROM tokens WHERE id_usuario = :id_usuario AND token = :token");
        $stmt->execute([
            ':id_usuario' => $_SESSION['id_usuario'],
            ':token' => $token
        ]);
    }
    
    // Eliminar la cookie del navegador
    setcookie('remember_user', '', [
        'expires' => time() - 3600, // Fecha en el pasado
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir a la página de bienvenida
header('Location: ../index.php');
exit();
?>