# Guía de Instalación y Ejecución
## Sistema de Gestión de Préstamos de Equipos Tecnológicos

> **Asignatura:** Construcción de Software  
> **Equipo:** S. B. Acosta Andrade · R. A. Aguirre Mora · D. P. Guaman Yucailla · J. S. Herrera Merchan · A. N. Lopez Mena · J. I. Macias Castro · A. J. Massott Diaz · M. J. Gomez Junco · K. A. Ormaza Padilla  
> **Tecnologías:** PHP 8.x · MySQL 8 · Tailwind CSS · Vanilla JS · XAMPP  
> **Repositorio:** https://github.com/DGPYParzival/prestamos-equipos  
> **Credenciales demo:** admin@universidad.edu / password  
> **Fecha:** Mayo 2026

---

## 0. Requisitos previos

Antes de clonar el repositorio, el equipo en el que se ejecutará el proyecto debe tener instalado lo siguiente. Todo es gratuito y de código abierto.

| Software | Versión | Descarga |
|----------|---------|----------|
| XAMPP | 8.2 o superior | apachefriends.org |
| Git | 2.x o superior | git-scm.com |
| VS Code (IDE) | Cualquier versión reciente | code.visualstudio.com |
| Navegador web | Chrome / Firefox / Edge | Ya instalado en el sistema |

>  XAMPP incluye Apache, MySQL y PHP en un solo instalador. No es necesario instalarlos por separado.

---

## 1. Instalar y configurar XAMPP

### Paso 1.1 — Descargar e instalar XAMPP

Accede a `apachefriends.org`, descarga el instalador para Windows y ejecútalo. Acepta todas las opciones por defecto. La ruta de instalación recomendada es `C:\xampp` (no cambiarla).

### Paso 1.2 — Iniciar Apache y MySQL

Abre el Panel de Control de XAMPP (busca "XAMPP Control Panel" en el menú Inicio) y haz clic en el botón **Start** de **Apache** y luego en el de **MySQL**. Ambos deben quedar en color verde.

>  **Si Apache no arranca:** el puerto 80 puede estar ocupado por otro programa. Ve a **Config → httpd.conf**, busca la línea `Listen 80` y cámbiala por `Listen 8080`. Luego vuelve a intentar Start.

### Paso 1.3 — Verificar que el servidor funciona

Abre el navegador y escribe la siguiente dirección. Si ves la pantalla de bienvenida de XAMPP, el servidor está listo.

```
http://localhost/
```

---

## 2. Clonar el repositorio de GitHub

### Paso 2.1 — Abrir la terminal (cmd o Git Bash)

Presiona las teclas `Windows + R`, escribe `cmd` y pulsa Enter. También puedes usar Git Bash si lo tienes instalado.

### Paso 2.2 — Navegar a la carpeta htdocs de XAMPP

`htdocs` es la carpeta raíz del servidor Apache. Cualquier proyecto que pongas aquí será accesible desde el navegador.

```bash
cd C:\xampp\htdocs
```

### Paso 2.3 — Clonar el repositorio

Ejecuta el siguiente comando. Git descargará todos los archivos del proyecto y creará la carpeta `prestamos-equipos` automáticamente.

```bash
git clone https://github.com/DGPYParzival/prestamos-equipos.git
```

### Paso 2.4 — Verificar que los archivos están presentes

Entra a la carpeta recién clonada y lista su contenido. Debes ver los archivos `index.php`, `schema.sql`, `.env.example`, etc.

```bash
cd prestamos-equipos
dir
```

>  Si ves los archivos del proyecto listados en la terminal, el clonado fue exitoso.

---

## 3. Abrir el proyecto en Visual Studio Code

Visual Studio Code (VS Code) es el IDE seleccionado por el equipo. Es gratuito, liviano y tiene soporte nativo para PHP, HTML, CSS, JavaScript, Git y MySQL.

### Paso 3.1 — Abrir la carpeta del proyecto en VS Code

