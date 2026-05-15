# Manual StarCheck

## 1. Objetivo

StarCheck es una aplicación para:

- gestionar el ingreso de usuarios al sistema,
- consultar órdenes de compra y órdenes de servicio,
- realizar preaprobaciones por área,
- realizar aprobaciones finales por gerencia,
- administrar usuarios, empresas y permisos,
- consultar proveedores, productos y reportes.

Este manual incluye una guía de uso para usuario final y una referencia técnica breve para soporte y mantenimiento.

## 2. Módulos Principales

Según el rol del usuario, el menú puede mostrar:

- `Dashboard`
- `Ordenes de Compra`
- `Proveedores`
- `Productos`
- `Reportes`
- `Usuarios`

## 3. Roles y Comportamiento

### 3.1 Administrador

- Puede ingresar a varios módulos administrativos.
- Puede aprobar órdenes si llegan al estado `PREAPROBADA`.
- No usa permisos por área.

### 3.2 Gerente

- Trabaja por empresa.
- Puede aprobar o rechazar órdenes en estado `PREAPROBADA`.
- Puede marcar órdenes como leídas.
- Puede tener un correo aprobador distinto por empresa.
- Puede tener también un ID de usuario por empresa.

### 3.3 Jefe de Área

- Trabaja por empresa y por áreas permitidas.
- Solo puede ver órdenes de sus áreas autorizadas.
- Puede realizar `Dar Visto Bueno` sobre órdenes `EMITIDA`.
- No puede marcar órdenes como leídas.

### 3.4 Jefe de Compras / Sistemas

- Tienen acceso a módulos administrativos según configuración del sistema.
- El comportamiento específico depende del flujo asignado por la empresa.

## 4. Inicio de Sesión

### 4.1 Ingreso

1. Abrir la pantalla de login.
2. Ingresar correo y contraseña.
3. Presionar `Ingresar`.

### 4.2 Datos de sesión cargados al iniciar

Al iniciar sesión, el sistema carga en la sesión del usuario:

- `id`
- `name`
- `email`
- `cargo`
- `company_ids`
- `company_default`
- `area_permissions`

Esto es importante porque los permisos visibles del frontend dependen de esos datos.

### 4.3 Tiempo de Inactividad

El sistema muestra un diálogo de advertencia antes de cerrar la sesión por inactividad.

Opciones del diálogo:

- `Continuar sesión`
- `Desconectar`

Si el usuario no responde a tiempo, la sesión se cierra automáticamente.

## 5. Flujo de Órdenes

### 5.1 Tipos de documentos

El sistema trabaja con:

- `OC`: Orden de Compra
- `OS`: Orden de Servicio

### 5.2 Estados principales

- `EMITIDA`
- `PREAPROBADA`
- `APROBADA`
- `RECHAZADO`

### 5.3 Reglas del flujo

1. Una orden nueva normalmente llega como `EMITIDA`.
2. El `JEFE DE AREA` puede hacer `Dar Visto Bueno`.
3. Cuando el visto bueno se registra correctamente, el portal muestra la orden como `PREAPROBADA`.
4. El `GERENTE` puede `Aprobar` o `Rechazar`.
5. La aprobación final actualiza el estado interno y también el estado ERP.

## 6. Uso del Módulo Ordenes de Compra

### 6.1 Filtros disponibles

En la pantalla de órdenes se puede filtrar por:

- empresa,
- estado,
- rango de fechas,
- área,
- búsqueda por texto,
- mostrar todos.

### 6.2 Regla del filtro de área

- El filtro de área solo aparece para `JEFE DE AREA`.
- Si el usuario no tiene áreas asignadas para una empresa, no verá órdenes de esa empresa por área.

### 6.3 Lectura de órdenes

- `GERENTE` y roles gerenciales equivalentes pueden marcar órdenes como leídas.
- `JEFE DE AREA` no cambia el estado `leido`.

### 6.4 Vista de detalle

Al abrir una orden, el usuario puede ver:

- proveedor,
- fechas,
- moneda,
- forma de pago,
- solicitante,
- responsable,
- observación,
- productos,
- importes,
- estado del flujo.

## 7. Acciones por Rol en la Orden

### 7.1 Jefe de Área

Si la orden está `EMITIDA`, verá:

- `Dar Visto Bueno`

Al ejecutar esta acción:

- se registra la preaprobación por área,
- la orden queda disponible para aprobación gerencial.

### 7.2 Gerencia

Si la orden está `PREAPROBADA`, verá:

- `Aprobar`
- `Rechazar`

Al aprobar:

- se cambia el estado a `APROBADA`,
- se guarda `usuarioAprobacion`,
- se registra la fecha de aprobación,
- se actualiza el estado ERP.

Al rechazar:

- se cambia el estado a `RECHAZADO`,
- se registra el usuario que rechazó,
- se actualiza el estado ERP.

## 8. Administración de Usuarios

El módulo `Usuarios` permite:

- crear usuarios,
- editar usuarios,
- asignar empresas,
- asignar áreas por empresa para jefes de área,
- asignar datos por empresa para cada usuario.

### 8.1 Crear Usuario

Campos principales:

- nombres y apellidos,
- cargo,
- correo,
- contraseña,
- empresas,
- estado.

### 8.2 Editar Usuario

Al editar un usuario se pueden actualizar:

- nombre,
- cargo,
- empresas,
- estado,
- permisos por área,
- contraseña si se desea cambiar.

### 8.3 Jefe de Área: permisos por empresa

Si el cargo es `JEFE DE AREA`, aparece la sección:

