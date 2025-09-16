# 📦 Inventory-App – Sistema de Gestión de Inventario

**Inventory-App** es una solución poderosa e intuitiva para la gestión de inventarios construida con **Laravel** y **FilamentPHP**, diseñada para optimizar tu stock, órdenes, proveedores y roles de usuario, todo en un solo lugar. Ya sea que administres un almacén, una pequeña empresa o una operación a gran escala, Inventory-App proporciona todas las herramientas que necesitas para mantener tu inventario bajo control.

**🌎 Sistema completamente traducido al español con moneda colombiana (COP)**

## 🚀 Características Principales

### 📦 Gestión de Inventario
- **Categorías de Productos** – Crear, actualizar, eliminar (soft-delete) y gestionar categorías de productos.
- **Proveedores** – Mantener registros de proveedores con capacidades CRUD completas.
- **Productos** – Rastrear productos con detalles clave como cantidad, proveedor, precios y categoría.

### 📑 Gestión de Órdenes
- **Manejo de Órdenes** – Crear y gestionar órdenes con validación automática de stock y actualizaciones dinámicas de inventario.
- **Validación de Stock** – Garantizar disponibilidad de productos durante el procesamiento de órdenes.
- **Alertas de Stock Bajo** – Notificaciones por email cuando el stock cae por debajo de un umbral establecido.

### 👥 Gestión de Usuarios y Roles
- **Administración de Usuarios** – Crear, editar y gestionar usuarios con asignaciones de roles.
- **Control de Acceso Basado en Roles (RBAC)** – Manejo fino de permisos con **Filament Shield**.

### 🔔 Notificaciones
- **Emails de Stock Bajo** – Alertas automáticas para notificar a administradores cuando el stock está bajo.

### 🏷️ Gestión Inteligente de URLs (Slugs)
- **Slugs Únicos Automáticos** – Generación automática de URLs únicas para productos y categorías.
- **Resolución de Conflictos** – Sistema inteligente que agrega numeración automática cuando hay nombres similares.
- **Regeneración Masiva** – Comando Artisan para regenerar todos los slugs existentes.
- **Validación de Formato** – Asegurar que los slugs cumplan con estándares web (solo letras, números y guiones).

### 📊 Panel de Control y Analíticas
- **Gráficos Interactivos** – Visualizar tendencias de ventas e inventario.
- **Estadísticas Rápidas** – Vista general de total de usuarios, productos, órdenes y alertas.

### 🧭 Búsqueda Global
- **Búsqueda Inteligente** – Buscar productos, órdenes y proveedores con detalles de resultados enriquecidos.
- **Navegación Rápida** – Saltar directamente a la página del elemento desde los resultados de búsqueda.

### 🔍 Filtros y Pestañas
- **Filtros de Órdenes** – Filtrar por marcos de tiempo personalizados como hoy, esta semana o este año.
- **Pestañas de Proveedores** – Organizar proveedores por categorías de productos.

### 🔐 Autenticación y Seguridad
- **Inicio de Sesión Seguro** – Sistema de autenticación completo con verificación de email.
- **Sistema de Permisos** – Restringir acceso por roles de usuario para mayor seguridad.

### 🔐 Gestión de Roles y Seguridad
- **Roles Granulares** – Sistema de roles con permisos específicos por funcionalidad.
- **Control de Acceso** – Restricción de acceso a módulos sensibles (usuarios, roles).
- **Rol de Operador** – Acceso limitado solo a gestión de inventario (productos, órdenes, proveedores).
- **Super Administrador** – Acceso completo a todas las funcionalidades del sistema.
- **Políticas de Seguridad** – Validación automática de permisos en cada acción.

## ⚙️ Guía de Instalación

### 📋 Prerequisitos

- **PHP** ≥ 8.2  
- **Composer** ≥ 2.3  
- **Node.js** ≥ 18.8  
- **MySQL** - Base de datos  

### 📥 Pasos de Instalación

#### 1. Clonar el Repositorio
```bash
git clone https://github.com/soporte-tesltda/inventario.git
```

#### 2. Navegar al Proyecto
```bash
cd inventario
```

#### 3. Instalar Dependencias
```bash
composer install
npm install
```

### 🔧 Configuración

#### 1. Configurar `.env`
Renombra `.env.example` a `.env` y ajusta las variables de entorno:
```dotenv
APP_NAME=Inventory-App
APP_ENV=local
APP_URL=http://127.0.0.1:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
DB_DATABASE=Inventory-App
DB_USERNAME=root
DB_PASSWORD=
MAIL_HOST=localhost
MAIL_PORT=1025
```

### 🗃️ Configuración de Base de Datos

#### 1. Ejecutar Migraciones
```bash
php artisan key:generate
php artisan migrate
```

