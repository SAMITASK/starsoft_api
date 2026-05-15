<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  selectedCompany: {
    type: String,
    default: null,
  },
  areaPermissions: {
    type: Object,
    default: () => ({}),
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])
const availableAreasByCompany = ref({})
const isLoadingAreas = ref(false)

const normalizeAreaCode = areaCode => `${areaCode ?? ''}`.trim().replace(/^0+(?=\d)/, '')

const loadAreasForCompany = async companyId => {
  if (!companyId || availableAreasByCompany.value[companyId]) {
    return
  }

  isLoadingAreas.value = true

  try {
    const res = await $api('/areas', {
      method: 'GET',
      query: {
        company: companyId,
      },
      onResponseError({ response }) {
        throw new Error(response._data?.message || 'Error al obtener áreas')
      },
    })

    availableAreasByCompany.value[companyId] = Array.isArray(res) ? res : []
  } catch (error) {
    console.error(`Error cargando áreas para ${companyId}:`, error)
    availableAreasByCompany.value[companyId] = []
  } finally {
    isLoadingAreas.value = false
  }
}

const areas = computed(() => {
  if (!props.selectedCompany || !props.areaPermissions[props.selectedCompany]) {
    return []
  }

  const allowedAreas = props.areaPermissions[props.selectedCompany]
  const availableAreas = availableAreasByCompany.value[props.selectedCompany] || []
  
  if (Array.isArray(allowedAreas)) {
    return allowedAreas.map(areaCode => {
      const normalizedAreaCode = normalizeAreaCode(areaCode)
      const areaInfo = availableAreas.find(area => normalizeAreaCode(area.AREA_CODIGO) === normalizedAreaCode)

      return {
        title: areaInfo?.AREA_DESCRIPCION || `${areaCode}`.trim(),
        value: areaInfo?.AREA_CODIGO || `${areaCode}`.trim(),
      }
    })
  }

  return []
})

const handleChange = (value) => {
  emit('update:modelValue', value)
  emit('change', value)
}

watch(() => props.selectedCompany, () => {
  loadAreasForCompany(props.selectedCompany)

  if (!areas.value.find((a) => a.value === props.modelValue)) {
    handleChange(null)
  }
}, { immediate: true })
</script>

<template>
  <VCol cols="12" sm="3">
    <VSelect
      :model-value="modelValue"
      :items="areas"
      item-title="title"
      item-value="value"
      label="Filtrar por Área"
      placeholder="Selecciona un área"
      :loading="isLoading || isLoadingAreas"
      clearable
      clear-icon="ri-close-line"
      no-data-text="No hay áreas disponibles"
      @update:model-value="handleChange"
    />
  </VCol>
</template>
