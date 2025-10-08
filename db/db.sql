CREATE DATABASE Biblioteca;

USE Biblioteca;

CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,    
    email VARCHAR(100) NOT NULL UNIQUE,
    passw VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE generos (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE libros (
    id INT NOT NULL AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    fecha DATE,
    link VARCHAR(500),
    descripcion TEXT,
    id_usuario INT NOT NULL,
    img VARCHAR(255),
    PRIMARY KEY(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE libro_genero (
    id_libro INT NOT NULL,
    id_genero INT NOT NULL,
    PRIMARY KEY(id_libro, id_genero),
    FOREIGN KEY (id_libro) REFERENCES libros(id) ON DELETE CASCADE,
    FOREIGN KEY (id_genero) REFERENCES generos(id) ON DELETE CASCADE
);