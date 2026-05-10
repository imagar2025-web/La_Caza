# 🐺 La Caza Plus — Guía de instalación

Aplicación web para gestión de hábitos en grupo, construida con **Laravel 11** (backend) y **Vue 3 + Vite** (frontend), conectada a **MySQL**.

---

## 📋 Tabla de contenidos

1. [Requisitos previos](#-requisitos-previos)
2. [Clonar el proyecto](#-1-clonar-el-proyecto)
3. [Configurar MySQL](#-2-configurar-mysql-phpmyadmin)
4. [Instalar el backend](#-3-backend-laravel)
5. [Instalar el frontend](#-4-frontend-vue--vite)
6. [Probar el proyecto](#-5-probar-que-funciona)
7. [Problemas frecuentes](#-problemas-frecuentes)
8. [Comandos útiles](#-comandos-útiles)

---

## 🛠️ Requisitos previos

Antes de empezar, ten instalado:

| Herramienta | Versión mínima | Descarga |
|---|---|---|
| **PHP** | 8.2+ | Incluido en XAMPP/Laragon |
| **Composer** | 2.x | https://getcomposer.org/download/ |
| **Node.js** | 18+ | https://nodejs.org/ |
| **XAMPP** o **Laragon** | — | https://www.apachefriends.org/ |
| **Git** | — | https://git-scm.com/ |

Verifica que todo está OK en PowerShell:

```powershell
php -v
composer -V
node -v
npm -v
```

---

## 🟦 1. Clonar el proyecto

```powershell
cd C:\Users\TuUsuario\Downloads
git clone <URL_DEL_REPO> lacaza-proyecto
cd lacaza-proyecto
```

Estructura esperada:

```
lacaza-proyecto/
├── backend/      ← Laravel 11
└── frontend/     ← Vue 3 + Vite
```

---

## 🟦 2. Configurar MySQL (phpMyAdmin)

### 2.1 Arrancar XAMPP
1. Abre **XAMPP Control Panel**
2. **Start** en **Apache** y **MySQL**
3. Apunta el puerto de MySQL (por defecto `3306`, a veces aparece `3307` si hay conflicto con otro MySQL instalado)

### 2.2 Crear la base de datos
1. Ve a `http://localhost/phpmyadmin`
2. Click en **Nueva** (panel izquierdo)
3. **Nombre BD:** `lacaza`
4. **Cotejamiento:** `utf8mb4_unicode_ci`
5. Click **Crear**

> ⚠️ **Verifica el puerto** con este comando antes de continuar:
> ```powershell
> Test-NetConnection -ComputerName 127.0.0.1 -Port 3306
> ```
> Debe salir `TcpTestSucceeded : True`. Si no, prueba con `3307`.

---

## 🟦 3. Backend (Laravel)

### 3.1 Entrar a la carpeta del backend

```powershell
cd backend
```

### 3.2 Instalar dependencias

```powershell
composer install
```

### 3.3 Crear el archivo `.env`

Crea un archivo `.env` en `backend/` con este contenido:

```dotenv
APP_NAME=LaCazaPlus
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lacaza
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

VITE_APP_NAME="${APP_NAME}"

FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

### 3.4 Ajustes según tu equipo

Cambia estas variables del `.env` **solo si tu MySQL tiene una configuración distinta**:

| Variable | Cuándo cambiarla |
|---|---|
| `DB_PORT=3306` | Si tu MySQL está en otro puerto (ej. `3307`) |
| `DB_USERNAME=root` | Si usas otro usuario de MySQL |
| `DB_PASSWORD=` | Si tu MySQL tiene contraseña, ponla aquí |

### 3.5 Generar la clave de la aplicación

```powershell
php artisan key:generate
```

Esto rellena automáticamente el `APP_KEY=` del `.env`.

### 3.6 Migrar y sembrar la BD

```powershell
php artisan migrate:fresh --seed
```

Salida esperada:

```
✓ Datos de prueba creados.
  Login de prueba: diego@lacaza.test / password123
  Código de la sala demo: XXXXXXXX
```

### 3.7 Levantar el servidor

```powershell
php artisan serve
```

→ Backend corriendo en **http://127.0.0.1:8000**

> 🚨 **NO cierres esta terminal** mientras estés trabajando.

---

## 🟦 4. Frontend (Vue + Vite)

### 4.1 Abre **OTRA terminal** de PowerShell

```powershell
cd C:\Users\TuUsuario\Downloads\lacaza-proyecto\frontend
```

### 4.2 Instalar dependencias

```powershell
npm install
```

### 4.3 Crear el `.env` del frontend

```powershell
"VITE_API_URL=http://127.0.0.1:8000/api" | Out-File -Encoding utf8 .env
```

### 4.4 Levantar el servidor de desarrollo

```powershell
npm run dev
```

→ Frontend corriendo en **http://localhost:5173**

> 🚨 **NO cierres esta terminal** mientras estés trabajando.

---

## 🟦 5. Probar que funciona

1. Abre el navegador en `http://localhost:5173`
2. Inicia sesión con las credenciales de prueba:
   - **Email:** `diego@lacaza.test`
   - **Contraseña:** `password123`

Si entra al dashboard → **¡todo OK!** 🎉

---

## ⚠️ Problemas frecuentes

### 🔴 `SQLSTATE[HY000] [2002] No se puede establecer una conexión`
- **Causa:** MySQL no está arrancado, o el puerto del `.env` no coincide.
- **Solución:**
  1. Verifica que MySQL está en verde en XAMPP.
  2. Comprueba el puerto: `Test-NetConnection -ComputerName 127.0.0.1 -Port 3306`
  3. Si tu MySQL está en `3307`, cambia `DB_PORT=3307` en el `.env`.

### 🔴 `Unknown database 'lacaza'`
- **Causa:** Falta crear la base de datos.
- **Solución:** Ve a phpMyAdmin y crea la BD `lacaza` (paso 2.2).

### 🔴 `Class "App\Http\Controllers\Controller" not found`
- **Causa:** Laravel 11 no incluye la clase base `Controller` por defecto.
- **Solución:** Crea el archivo `backend/app/Http/Controllers/Controller.php` con:
  ```php
  <?php

  namespace App\Http\Controllers;

  abstract class Controller
  {
      //
  }
  ```

### 🔴 `php artisan route:list` no muestra las rutas `api/*`
- **Causa:** En Laravel 11 hay que registrar manualmente las rutas API.
- **Solución:** Abre `backend/bootstrap/app.php` y añade la línea `api:` dentro de `withRouting`:

  ```php
  ->withRouting(
      web: __DIR__.'/../routes/web.php',
      api: __DIR__.'/../routes/api.php',          // ← ESTA LÍNEA
      commands: __DIR__.'/../routes/console.php',
      health: '/up',
  )
  ```

  Después limpia caché:
  ```powershell
  php artisan route:clear
  php artisan config:clear
  ```

### 🔴 Login devuelve `500 (Internal Server Error)`
- **Causa:** Falta la tabla `personal_access_tokens` de Sanctum.
- **Solución:**
  ```powershell
  php artisan migrate:fresh --seed
  ```

### 🔴 Después de cambiar el `.env` no se aplican los cambios
- **Solución:** Ejecuta siempre tras tocar el `.env`:
  ```powershell
  php artisan config:clear
  ```

### 🔴 El frontend no conecta con el backend (CORS, 404)
- **Causa:** El `.env` del frontend apunta a una URL distinta o falta reiniciar Vite.
- **Solución:**
  1. Verifica que `frontend/.env` contiene `VITE_API_URL=http://127.0.0.1:8000/api`
  2. Reinicia `npm run dev` (Ctrl+C y volver a lanzar). Vite NO recarga las variables de entorno automáticamente.

---

## 🧰 Comandos útiles

### Backend

```powershell
# Limpiar todas las cachés
php artisan optimize:clear

# Ver todas las rutas registradas
php artisan route:list

# Ver el log más reciente
Get-Content storage\logs\laravel.log -Tail 50

# Resetear la BD desde cero
php artisan migrate:fresh --seed

# Comprobar el estado de las migraciones
php artisan migrate:status
```

### Frontend

```powershell
# Compilar para producción
npm run build

# Reinstalar dependencias desde cero
Remove-Item -Recurse -Force node_modules, package-lock.json
npm install
```

---

## 📌 Resumen — Lo que debe quedar corriendo

| Terminal | Carpeta | Comando | URL |
|---|---|---|---|
| 1 | `backend` | `php artisan serve` | http://127.0.0.1:8000 |
| 2 | `frontend` | `npm run dev` | http://localhost:5173 |

---

## 🔑 Credenciales de prueba

| Campo | Valor |
|---|---|
| Email | `diego@lacaza.test` |
| Contraseña | `password123` |
| Sala demo | Código mostrado en consola tras `db:seed` |

---

## 🤝 Equipo

- **Diego Delgado Fernández** — Frontend
- **Iván Martín** — Backend
- **Víctor Villalta** — Backend

---

> Última actualización: mayo 2026
