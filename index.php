<?php
session_start();

// Si ya está autenticado con sesión, redirigir a biblioteca
if (isset($_SESSION['id_usuario'])) {
    header('Location: php/biblioteca.php');
    exit();
}

// Verificar si hay cookie de "Recordarme"
if (isset($_COOKIE['remember_user'])) {
    $conn = require("php/conection.php");
    $token = $_COOKIE['remember_user'];
    
    // Buscar el token y verificar que no haya expirado
    $stmt = $conn->prepare("SELECT id_usuario, fecha_expiracion FROM tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tokenData) {
        $fecha_expiracion = new DateTime($tokenData['fecha_expiracion']);
        $fecha_actual = new DateTime('now');
        
        // Verificar si el token no ha expirado
        if ($fecha_actual <= $fecha_expiracion) {
            // Token válido, restaurar la sesión
            $stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt_usuario->execute([':id' => $tokenData['id_usuario']]);
            $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['login'] = true;
                
                header('Location: php/biblioteca.php');
                exit();
            }
        } else {
            // Token expirado, eliminar de BD y cookie
            $stmt_delete = $conn->prepare("DELETE FROM tokens WHERE token = :token");
            $stmt_delete->execute([':token' => $token]);
            
            setcookie('remember_user', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    } else {
        // Token no encontrado, eliminar cookie
        setcookie('remember_user', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca online</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .welcome-hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #8b5a2b 0%, #6b4423 100%);
            color: #fefdfb;
            margin: -50px -50px 40px -50px;
            box-shadow: 0 4px 20px rgba(139, 90, 43, 0.3);
        }
        
        .welcome-hero h1 {
            font-size: 4em;
            color: #fefdfb;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .welcome-hero p {
            font-size: 1.3em;
            color: #d4a574;
            font-style: italic;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .welcome-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }
        
        .welcome-card {
            background: #faf8f5;
            padding: 40px 30px;
            border: 2px solid #d4a574;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(139, 90, 43, 0.1);
        }
        
        .welcome-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(139, 90, 43, 0.2);
            border-color: #8b5a2b;
        }
        
        .welcome-card h2 {
            color: #8b5a2b;
            font-size: 2em;
            margin-bottom: 20px;
            border: none;
        }
        
        .welcome-card p {
            color: #6b4423;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .welcome-card .btn-primary {
            display: inline-block;
            background-color: #8b5a2b;
            color: #fefdfb;
            padding: 16px 40px;
            text-decoration: none;
            border: 2px solid #6b4423;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(139, 90, 43, 0.2);
        }
        
        .welcome-card .btn-primary:hover {
            background-color: #6b4423;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(139, 90, 43, 0.3);
        }
        
        .welcome-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
        <div class="container">
        <div class="welcome-hero">
            <h1>Biblioteca Online</h1>
            <p>Tu espacio personal para gestionar y compartir tu colección de libros</p>
        </div>
        
        <div class="welcome-options">
            <div class="welcome-card">
                <div class="welcome-icon">🔐</div>
                <h2>Iniciar Sesión</h2>
                <p>¿Ya tienes una cuenta? Accede a tu biblioteca personal y descubre nuevos libros.</p>
                <a href="php/login.php" class="btn-primary">Iniciar Sesión</a>
            </div>
            
            <div class="welcome-card">
                <div class="welcome-icon">✨</div>
                <h2>Registrarse</h2>
                <p>¿Nuevo aquí? Crea tu cuenta y comienza a construir tu biblioteca digital.</p>
                <a href="php/registrar.php" class="btn-primary">Crear Cuenta</a>
            </div>
        </div>
        
        <div style="margin-top: 60px; text-align: center; padding: 30px; background: #faf8f5; border-left: 4px solid #d4a574;">
            <h3 style="color: #8b5a2b; margin-bottom: 15px;">¿Qué puedes hacer en Biblioteca Online?</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 25px;">
                <div>
                    <strong style="color: #6b4423;">📖 Registrar libros</strong>
                    <p style="color: #8b7355; margin-top: 5px; font-size: 0.9em;">Añade tus libros favoritos con portadas e información detallada</p>
                </div>
                <div>
                    <strong style="color: #6b4423;">⭐ Valorar lecturas</strong>
                    <p style="color: #8b7355; margin-top: 5px; font-size: 0.9em;">Da tu opinión y descubre lo que otros piensan</p>
                </div>
                <div>
                    <strong style="color: #6b4423;">🔍 Buscar y explorar</strong>
                    <p style="color: #8b7355; margin-top: 5px; font-size: 0.9em;">Encuentra libros por título, autor o género</p>
                </div>
            </div>
        </div>
</body>
</html>