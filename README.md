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
... (238 líneas restantes)
