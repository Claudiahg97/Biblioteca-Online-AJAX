# 📚 Biblioteca Online

Sistema de gestión de biblioteca digital desarrollado en PHP, MySQL y JavaScript.

## 🚀 Características

- **Gestión de usuarios**: Registro, login y validación de contraseñas seguras
- **Catálogo de libros**: Búsqueda, visualización y gestión de inventario
- **Sistema de préstamos**: Control de préstamos y devoluciones
- **Validación en tiempo real**: Feedback instantáneo en formularios
- **Interfaz responsive**: Diseño adaptable a diferentes dispositivos

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Navegador web moderno

## 🛠️ Instalación

### 1. Clonar o descargar el proyecto

```bash
git clone [URL_DEL_REPOSITORIO]
cd biblioteca-online
```

### 2. Configurar la base de datos

Crear una base de datos MySQL:

```sql
CREATE DATABASE biblioteca;
USE biblioteca;
```

Importar el archivo SQL proporcionado o crear las tablas necesarias.

### 3. Configurar conexión a la base de datos

Editar el archivo de configuración PHP (generalmente `config.php` o `conexion.php`):

```php
<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "biblioteca";

$conn = new mysqli($servidor, $usuario, $password, $basedatos);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
```

### 4. Configurar permisos

Asegurarse de que el servidor web tenga permisos de lectura en todos los archivos.

## 📁 Estructura del proyecto

```
biblioteca-online/
├── index.php              # Página principal
├── registro.php           # Registro de usuarios
├── login.php             # Inicio de sesión
├── php/
│   ├── config.php        # Configuración de BD
│   ├── politicas.php     # Validación de contraseñas
│   └── ...               # Otros scripts PHP
├── js/
│   └── validaciones.js   # Validaciones del lado del cliente
├── css/
│   └── styles.css        # Estilos
└── README.md
```

## 🔐 Políticas de Contraseña

El sistema implementa validación robusta de contraseñas:

- ✅ Más de 8 caracteres
- ✅ Al menos una letra mayúscula
- ✅ Al menos una letra minúscula
- ✅ Al menos un número
- ✅ Al menos un símbolo especial (!@#$%^&*()_+-=[]{}etc.)

### Ejemplo de validación en JavaScript

```javascript
function showHint(str) {
    if (str.length == 0) {
        document.getElementById("txtHint").innerHTML = "";
        return;
    }
    
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("txtHint").innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "php/politicas.php?q=" + encodeURIComponent(str), true);
    xmlhttp.send();
}
```

## 💻 Uso

### Registro de usuario

1. Acceder a `registro.php`
2. Completar el formulario con los datos requeridos
3. La contraseña será validada en tiempo real
4. Confirmar el registro

### Inicio de sesión

1. Acceder a `login.php`
2. Ingresar credenciales
3. Acceder al sistema

### Gestión de libros

[Describir funcionalidades específicas de tu sistema]

## 🔧 Configuración avanzada

### Personalizar validación de contraseñas

Editar `php/politicas.php` para ajustar los requisitos:

```php
function validarContrasena($password) {
    $errores = [];
    
    if (strlen($password) <= 8) {
        $errores[] = "Debe tener más de 8 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = "Debe contener al menos una letra mayúscula";
    }
    
    // ... más validaciones
    
    return [
        'valida' => empty($errores),
        'errores' => $errores
    ];
}
```

## 🐛 Solución de problemas

### El símbolo + no se detecta en la validación

Asegurarse de usar `encodeURIComponent()` al enviar datos por GET:

```javascript
xmlhttp.open("GET", "php/politicas.php?q=" + encodeURIComponent(str), true);
```

### Error de conexión a la base de datos

Verificar:
- Credenciales en el archivo de configuración
- Que el servidor MySQL esté corriendo
- Permisos del usuario de base de datos

### Página en blanco

Habilitar errores en PHP para debug:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📝 Licencia

[Especificar licencia del proyecto]

## 👥 Contribuciones

[Instrucciones para contribuir al proyecto]

## 📧 Contacto

[Información de contacto del desarrollador/equipo]

## 🔄 Actualizaciones

### Versión 1.0.0
- Sistema de registro y login
- Validación de contraseñas
- Catálogo básico de libros

---

**Desarrollado con ❤️ para la gestión eficiente de bibliotecas**