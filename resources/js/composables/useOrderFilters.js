import { computed } from 'vue'

export const useOrderFilters = () => {
  const userData = useCookie('userData')
  const userCargo = computed(() => userData.value?.cargo?.toUpperCase() || '')
  const normalizedCargo = computed(() => userCargo.value.replace(/\s+/g, ' ').trim())
  const normalizeStatus = value => value === 'RECHAZADA' ? 'RECHAZADO' : value

  // 👤 Roles
  const isJefeDeArea = computed(() => normalizedCargo.value === 'JEFE DE AREA')
  const isGerente = computed(() => normalizedCargo.value.startsWith('GERENTE'))
  const isAdministrador = computed(() => normalizedCargo.value === 'ADMINISTRADOR')

  // 🔒 Control de permisos
  const canApprove = computed(() => isGerente.value || isAdministrador.value)
  const canPreApprove = computed(() => isJefeDeArea.value)
  const canMarkAsRead = computed(() => isGerente.value)

  // 🏢 Permisos por área
  const areaPermissions = computed(() => userData.value?.area_permissions ?? {})
  const hasAreaRestrictions = computed(() => isJefeDeArea.value && Object.keys(areaPermissions.value).length > 0)

  // 📋 Estados disponibles según rol
  const availableStatuses = computed(() => [
    { title: 'EMITIDA', value: 'EMITIDA', color: 'primary' },
    { title: 'PREAPROBADA', value: 'PREAPROBADA', color: 'warning' },
    { title: 'APROBADA', value: 'APROBADA', color: 'success' },
    { title: 'RECHAZADO', value: 'RECHAZADO', color: 'error' },
  ])

  // 🎨 Obtener color de estado
  const getStatusColor = value => {
    const status = availableStatuses.value.find(s => s.value === normalizeStatus(value))
    
    return status ? status.color : 'grey'
  }

  return {
    userCargo,
    isJefeDeArea,
    isGerente,
    isAdministrador,
    canApprove,
    canPreApprove,
    canMarkAsRead,
    areaPermissions,
    hasAreaRestrictions,
    availableStatuses,
    normalizeStatus,
    getStatusColor,
  }
}
