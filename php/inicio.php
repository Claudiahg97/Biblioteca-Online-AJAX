<?php
    session_start();

    $email = $_POST['emailI'];
    try {
        $conn = require( "conection.php");
        
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC); 
        /*Guardo la información sacada de la base de datos en la variable $usuario
        * con la función fetch(PDO::FETCH_ASSOC) el contenido es un array asosiativo
        */
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    if ($usuario) { 
                 
        if (password_verify($_POST['contra'], $usuario['passw'])) {
            $_SESSION['error'] = "Inicio de sesión exitoso";
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['login'] = true;
            
            header('Location:http://localhost/Biblioteca-Online/php/biblioteca.php');
        }else{
            $_SESSION['error'] = "Inicio de sesión incorrecto";
            $_SESSION['emailI'] = $usuario['email'];
            header('Location:http://localhost/Biblioteca-Online');
        }
    }
    $conn = null;
        
    
