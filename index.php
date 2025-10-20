<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca online</title>
    <link rel="stylesheet" href="style/style.css">
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
            xmlhttp.open("GET", "php/politicas.php?q=" + encodeURIComponent(str), true);
            xmlhttp.send();
        }
        }
    </script>
</head>
<body>
    <div class="container auth-container">
        <h1>Biblioteca Online</h1>
        <form action="php/registro.php" method = "POST" enctype="multipart/form-data">
        <h3>Registro de nuevo usuario</h3>        
        Nombre: <input type="text" name="name" placeholder= "ej: Santiago" value = "<?php if(isset($_SESSION['nombre'])) echo $_SESSION['nombre']?>"> <br>   
        Email: <input type="text" name="email" placeholder= "ej: ejemplo@mail.com" value = "<?php  if(isset($_SESSION['email'])) echo $_SESSION['email']?>"><br>       
        Foto de perfil: <input type="file" name="fileToUpload" id="fileToUpload"><br>
        Contraseña*: <input type="password" name="password" required onkeyup="showHint(this.value)"> <br>
        Repite la contraseña*: <input type="password" name="compPassword" required onkeyup="showHint(this.value)"> <br>        
        <input type="submit" name= "abrir" value="Registrarse" >
        </form>
        <small class="info">Politicas:<div id="txtHint"></div><br></smart>

        <form action="php/inicio.php" method = "POST">
        <h3>Inicio de Sesión</h3>
        Email: <input type="text" name="emailI" placeholder= "ej: ejemplo@mail.com" value = "<?php  if(isset($_SESSION['emailI'])) echo $_SESSION['emailI']?>"><br>       
        Contraseña*: <input type="password" name="contra" required > <br>
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
<?php session_destroy(); ?>
