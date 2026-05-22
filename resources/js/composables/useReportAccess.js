import { computed } from 'vue'

const normalizeAreaCode = areaCode => {
  const normalizedAreaCode = `${areaCode ?? ''}`.trim()

  if (/^\d+$/.test(normalizedAreaCode))
    return normalizedAreaCode.replace(/^0+(?=\d)/, '') || '0'

  return normalizedAreaCode.toUpperCase()
}

export const useReportAccess = () => {
  const userData = useCookie('userData')
  const normalizedCargo = computed(() => `${userData.value?.cargo ?? ''}`.toUpperCase().replace(/\s+/g, ' ').trim())
  const isAreaManager = computed(() => normalizedCargo.value === 'JEFE DE AREA')
  const isManagerOrAdmin = computed(() => ['GERENTE', 'ADMINISTRADOR'].includes(normalizedCargo.value))
  const areaPermissions = computed(() => userData.value?.area_permissions ?? {})

  const getAllowedAreasForCompany = companyId => {
    if (!companyId || !isAreaManager.value)
      return null

    return Array.isArray(areaPermissions.value?.[companyId])
      ? areaPermissions.value[companyId]
      : []
  }

  // En reportes el backend también filtra, pero dejamos esta capa para que
  // el select nunca ofrezca áreas fuera del alcance del JEFE DE AREA.
  const filterAreasForCompany = (companyId, availableAreas = []) => {
    if (!Array.isArray(availableAreas))
      return []

    if (!isAreaManager.value)
      return availableAreas

    const allowedAreas = getAllowedAreasForCompany(companyId)

    if (!allowedAreas?.length)
      return []

    const allowedAreaCodes = new Set(allowedAreas.map(normalizeAreaCode))

    return availableAreas.filter(area => {
      const areaCode = area?.id ?? area?.value ?? area?.AREA_CODIGO

      return allowedAreaCodes.has(normalizeAreaCode(areaCode))
    })
  }

  const sanitizeSelectedArea = (selectedArea, availableAreas = []) => {
    if (!selectedArea)
      return null

    const normalizedSelectedArea = normalizeAreaCode(selectedArea)

    return availableAreas.some(area => {
      const areaCode = area?.id ?? area?.value ?? area?.AREA_CODIGO

      return normalizeAreaCode(areaCode) === normalizedSelectedArea
    })
      ? selectedArea
      : null
  }

  return {
    isAreaManager,
    isManagerOrAdmin,
    areaPermissions,
    getAllowedAreasForCompany,
    filterAreasForCompany,
    sanitizeSelectedArea,
  }
}
