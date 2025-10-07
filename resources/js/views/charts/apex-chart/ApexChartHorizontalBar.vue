<script setup>
import { useTheme } from "vuetify";
import { getBarChartConfig } from "@core/libs/apex-chart/apexCharConfig";

const props = defineProps({
  company: String,
  staff: String,
  urlParams: {
    type: String,
    default: "/reports/monthly",
  },
  valueType: {
    type: String,
    default: "money",
  },
});

const emit = defineEmits(['update:total'])

const vuetifyTheme = useTheme();
const series = ref([{ data: [] }]);
const categories = ref([]);
const total = ref(0);
const isLoading = ref(false);
const hasLoaded = ref(false);

async function loadChartData() {
  if (!props.company) {
    resetChart();
    hasLoaded.value = false;
    return;
  }

  try {
    isLoading.value = true;
    hasLoaded.value = false;

    const { data } = await $api(props.urlParams, {
      params: {
        company: props.company,
        staff: props.staff,
        area: props.area,
      },
    });

    if (Array.isArray(data) && data.length > 0) {
      series.value = [
        { name: "OC", data: data.map((r) => parseFloat(r.oc_total || 0)) },
        { name: "OS", data: data.map((r) => parseFloat(r.os_total || 0)) },
      ];

      categories.value = data.map((r) => {
        const [year, month] = r.month.split("-");
        const meses = [
          "ene",
          "feb",
          "mar",
          "abr",
          "may",
          "jun",
          "jul",
          "ago",
          "sep",
          "oct",
          "nov",
          "dic",
        ];
        return `${meses[parseInt(month) - 1]}. ${year.slice(2)}`;
      });
    } else {
      resetChart();
    }
  } catch (error) {
    console.error("Error cargando gráfico mensual:", error);
    resetChart();
  } finally {
    hasLoaded.value = true;
  }
}

const chartOptions = computed(() => {
  const options = getBarChartConfig(vuetifyTheme.current.value);
  return {
    ...options,
    xaxis: {
      ...options.xaxis,
      categories: categories.value,
    },
  };
});

function resetChart() {
  series.value = [{ data: [] }];
  categories.value = [];
  total.value = 0;
}
watch(
  [() => props.company, () => props.type, () => props.staff, () => props.area],
  loadChartData,
  { immediate: true }
);
</script>

<template>
  <div class="chart-container">
    <VueApexCharts
      v-if="series.length > 0"
      type="bar"
      height="400"
      :options="chartOptions"
      :series="series"
    />

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