Desde la misma terminal donde clonaste el proyecto, ejecuta el siguiente comando. VS Code abrirá directamente la carpeta raíz del proyecto.

```bash
code C:\xampp\htdocs\prestamos-equipos
```

**Alternativa — usando la interfaz gráfica de VS Code:**

- Abre VS Code desde el menú Inicio.
- Ve a **Archivo → Abrir carpeta** (o presiona `Ctrl + K`, luego `Ctrl + O`).
- Navega hasta `C:\xampp\htdocs\prestamos-equipos` y haz clic en **Seleccionar carpeta**.

>  Es importante abrir la **CARPETA completa**, no un archivo suelto. De esta forma VS Code reconoce el proyecto, el árbol de archivos, Git y las extensiones funcionan correctamente.

### Paso 3.2 — Instalar extensiones recomendadas

Ve al panel de Extensiones (`Ctrl + Shift + X`) e instala las siguientes. Son las que usa el equipo para mantener el código limpio y documentado.

| Extensión | Para qué sirve |
|-----------|---------------|
| PHP Intelephense | Autocompletado, detección de errores y navegación en PHP |
| MySQL (cweijan) | Conectar y ejecutar queries MySQL directamente desde VS Code |
| Tailwind CSS IntelliSense | Autocompletado de clases Tailwind en HTML y PHP |
| GitLens | Historial de commits y autoría de cambios por línea |
| Prettier | Formateado automático de HTML, JS y CSS |
| Path Intellisense | Autocompletado de rutas de archivos en include/require |

---

## 4. Crear el archivo de configuración (.env)

El archivo `.env` contiene las credenciales de conexión a la base de datos. No está incluido en el repositorio por seguridad (está en el `.gitignore`). Debes crearlo manualmente.

### Paso 4.1 — Crear el archivo .env desde la plantilla

