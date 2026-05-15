# Manual de Usuario StarCheck

## 1. ¿Qué es StarCheck?

StarCheck es una aplicación para revisar y gestionar:

- órdenes de compra,
- órdenes de servicio,
- aprobaciones por área,
- aprobaciones gerenciales,
- consulta de proveedores,
- consulta de productos,
- reportes,
- administración de usuarios, según el rol asignado.

## 2. Ingreso al Sistema

### 2.1 Iniciar sesión

1. Abra la pantalla de acceso.
2. Ingrese su correo.
3. Ingrese su contraseña.
4. Presione `Ingresar`.

Si los datos son correctos, el sistema mostrará el menú correspondiente a su perfil.

## 3. Menú Principal

Dependiendo del usuario, podrá ver uno o varios de estos módulos:

- `Dashboard`
- `Ordenes de Compra`
- `Proveedores`
- `Productos`
- `Reportes`
- `Usuarios`

## 4. Roles de Usuario

### 4.1 Jefe de Área

Puede:

- ver órdenes de sus empresas asignadas,
- ver solo las áreas autorizadas,
- filtrar por área,
- dar visto bueno a órdenes emitidas.

No puede:

- aprobar de forma final,
- rechazar de forma final,
- marcar órdenes como leídas.

### 4.2 Gerente

Puede:

- ver órdenes de sus empresas,
- aprobar órdenes preaprobadas,
- rechazar órdenes preaprobadas,
- marcar órdenes como leídas.

### 4.3 Administrador / Sistemas

Puede:

- ingresar a módulos administrativos,
- gestionar usuarios,
- revisar configuraciones y consultas del sistema, según permisos.

## 5. Módulo Ordenes de Compra

En este módulo podrá revisar órdenes de compra y órdenes de servicio.

### 5.1 Filtros disponibles

Puede filtrar por:

- empresa,
- estado,
- rango de fechas,
- área,
- búsqueda por texto.

### 5.2 Estados de las órdenes

Los estados visibles son:

- `EMITIDA`
- `PREAPROBADA`
- `APROBADA`
- `RECHAZADO`

## 6. Flujo de Aprobación

### 6.1 Jefe de Área

Cuando una orden está en estado `EMITIDA`, el jefe de área puede abrirla y usar:

- `Dar Visto Bueno`

Después de eso, la orden pasará al flujo de aprobación gerencial.

### 6.2 Gerencia

Cuando una orden está en estado `PREAPROBADA`, gerencia puede usar:

- `Aprobar`
- `Rechazar`

## 7. Cómo Revisar una Orden

1. Ingrese a `Ordenes de Compra`.
2. Seleccione los filtros que necesite.
3. Haga clic sobre la orden.
4. Revise la información del detalle.

En la ventana de detalle podrá ver:

- proveedor,
- fechas,
- moneda,
- forma de pago,
- solicitante,
- responsable,
- observación,
- productos,
- importes,
- estado de la orden.

## 8. Acciones en la Orden

### 8.1 Dar visto bueno

Disponible para `JEFE DE AREA` cuando la orden está `EMITIDA`.

### 8.2 Aprobar

Disponible para `GERENTE` cuando la orden está `PREAPROBADA`.

### 8.3 Rechazar

Disponible para `GERENTE` cuando la orden está `PREAPROBADA`.

### 8.4 Cancelar

Cierra la ventana sin realizar cambios.

## 9. Lectura de Órdenes

- Las órdenes pueden marcarse como leídas cuando las revisa gerencia.
- Los jefes de área no cambian el estado de lectura.

## 10. Módulo Usuarios

Este módulo normalmente está disponible para perfiles administrativos.

Aquí se puede:

- crear usuarios,
- editar usuarios,
- asignar empresas,
- asignar áreas por empresa,
- asignar datos por empresa.

## 11. Asignación de Áreas

Para usuarios con cargo `JEFE DE AREA`, se puede configurar:

- qué empresas tiene asignadas,
- qué áreas puede revisar dentro de cada empresa.

Cuando se edita el usuario, las áreas previamente guardadas deben aparecer seleccionadas.

## 12. Asignación por Empresa

En la lista de usuarios existe un botón con ícono de empresa.

Ese botón permite registrar información por empresa para cada usuario.

### 12.1 Para usuarios de gerencia

Se puede asignar:

- `ID de usuario`
- `Correo aprobador`

### 12.2 Para otros usuarios

Se puede asignar:

- `ID de usuario`

## 13. Sesión por Inactividad

Si el sistema detecta inactividad, mostrará una ventana de aviso.

Opciones:

- `Continuar sesión`
- `Desconectar`

Si no responde a tiempo, la sesión se cerrará automáticamente.

## 14. Recomendaciones de Uso

- Verifique siempre que está trabajando en la empresa correcta.
- Revise el estado antes de intentar aprobar.
- Si es jefe de área y no ve órdenes, confirme que tenga áreas asignadas.
- Si es gerente y no aparece el botón `Aprobar`, verifique que la orden esté `PREAPROBADA`.
- Si una asignación por empresa no aparece, vuelva a abrir el registro y confirme los datos guardados.

## 15. Problemas Frecuentes

### 15.1 No puedo ingresar

Revise:

- correo,
- contraseña,
- estado activo del usuario.

### 15.2 No veo una orden

Revise:

- filtros aplicados,
- empresa seleccionada,
- rango de fechas,
- áreas asignadas si es jefe de área.

### 15.3 No me aparece el botón de aprobar

Revise:

- que el usuario sea gerente,
- que la orden esté `PREAPROBADA`.

### 15.4 No veo mis áreas seleccionadas

Revise:

- que el usuario sea `JEFE DE AREA`,
- que las áreas hayan sido guardadas correctamente por empresa.

