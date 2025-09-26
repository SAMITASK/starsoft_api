export default [
  {
    title: 'Home',
    to: { name: 'root' },
    icon: { icon: 'ri-home-smile-2-line' },
    roles: ['ADMINISTRADOR', 'JEFE DE COMPRAS', 'SISTEMAS'],
  },
    {
    title: 'Ordenes de Compra',
    to: { name: 'ocs' },
    icon: { icon: 'ri-file-text-line' },
    roles: ['ADMINISTRADOR', 'GERENTE', 'JEFE DE COMPRAS', 'SISTEMAS']
  },
    {
    title: 'Proveedores',
    to: { name: 'apps-supplier-list' },
    icon: { icon: 'ri-store-3-line' },
    roles: ['ADMINISTRADOR', 'GERENTE', 'JEFE DE COMPRAS', 'SISTEMAS']
  },
  {
    title: 'Productos',
    to: { name: 'apps-product-list' },
    icon: { icon: 'ri-table-view' },
    roles: ['ADMINISTRADOR', 'GERENTE', 'JEFE DE COMPRAS', 'SISTEMAS']
  },
  
  {
    title: 'Usuarios',
    to: { name: 'apps-user-list' },
    icon: { icon: 'ri-group-line' },
    roles: ['ADMINISTRADOR', 'SISTEMAS']
  },
]
