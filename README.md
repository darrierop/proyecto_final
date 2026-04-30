# 🎓 Sistema de Gestión Académica

Sistema web completo en PHP + MySQL con roles diferenciados:
**Administrador**, **Profesor** y **Alumno**.

---

## 📁 Estructura de carpetas

```
sistema_academico/
│
├── 📄 index.php               ← Redirección automática al panel o login
├── 📄 login.php               ← Página de inicio de sesión
├── 📄 panel.php               ← Panel principal (adaptado por rol)
├── 📄 mensajes.php            ← Sistema de mensajería interna
├── 📄 cerrar_sesion.php       ← Cierre de sesión
│
├── 📂 estilos/                ← ✨ Hojas de estilo CSS separadas
│   ├── global.css             ← Variables, sidebar, tablas, botones, formularios
│   └── login.css              ← Estilos exclusivos de la pantalla de login
│
├── 📂 incluye/                ← Archivos PHP reutilizables
│   ├── bd.php                 ← ⚙️ CONFIGURACIÓN DE BD (editar esto primero)
│   ├── autenticacion.php      ← Funciones de sesión y control de acceso
│   ├── cabecera.php           ← Sidebar + barra superior compartidos
│   └── pie.php                ← Cierre HTML + JavaScript
│
├── 📂 administrador/          ← Páginas exclusivas del rol Administrador
│   ├── usuarios.php           ← CRUD de usuarios
│   ├── cursos.php             ← CRUD de cursos
│   ├── asignaturas.php        ← CRUD de asignaturas
│   ├── aulas.php              ← CRUD de aulas
│   └── matriculas.php         ← Gestión de matrículas y notas
│
├── 📂 profesor/               ← Páginas exclusivas del rol Profesor
│   ├── mis_cursos.php         ← Vista y estadísticas de sus cursos
│   ├── calificaciones.php     ← Registro de notas y actividades ponderadas
│   └── horarios.php           ← Consulta de horarios por curso
│
└── 📂 alumno/                 ← Páginas exclusivas del rol Alumno
    ├── mis_cursos.php         ← Asignaturas matriculadas
    ├── calificaciones.php     ← Notas con barra de progreso ponderada
    └── horario.php            ← Horario semanal visual
```

---

## 🚀 Instalación

### 1. Requisitos
- PHP 7.4+ (recomendado PHP 8.x)
- MySQL 5.7+ / MariaDB
- Apache (XAMPP/WAMP/LAMP) o Nginx

### 2. Importar la base de datos
Importa el archivo `database.sql` en phpMyAdmin o tu cliente MySQL.

### 3. Configurar la conexión
Edita `incluye/bd.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Tu usuario MySQL
define('DB_PASS', '');          // Tu contraseña MySQL
define('DB_NAME', 'sistemaacademico');
```

### 4. Colocar los archivos
Copia la carpeta completa dentro de tu servidor:
- **XAMPP**: `C:/xampp/htdocs/sistema_academico/`
- **WAMP**:  `C:/wamp64/www/sistema_academico/`
- **Linux**: `/var/www/html/sistema_academico/`

### 5. Acceder
```
http://localhost/sistema_academico/
```

---

## 👥 Cuentas de acceso

| Usuario     | Contraseña   | Rol           |
|-------------|--------------|---------------|
| admin       | admin123     | Administrador |
| profesor1   | profesor123  | Profesor      |
| profesor2   | profesor123  | Profesor      |
| alumno1     | alumno123    | Alumno        |
| alumno2     | alumno123    | Alumno        |
| alumno3     | alumno123    | Alumno        |

---

## 🎨 CSS separado por responsabilidad

| Archivo              | Contenido                                              |
|----------------------|--------------------------------------------------------|
| `estilos/global.css` | Variables CSS, layout, sidebar, tablas, botones, formularios, alertas |
| `estilos/login.css`  | Animaciones, panel dividido y formulario de la pantalla de acceso |

Todos los archivos PHP cargan el CSS mediante `<link rel="stylesheet">` — **sin estilos embebidos**.

---

## 🔒 Seguridad
- Contraseñas con **bcrypt** (password_hash / password_verify)
- Sesiones con verificación de rol en cada página
- Prepared statements en todas las consultas SQL
- Sanitización con `htmlspecialchars()` en todas las salidas
