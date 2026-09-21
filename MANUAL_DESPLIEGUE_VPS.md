# 🚀 Manual de Actualización: De Local al VPS

Este manual resume los pasos exactos que debes seguir siempre que hagas cambios en tu ordenador (Local) y quieras que aparezcan en tu servidor en internet (VPS).

---

## 💻 PARTE 1: En tu ordenador (Windows / Local)
Cada vez que programes algo nuevo, guardes archivos y quieras subir la actualización, abre la consola en la carpeta de tu proyecto (`C:\xampp\htdocs\grintranetedu`) y ejecuta estos 3 pasos:

1. **Prepara todos los archivos modificados:**
   ```bash
   git add .
   ```

2. **Guarda los cambios con un mensaje descriptivo:**
   ```bash
   git commit -m "Describe aquí qué has cambiado, ej: Añadido gestor de alumnos"
   ```

3. **Sube los cambios a GitHub:**
   ```bash
   git push origin main
   ```
*(Con esto tu código ya está seguro en la nube de GitHub).*

---

## 🌍 PARTE 2: En tu servidor (VPS Debian)
Ahora toca decirle al servidor que se descargue esa actualización.

1. **Conéctate a tu servidor:**
   ```bash
   ssh root@IP_DE_TU_VPS
   ```

2. **Entra a la carpeta de tu proyecto:**
   ```bash
   cd /var/www/laravel
   ```

3. **Descarga la actualización:**
   ```bash
   git pull origin main
   ```

### 🛠️ Comandos de Mantenimiento (¡Muy importantes!)
Dependiendo de lo que hayas cambiado en local, puede que necesites ejecutar algunos de estos comandos en el VPS después del `git pull`:

* **Si has creado tablas nuevas o modificado la base de datos (Migraciones):**
  ```bash
  php artisan migrate --force
  ```
* **Si has añadido permisos nuevos o roles:**
  ```bash
  php artisan db:seed --class=PermissionSeeder
  php artisan permission:cache-reset
  ```
* **Si has instalado paquetes nuevos (librerías con Composer):**
  ```bash
  composer install --optimize-autoloader --no-dev
  ```
* **Comando comodín (Limpia la caché general para evitar errores):**
  ```bash
  php artisan optimize:clear
  ```

> 💡 **Consejo:** Si haces cambios simples (como cambiar un texto en HTML o CSS), con hacer el **`git pull origin main`** será suficiente para que la web se actualice al instante. No hace falta ejecutar nada más.