#### 2. Configurar Roles y Permisos
```bash
php artisan shield:install
php artisan shield:generate --all
php artisan shield:super-admin --user=1
```

### 🖇️ Enlace de Almacenamiento
```bash
php artisan storage:link
```

#### 🔧 Solución de Problemas de Imágenes en Windows

Si las imágenes no se muestran correctamente:

```bash
# Reparar enlace simbólico en Windows (ejecutar como administrador)
php artisan storage:fix-link
```

### 🌱 Sembrar Datos de Prueba
```bash
php artisan db:seed
```

### 🔧 Comandos Útiles

```bash
# Crear super administrador
php artisan shield:super-admin --user=1

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Optimizar aplicación
php artisan optimize
```

### 🚀 Ejecutar la Aplicación

#### Iniciar Servidor Laravel:
```bash
php artisan serve
```

#### Iniciar Frontend (Servidor de Desarrollo Vite):
```bash
npm run dev
```

Accede a la aplicación en [http://127.0.0.1:8000](http://127.0.0.1:8000)

**Credenciales de prueba:**
- **Super Administrador:**
  - Email: admin@tes.com
  - Contraseña: password
  - Acceso: Completo (todos los módulos)

- **Operador:**
  - Email: operador@tes.com  
  - Contraseña: password
  - Acceso: Solo inventario (productos, órdenes, proveedores)

## 🌟 Características del Sistema en Español

- ✅ **Interfaz Completamente Traducida** – Todos los menús, formularios y mensajes en español
- ✅ **Moneda Colombiana (COP)** – Precios y totales mostrados en pesos colombianos
- ✅ **Panel de Control** – Dashboard completamente localizado
- ✅ **Gestión de Inventario** – Productos, categorías y proveedores en español
- ✅ **Sistema de Órdenes** – Formularios y tablas traducidos
- ✅ **Notificaciones** – Alertas y mensajes del sistema en español
- ✅ **Roles y Permisos** – Sistema de autenticación localizado

## 🛠️ Tecnologías Utilizadas

- **Laravel** – Framework PHP moderno
- **FilamentPHP** – Panel de administración elegante
- **Tailwind CSS** – Estilos utilitarios
- **Livewire** – Componentes dinámicos
- **MySQL** – Base de datos relacional
- **Vite** – Bundler de assets moderno

## 📄 Licencia

Este proyecto es privado.



## 📞 Soporte

Para soporte o preguntas, contacta a:
- **Email:** gerencia0tesltda@gmail.com
- **GitHub:** [@soporte-tesltda](https://github.com/soporte-tesltda)

---

**Desarrollado con ❤️ para la gestión eficiente de inventarios en Colombia**

## 📝 Historial de Cambios

### Versión 2.1.1 - Septiembre 2025
- ✅ **Sistema de Roles Granular** con políticas de seguridad
- ✅ **Rol de Operador** con acceso limitado a inventario  
- ✅ **Sistema de Slugs Únicos** para productos y categorías
- ✅ **Optimizaciones de rendimiento** y formularios
- ✅ **Interfaz completamente en español** con moneda colombiana



## 🚀 Despliegue a Producción

### 📋 Preparación para Producción

Para desplegar el sistema en un servidor de producción, sigue estos pasos críticos:

#### 1. Configuración de Entorno
```bash
# Copiar configuración de producción
cp .env.production.example .env

# Editar variables críticas:
# - APP_ENV=production
# - APP_DEBUG=false  
# - APP_URL=https://tu-dominio.com
# - Configuración de base de datos real
```

#### 2. Script de Despliegue Automatizado
```bash
# Ejecutar script completo de despliegue
chmod +x deploy-production.sh
./deploy-production.sh
```

#### 3. Configurar Storage
```bash
# Crear enlace simbólico para imágenes
php artisan storage:link
```

### 🖼️ Configuración de Imágenes

**Pasos críticos:**
1. ✅ Configurar `APP_URL` con el dominio real
2. ✅ Ejecutar `php artisan storage:link` en el servidor
3. ✅ Configurar permisos correctos en `/storage/` y `/public/storage/`

### 🔧 Optimizaciones de Producción
```bash
# Instalar dependencias optimizadas
composer install --no-dev --optimize-autoloader

# Optimizar aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Ejecutar migraciones
php artisan migrate --force
```

### 📊 Verificación Post-Despliegue
```bash
# Monitorear logs
tail -f storage/logs/laravel.log
```

### 🆘 Solución de Problemas Comunes

| Problema | Solución |
|----------|----------|
| Imágenes no se ven | `php artisan storage:link` |
| Error 500 | Verificar permisos en `storage/` |
| URLs incorrectas | Actualizar `APP_URL` en `.env` |
