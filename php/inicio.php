<?php
session_start();

$email = $_POST['emailI'] ?? '';
$password = $_POST['contra'] ?? '';
$recordar = isset($_POST['recordar']);

$_SESSION['emailI'] = $email;

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Por favor, completa todos los campos";
    header('Location: ../login.php');
    exit();
}

try {
    $conn = require("conection.php");
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if ($usuario && password_verify($_POST['contra'], $usuario['passw'])) {
        // Login exitoso
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['login'] = true;
        
        // Si marcó "Recordarme", crear cookie
        if ($recordar) {
            // Generar token único y seguro
            $token = bin2hex(random_bytes(32));
            
            // Calcular fecha de expiración (30 días)
            $fecha_creacion = new DateTime('now');
            $fecha_expiracion = new DateTime('now');
            $fecha_expiracion->modify('+30 days');
            
            // Eliminar tokens antiguos del usuario antes de crear uno nuevo
            $stmt_delete = $conn->prepare("DELETE FROM tokens WHERE id_usuario = :id_usuario");
            $stmt_delete->execute([':id_usuario' => $usuario['id']]);
            
            // Guardar token en la base de datos
            $stmt_token = $conn->prepare("INSERT INTO tokens (id_usuario, token, fecha_creacion, fecha_expiracion) VALUES (:id_usuario, :token, :fecha_creacion, :fecha_expiracion)");
            $stmt_token->execute([
                ':id_usuario' => $usuario['id'],
                ':token' => $token,
                ':fecha_creacion' => $fecha_creacion->format('Y-m-d H:i:s'),
                ':fecha_expiracion' => $fecha_expiracion->format('Y-m-d H:i:s')
            ]);
            
            // Crear cookie que dura 30 días
            setcookie('remember_user', $token, [
                'expires' => time() + (30 * 24 * 60 * 60), // 30 días
                'path' => '/',
                'secure' => false, // Cambiar a true si usas HTTPS
                'httponly' => true, // Protección contra XSS
                'samesite' => 'Lax' // Protección contra CSRF
            ]);
        }
        
        // Limpiar variable temporal
        unset($_SESSION['emailI']);
        
        header('Location: biblioteca.php');
        exit();
    } else {
        $_SESSION['error'] = "Email o contraseña incorrectos";
        header('Location: login.php');
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error en el sistema. Intenta de nuevo.";
    header('Location: ../index.php');
    exit();
}

$conn = null;
?>