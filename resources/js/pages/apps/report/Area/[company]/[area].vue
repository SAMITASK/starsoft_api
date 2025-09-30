<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";
import AreaFilter from '@/views/apps/report/Area/AreaFilter.vue'
import TotalOverview from "@/views/apps/report/Area/totalOverview.vue";
import { ref } from "vue";



const route = useRoute()
const router = useRouter()
const dateRange = ref("")
const typeSel = ref()
const nameArea = ref(route.params.area)
const company = route.params.company
const searchQuery = ref("")

const id = ref();

const state = router.options.history.state
if(state && state.dateRange) {
  dateRange.value = state.dateRange
  typeSel.value = state.type
  id.value = state.id
}

console.log (company, nameArea.value, dateRange.value, typeSel.value, id.value)


const fetchAreas = async () => {
  if (!dateRange.value || !dateRange.value.includes(' a ')) return;

  try {
    const res = await $api("/reports/products", {
      method: "GET",
      params: {
        company: company,
        date: dateRange.value,
        area: id.value,
        type: typeSel.value,
      },
    });

    console.log(res)
  } catch (error) {
    console.error("Error cargando proveedores:", error);
    suppliers.value = [];
    maxMonto.value = 1;
  }
};


fetchAreas();


</script>

<template>
   <VRow class="match-height">
      <VCol
        cols="12"
        md="4"
      >
        <AreaFilter
          :date-range="dateRange"
          :type-sel="typeSel"
        />
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <TotalOverview
          :name-area="nameArea"
        />
      </VCol>
    </VRow>





</template>
