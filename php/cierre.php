<?php
    session_start();

    if(isset($_POST['cerrar'])) {
        session_destroy();
        header('Location:http://localhost/Biblioteca-Online');
    }
    else header('Location:http://localhost/Biblioteca-Online/biblioteca.php');