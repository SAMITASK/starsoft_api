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

const areas = computed(() => {
  if (!props.selectedCompany || !props.areaPermissions[props.selectedCompany]) {
    return []
  }

  const allowedAreas = props.areaPermissions[props.selectedCompany]
  
  if (Array.isArray(allowedAreas)) {
    return allowedAreas.map((areaCode) => ({
      title: areaCode,
      value: areaCode,
    }))
  }

  return []
})

const handleChange = (value) => {
  emit('update:modelValue', value)
  emit('change', value)
}

watch(() => props.selectedCompany, () => {
  if (!areas.value.find((a) => a.value === props.modelValue)) {
    handleChange(null)
  }
})
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
      :loading="isLoading"
      clearable
      clear-icon="ri-close-line"
      no-data-text="No hay áreas disponibles"
      @update:model-value="handleChange"
    />
  </VCol>
</template>
