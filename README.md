<div align="center">
  <br />
  <a href="https://i.postimg.cc/QNwPCXzy/starcheck.png" target="_blank">
    <img src="https://i.postimg.cc/QNwPCXzy/starcheck.png" alt="Portal de Compras Starsoft Banner" width="600"/>
  </a>
  <br />
  <div>
    <img src="https://img.shields.io/badge/-Vue.js-black?style=for-the-badge&logo=vue.js&logoColor=4FC08D" alt="Vue.js" />
    <img src="https://img.shields.io/badge/-Vite-black?style=for-the-badge&logo=vite&logoColor=white&color=646CFF" alt="Vite" />
    <img src="https://img.shields.io/badge/-Blade-black?style=for-the-badge&logo=laravel&logoColor=white&color=FF2D20" alt="Blade" />
    <img src="https://img.shields.io/badge/-SQL_Server-black?style=for-the-badge&logo=microsoft-sql-server&logoColor=CC2927" alt="SQL Server" />
  </div>
  <h2 align="center">Portal de Compras — Starsoft API 📦</h2>
  <p align="center">
    Portal web <b>multi-empresa</b> integrado con las bases de datos SQL Server del ERP Starsoft<br>
    Gestión de Órdenes de Compra (OC/OS) con flujo de aprobación, auditoría y reportes complementarios.<br>
    Conexión dinámica a múltiples empresas diferenciadas por ID de base de datos.
  </p>
</div>

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Tecnologías](#tecnologías)
3. [Características](#características)
4. [Instalación Rápida](#instalación-rápida)
5. [Uso](#uso)
6. [Contribuciones](#contribuciones)
7. [Licencia](#licencia)

---

## <a name="introducción">🤖 Introducción</a>

Este proyecto es un portal web completo **multi-empresa** para la gestión de Órdenes de Compra (OC) y Órdenes de Servicio (OS). El sistema se conecta dinámicamente a múltiples bases de datos SQL Server, cada una correspondiente a una empresa diferente del ERP Starsoft, diferenciadas únicamente por su ID.

El portal permite un control integral del flujo de aprobación, auditoría de procesos y generación de reportes complementarios, adaptándose automáticamente al contexto de cada empresa según el usuario que accede.

---

## <a name="tecnologías">⚙️ Tecnologías</a>

- **Vue 3** (framework frontend)
- **Vite** (herramienta de construcción)
- **PHP** (backend/API)
- **SQL Server** (base de datos ERP Starsoft)
- **Composer** (gestión de dependencias PHP)
- **pnpm** (gestor de paquetes frontend)

---

## <a name="características">🔋 Características</a>

- 🏢 **Multi-empresa**: Conexión dinámica a múltiples bases de datos por ID de empresa.
- 🔄 **Integración ERP**: Conexión directa con SQL Server de Starsoft (bases generadas por el ERP).
- ✅ **Flujo de aprobación**: Sistema de aprobaciones multinivel para OC/OS.
- 📊 **Reportes y auditoría**: Seguimiento completo de operaciones por empresa.
- 🎨 **Interfaz moderna**: Desarrollada con Vue 3 para una experiencia fluida.
- 🔐 **Gestión de permisos**: Control de acceso según roles y empresas.
- 🔀 **Cambio de contexto**: Usuarios pueden gestionar múltiples empresas desde un solo portal.
- ⚡ **Rendimiento optimizado**: Vite para desarrollo rápido y builds optimizados.

---

## <a name="instalación-rápida">🤸 Instalación Rápida</a>

**Requisitos**

- [Git](https://git-scm.com/)
- [Node.js & pnpm](https://pnpm.io/)
- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- SQL Server

**Instalación**

```bash
git clone https://github.com/EmersonValenzuela/portal-compras-starsoft.git
cd portal-compras-starsoft

# Frontend
pnpm install
pnpm run dev

# Backend
composer install
# Configurar .env con la conexión a SQL Server
# Ejecutar scripts en /sql para entorno de desarrollo
# Iniciar el servidor según la estructura del backend
```

Abre [http://localhost:5173](http://localhost:5173) en tu navegador.

---

## <a name="uso">🕸️ Uso</a>

1. Configura las conexiones a SQL Server en el archivo `.env` (múltiples empresas)
2. El sistema identificará automáticamente las bases de datos del ERP Starsoft por su ID
3. Ejecuta los scripts de base de datos necesarios desde `/sql`
4. Inicia el backend PHP
5. Inicia el frontend con `pnpm run dev`
6. Accede al portal y selecciona la empresa con la que deseas trabajar
7. Gestiona órdenes de compra según los permisos asignados para cada empresa
8. Utiliza el flujo de aprobación correspondiente a cada contexto empresarial

---

## <a name="contribuciones">🤝 Contribuciones</a>

No se aceptan contribuciones externas en este momento. Consulta `CONTRIBUTING.md` para más información sobre la política del proyecto.

---

## <a name="licencia">📝 Licencia</a>

MIT (Agregar la licencia correspondiente al repositorio)

---

> Desarrollado e implementado por [EmersonValenzuela](https://github.com/EmersonValenzuela)