En la raíz del proyecto encontrarás el archivo `.env.example` con la estructura base. Cópialo y renómbralo a `.env` usando la terminal integrada de VS Code (`` Ctrl + ` ``):

```bash
copy .env.example .env
```

### Paso 4.2 — Editar el archivo .env

Abre el archivo `.env` en VS Code (aparece en el explorador de archivos de la izquierda) y verifica que tenga exactamente estos valores para una instalación local con XAMPP:

```env
DB_HOST=localhost
DB_NAME=prestamos_equipos
DB_USER=root
DB_PASS=
```

>  En XAMPP, el usuario de MySQL es `root` y la contraseña está vacía por defecto. Si configuraste una contraseña personalizada, escríbela en `DB_PASS`.

>  **NUNCA** subas el archivo `.env` a GitHub. Contiene credenciales sensibles. El `.gitignore` del proyecto ya lo excluye automáticamente.

---

## 5. Crear la base de datos

El proyecto incluye el archivo `schema.sql` con toda la estructura de tablas y el archivo `seed.sql` con datos de prueba. Hay dos formas de ejecutarlos:

### Opción A — Usando phpMyAdmin (recomendada, más visual)

#### Paso A.1 — Abrir phpMyAdmin

Con Apache y MySQL corriendo en XAMPP, abre el navegador y ve a:

```
http://localhost/phpmyadmin
```

#### Paso A.2 — Crear la base de datos

En el panel izquierdo, haz clic en **Nueva**. En el campo "Nombre de la base de datos" escribe exactamente:

```
prestamos_equipos
```

En el selector de cotejamiento elige `utf8mb4_unicode_ci` y haz clic en **Crear**.

#### Paso A.3 — Ejecutar schema.sql

Con la base de datos `prestamos_equipos` seleccionada en el panel izquierdo, haz clic en la pestaña **SQL**. Abre el archivo `schema.sql` desde VS Code, copia todo su contenido, pégalo en el cuadro de texto y haz clic en **Continuar**.

#### Paso A.4 — Ejecutar seed.sql

Repite el mismo proceso de la pestaña SQL, pero ahora con el contenido del archivo `seed.sql`. Esto cargará las categorías de equipos y el usuario administrador por defecto.

> Si no aparecen errores en rojo, la base de datos está lista. Verás las 6 tablas listadas en el panel izquierdo: `usuarios`, `categorias`, `equipos`, `prestamos`, `sanciones`, `mantenimientos`.

### Opción B — Usando la terminal de VS Code

#### Paso B.1 — Abrir la terminal integrada de VS Code

Presiona `` Ctrl + ` `` para abrir la terminal dentro de VS Code. Asegúrate de estar en la carpeta raíz del proyecto.

#### Paso B.2 — Ejecutar los archivos SQL

Ejecuta los siguientes dos comandos en orden. El sistema te pedirá la contraseña de MySQL (si usas XAMPP por defecto, presiona Enter sin escribir nada):

```bash
mysql -u root -p < schema.sql
mysql -u root -p prestamos_equipos < seed.sql
```

---

## 6. Acceder al sistema

### Paso 6.1 — Verificar que Apache y MySQL siguen corriendo

Vuelve al Panel de Control de XAMPP y confirma que Apache y MySQL tienen el indicador verde. Si los detuviste, haz clic en **Start** nuevamente.

### Paso 6.2 — Abrir el sistema en el navegador

Con el servidor corriendo, abre el navegador y escribe la siguiente dirección:

```
http://localhost/prestamos-equipos/
```

Si cambiaste el puerto de Apache a 8080 (ver Paso 1.2), usa:

```
http://localhost:8080/prestamos-equipos/
```

### Paso 6.3 — Iniciar sesión con el administrador

Deberías ver el formulario de login. Usa las siguientes credenciales del administrador creado por el `seed.sql`:

| Campo | Valor |
|-------|-------|
| Correo electrónico | admin@universidad.edu |
| Contraseña | Admin123 |

>  Si puedes ver el dashboard del administrador, el sistema está completamente instalado y funcionando.

---

## 7. Usuarios y roles de prueba

El sistema tiene tres roles con distintos permisos. Puede crear usuarios adicionales desde el panel de administración o desde el formulario de registro.

| Rol | Correo | Contraseña | Acceso principal |
|-----|--------|------------|-----------------|
| Administrador | admin@universidad.edu | Admin123 | Acceso total |
| Docente | Crear desde Registro | La que elija | Solicitar préstamos |
| Estudiante | Crear desde Registro | La que elija | Solicitar préstamos |

### Permisos del rol Administrador

- Gestionar equipos y categorías (crear, editar, dar de baja)
- Aprobar o rechazar solicitudes de préstamo
- Registrar devoluciones y evaluar el estado del equipo
- Ver, crear y levantar sanciones
- Enviar equipos a mantenimiento
- Ver el dashboard con estadísticas y reportes

### Permisos de los roles Docente y Estudiante

- Ver catálogo de equipos disponibles
- Solicitar el préstamo de un equipo disponible
- Ver el estado de sus propias solicitudes
- Consultar su historial de préstamos en su perfil

---

## 8. Resumen rápido

Si ya tiene XAMPP instalado y solo necesita recordar los pasos esenciales:

```bash
# 1. Iniciar Apache y MySQL en XAMPP Control Panel

# 2. Navegar a htdocs y clonar el repositorio
cd C:\xampp\htdocs
git clone https://github.com/DGPYParzival/prestamos-equipos.git

# 3. Entrar al proyecto y crear el .env
cd prestamos-equipos
copy .env.example .env

# 4. Importar la base de datos en phpMyAdmin
#    → http://localhost/phpmyadmin
#    → Ejecutar schema.sql y luego seed.sql

# 5. Abrir el sistema en el navegador
#    → http://localhost/prestamos-equipos/

# 6. Iniciar sesión
#    → admin@universidad.edu / Admin123
```

---

*Construcción de Software · Grupo D · Mayo 2026*
