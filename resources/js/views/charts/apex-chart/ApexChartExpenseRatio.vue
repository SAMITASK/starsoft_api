<script setup>
import { ref, computed, watch } from 'vue'
import { useTheme } from 'vuetify'
import { getDonutChartConfig } from '@core/libs/apex-chart/apexCharConfig'

// Props
const props = defineProps({
  company: String,
  dateRange: String,
  staff: String,
  type: String,
  urlParams: {
    type: String,
    default: '/reports/areas',
  },
  valueType: {
    type: String,
    default: 'money', // money | count
  },
})

const vuetifyTheme = useTheme()

const series = ref([])
const labels = ref([])
const total = ref(0)

const isLoading = ref(false)
const hasLoaded = ref(false)

async function loadChartData() {
  if (!props.company || !props.dateRange) {
    resetChart()
    hasLoaded.value = false
    return
  }

  try {
    isLoading.value = true
    hasLoaded.value = false

    const { data } = await $api(props.urlParams, {
      params: {
        company: props.company,
        date: props.dateRange,
        staff: props.staff,
        type: props.type,
      },
    })

    if (Array.isArray(data) && data.length > 0) {
      series.value = data.map(r => r.MONTO_TOTAL)
      labels.value = data.map(r => r.name)
      total.value = data.reduce((sum, r) => sum + parseFloat(r.MONTO_TOTAL || 0), 0)
    } else {
      resetChart()
    }
  } catch (error) {
    resetChart()
  } finally {
    isLoading.value = false
    hasLoaded.value = true
  }
}

const chartOptions = computed(() => 
  getDonutChartConfig(vuetifyTheme.current.value, labels.value, total.value, props.valueType)
)

console.log(props.valueType);


// Resetear gráfico
function resetChart() {
  series.value = []
  labels.value = []
  total.value = 0
}

// Reactividad a cambios de filtros
watch(
  [() => props.company, () => props.dateRange, () => props.staff, () => props.type],
  loadChartData,
  { immediate: true }
)
</script>

<template>
  <div class="chart-container">
    <!-- Gráfico -->
    <VueApexCharts
      v-if="series.length > 0"
      type="donut"
      height="500"
      :options="chartOptions"
      :series="series"
    />

    <!-- Estado vacío -->
    <div
      v-else-if="hasLoaded && !isLoading"
      class="d-flex flex-column align-center justify-center text-center py-16 empty-state"
      style="block-size: 500px;"
    >
      <VIcon icon="ri-pie-chart-line" size="64" class="text-disabled mb-4" />
      <p class="text-disabled">No hay datos disponibles</p>

      <VBtn
        v-if="!props.company || !props.dateRange"
        color="primary"
        variant="tonal"
        class="mt-2"
      >
        Selecciona los filtros para ver los datos
      </VBtn>
    </div>
  </div>
</template>