- `Permisos de Áreas por Empresa`

Ahí se seleccionan las áreas permitidas por cada empresa asignada.

Reglas:

- solo aplica a `JEFE DE AREA`,
- se guarda por empresa,
- al volver a editar el usuario, las áreas ya guardadas deben mostrarse seleccionadas.

### 8.4 Asignación de datos por empresa

En la grilla de usuarios, cada fila tiene un ícono de empresa.

Ese botón abre un diálogo de asignación por empresa.

#### Para usuarios no gerenciales

Se puede asignar:

- `ID de usuario`

Este valor se usa como referencia del usuario en esa empresa.

#### Para usuarios gerenciales

Se puede asignar:

- `ID de usuario`
- `Correo aprobador`

El correo aprobador por empresa se usa cuando gerencia aprueba una orden y debe llenarse el campo `usuarioAprobacion` con el correo correspondiente a esa empresa.

## 9. Reglas de Negocio Importantes

### 9.1 Gerencia y áreas

La gerencia:

- no usa `area_permissions`,
- trabaja por empresa,
- puede ver todas las áreas de la empresa que tiene asignada.

### 9.2 Jefes de área

Los jefes de área:

- sí usan `area_permissions`,
- solo ven órdenes de sus áreas autorizadas,
- no pueden marcar órdenes como leídas.

### 9.3 Preaprobación

La preaprobación se guarda en una tabla separada y el portal interpreta esa marca para mostrar el estado `PREAPROBADA`, aunque la tabla principal de aprobaciones siga en `EMITIDA`.

### 9.4 Correo aprobador por empresa

Cuando un gerente aprueba:

- el sistema primero busca si existe un `correo aprobador` asignado en la empresa,
- si existe, ese valor se guarda en `usuarioAprobacion`,
- si no existe, el sistema usa un valor de respaldo del usuario.

## 10. Solución de Problemas

### 10.1 Un jefe de área no ve órdenes

Revisar:

- que tenga empresas asignadas,
- que tenga áreas asignadas en esas empresas,
- que el login haya cargado correctamente `area_permissions`.

### 10.2 La orden no muestra `Aprobar`

Revisar:

- que la orden esté `PREAPROBADA`,
- que exista el registro de preaprobación,
- que el usuario tenga rol gerencial,
- que el listado esté devolviendo correctamente el estado transformado.

### 10.3 Las áreas no salen seleccionadas al editar

Revisar:

- que `area_permissions` esté guardado como objeto por empresa,
- que el recurso del usuario preserve la clave de empresa,
- que el selector cargue las áreas de esa empresa.

### 10.4 El diálogo de asignación muestra datos del usuario anterior

El diálogo debe limpiarse al:

- cerrar el modal,
- cambiar de usuario,
- cambiar de empresa dentro del mismo flujo.

## 11. Referencia Técnica

### 11.1 Endpoints principales

Autenticación:

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/keep-alive`

Órdenes:

- `GET /api/purchase-orders`
- `POST /api/details-order`
- `POST /api/mark-as-read`
- `POST /api/handle-approval`

Usuarios:

- `GET /api/users/list`
- `POST /api/users/add`
- `PUT /api/users/update/{id}`
- `GET /api/users/companyUser/{userId}/{companyId}`
- `POST /api/users/addCompanyUser`

Catálogos:

- `GET /api/companies`
- `GET /api/areas`
- `GET /api/suppliers`
- `GET /api/products`

### 11.2 Tablas relevantes

Base de autenticación:

- `users`
- `company_user`
- `order_area_pre_approvals`

Tabla de aprobaciones:

- `listadoaprobaciones` o tabla equivalente modelada por `Orders`

### 11.3 Campos importantes

En `users`:

- `company_ids`
- `company_default`
- `area_permissions`
- `cargo`
- `email`

En `company_user`:

- `user_id`
- `company_id`
- `user_code`
- `approval_email`

En `order_area_pre_approvals`:

- `company_code`
- `order_type`
- `order_code`
- `area_manager_user_id`
- `area_manager_name`
- `area_manager_approved_at`

En `Orders` o equivalente:

- `estado`
- `leido`
- `usuarioAprobacion`
- `fechaAprobacion`

## 12. Archivos Clave del Proyecto

Backend:

- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/OrdersApi.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Resources/UserResource.php`
- `app/Models/User.php`
- `app/Models/CompanyUserPivot.php`
- `app/Models/OrderAreaPreApproval.php`

Frontend:

- `resources/js/pages/login.vue`
- `resources/js/pages/ocs.vue`
- `resources/js/components/dialogs/OCDetailDialog.vue`
- `resources/js/views/apps/user/list/AddNewUserDrawer.vue`
- `resources/js/views/apps/user/list/AssignDialog.vue`
- `resources/js/components/filters/UserAreaPermissions.vue`
- `resources/js/composables/useOrderFilters.js`
- `resources/js/composables/useAuth.js`
- `resources/js/composables/useIdleTimeout.js`

## 13. Recomendaciones Operativas

- Mantener actualizadas las empresas asignadas por usuario.
- Para `JEFE DE AREA`, revisar siempre áreas después de agregar o quitar empresas.
- Para `GERENTE`, registrar el correo aprobador por empresa antes de aprobar órdenes.
- Validar los datos por empresa cuando se migren usuarios nuevos.
- Probar los flujos críticos después de cada ajuste:
  - login,
  - preaprobación,
  - aprobación final,
  - edición de usuarios,
  - asignación por empresa,
  - sesión por inactividad.

