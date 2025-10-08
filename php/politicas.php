<?php

    //Parametro por URL
    $q = $_REQUEST['q'];
    
    // Verificar longitud mínima
    if (strlen($q) >= 8) echo '<div style="color: green;">Debe tener más de 8 caracteres</div>';
    else echo '<div style="color: red;">Debe tener más de 8 caracteres</div>';
    
    
    // Verificar al menos una mayúscula
    if (!preg_match('/[A-Z]/', $q)) echo'<div style="color: red;">Debe contener al menos una letra mayúscula</div>';
    else echo'<div style="color: green;">Debe contener al menos una letra mayúscula</div>';
    
    
    // Verificar al menos una minúscula
    if (!preg_match('/[a-z]/', $q)) echo'<div style="color: red;">Debe contener al menos una letra minúscula</div>';
    else echo'<div style="color: green;">Debe contener al menos una letra minúscula</div>';
    
    // Verificar al menos un número
    if (!preg_match('/[0-9]/', $q)) echo'<div style="color: red;">Debe contener al menos un número</div>';
    else echo'<div style="color: green;">Debe contener al menos un número</div>';
    
    
    // Verificar al menos un símbolo especial
    if (!preg_match('/[!@#$%^&*()_\+\-=\[\]{};:\'",.<>?\/\\|`~]/', $q)) echo'<div style="color: red;">Debe contener al menos un símbolo especial</div>';
    else echo'<div style="color: green;">Debe contener al menos un símbolo especial</div>';
