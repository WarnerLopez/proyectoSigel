
# SIGEL - Sistema de Gestión de Equipos y Préstamos

## Descripción

SIGEL es una aplicación web desarrollada en PHP y MySQL
para administrar usuarios, categorías, equipos y préstamos.

El sistema permite registrar préstamos de uno o varios equipos,
controlar su disponibilidad y consultar reportes.

## Tecnologías utilizadas

- PHP 8.2
- MySQL
- Apache
- XAMPP
- phpMyAdmin
- Bootstrap 5
- HTML y CSS

## Requisitos

- Tener instalado XAMPP.
- Tener habilitados Apache y MySQL.
- Utilizar un navegador web.

## Instalación

### 1. Copiar el proyecto

Copiar la carpeta del proyecto dentro de:

C:\xampp\htdocs\

### 2. Iniciar XAMPP

Abrir el panel de control de XAMPP e iniciar:

- Apache
- MySQL

### 3. Crear la base de datos

Entrar a:

http://localhost/phpmyadmin

Crear una base de datos llamada:

sigel

Se recomienda utilizar el conjunto de caracteres utf8mb4.

### 4. Importar la estructura

Seleccionar la base de datos sigel.

Entrar en la pestaña Importar y seleccionar:

sql/schema.sql

Ejecutar la importación.

### 5. Importar los datos iniciales

En la misma base de datos, importar:

sql/seed.sql

Este archivo registra los roles, usuarios, categorías
y equipos iniciales.

### 6. Configurar la conexión

Abrir el archivo:

config/database.php

Comprobar que la configuración corresponda al entorno:

Host: localhost
Base de datos: sigel
Usuario: root
Contraseña: vacía (configuración habitual de XAMPP)

Si MySQL utiliza otras credenciales, modificar la conexión.

### 7. Configurar el acceso al proyecto

El proyecto se ha desarrollado utilizando el dominio local:

http://proyectosigel.com

Para utilizar ese dominio en otro equipo, se debe configurar
el archivo hosts de Windows y el VirtualHost de Apache.

La configuración exacta depende de la ubicación del proyecto
y del DocumentRoot utilizado.

### 8. Iniciar sesión

Abrir la página de inicio de sesión del proyecto.

Usuarios iniciales:

Administrador:
Correo: admin@sigel.com
Contraseña: Admin123

Operador:
Correo: operador@sigel.com
Contraseña: Operador123

Estas credenciales son únicamente para pruebas académicas.

## Roles

### Administrador

- Gestionar usuarios.
- Gestionar categorías.
- Gestionar equipos.
- Gestionar préstamos.
- Consultar reportes.

### Operador

- Registrar y gestionar préstamos.
- Consultar préstamos de su categoría asignada.
- No puede administrar usuarios ni acceder a reportes.

## Funcionalidades

- Inicio y cierre de sesión.
- Control de acceso por roles.
- CRUD de usuarios, categorías y equipos.
- Búsqueda y paginación.
- Eliminación lógica.
- Registro de préstamos con varios equipos.
- Validación de traslapes de fechas.
- Estados de préstamo: solicitado, aprobado, entregado,
  devuelto y cancelado.
- Reportes de equipos más prestados.
- Reportes de préstamos por categoría.
- Reportes de préstamos por mes.

## Archivos SQL

schema.sql:
Contiene las instrucciones para crear las tablas y relaciones.

seed.sql:
Contiene los datos iniciales necesarios para probar el sistema.

## Consideraciones

- Las contraseñas se almacenan mediante hashes.
- Se utilizan consultas preparadas con PDO.
- Las operaciones de préstamos utilizan transacciones.
- Los permisos se verifican en el servidor.
- Los usuarios, categorías y equipos utilizan eliminación lógica.
