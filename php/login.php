<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca online</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <a href="../index.php">← Volver a la página principal</a>
    <div class="container auth-container">
        <h1>Biblioteca Online</h1>
        
        <form action="inicio.php" method = "POST">
        <h3>Inicio de Sesión</h3>
        Email: <input type="text" name="emailI" placeholder= "ej: ejemplo@mail.com" value = "<?php  if(isset($_SESSION['emailI'])) echo $_SESSION['emailI']?>"><br>       
        Contraseña*: <input type="password" name="contra" required > <br>
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="recordar" name="recordar" style="width: auto;">
                <label for="recordar" style="margin: 0; text-transform: none;">Recordarme en este dispositivo</label>
        </div>
        <input type="submit" name= "abrir" value="Iniciar Sesión" >
        </form>
        <?php        
            if (isset($_SESSION['error'])){
                echo "<h3>" . $_SESSION['error'] . "<h3>";
            }        
        ?>
    </div>
</body>
</html>
