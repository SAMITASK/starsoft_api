<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  companyIds: {
    type: Array,
    default: () => [],
  },
  companies: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const areasByCompany = ref({})
const loadingCompany = ref(null)

// 📌 Cargar áreas disponibles por empresa
const loadAreasForCompany = async companyId => {
  loadingCompany.value = companyId

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

    areasByCompany.value[companyId] = Array.isArray(res) ? res : []
  } catch (error) {
    console.error(`Error cargando áreas para ${companyId}:`, error)
    areasByCompany.value[companyId] = []
  } finally {
    loadingCompany.value = null
  }
}

// 🔄 Cargar áreas cuando cambian las empresas
watch(
  () => props.companyIds,
  newCompanies => {
    if (newCompanies && newCompanies.length > 0) {
      newCompanies.forEach(companyId => {
        if (!areasByCompany.value[companyId]) {
          loadAreasForCompany(companyId)
        }
      })
    }
  },
  { immediate: true },
)

const handleAreaChange = (companyId, selectedAreas) => {
  const updated = {
    ...props.modelValue,
    [companyId]: selectedAreas || [],
  }

  emit('update:modelValue', updated)
}

const getAreasForCompany = companyId => {
  return areasByCompany.value[companyId] || []
}

const getCompanyName = companyId => {
  return props.companies.find(company => company.value === companyId)?.title || companyId
}
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      class="mb-3"
    >
      <h6 class="text-base font-weight-bold mb-3">
        Permisos de Áreas por Empresa
      </h6>
      <VDivider />
    </VCol>

    <VCol
      v-for="companyId in companyIds"
      :key="companyId"
      cols="12"
      md="6"
    >
      <VSelect
        :model-value="modelValue[companyId] || []"
        :items="getAreasForCompany(companyId)"
        item-title="AREA_DESCRIPCION"
        item-value="AREA_CODIGO"
        :label="`Áreas - ${getCompanyName(companyId)}`"
        placeholder="Selecciona las áreas permitidas"
        multiple
        chips
        :loading="loadingCompany === companyId"
        clearable
        no-data-text="No hay áreas disponibles"
        @update:model-value="handleAreaChange(companyId, $event)"
      />
    </VCol>
  </VRow>
</template>
