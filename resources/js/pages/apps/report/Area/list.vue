<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

//Companies
const selectedCompany = ref(useCookie('userData').value?.company_default || "003");
const selectedType = ref("OC");
const companies = ref([]);

const isLoading = ref(true);
const errorMessage = ref(null);
const totalMonto = computed(() => {
  return filteredAreas.value.reduce((sum, item) => sum + Number(item.MONTO_TOTAL || 0), 0);
});

const loadingTable = ref(false);

async function fetchData(url, params, loadingRef, errorRef) {
  loadingRef.value = true
  errorRef.value = null
  
  try {
    const query = new URLSearchParams(params).toString()
    const fullUrl = query ? `${url}?${query}` : url
    return await $api(fullUrl)
  } catch (err) {
    errorRef.value = err.message || "Error al cargar datos"
    throw err
  } finally {
    loadingRef.value = false
  }
}

async function loadCompanies() {
  companies.value = await fetchData('/users/companies', {}, isLoading, errorMessage)
}

loadCompanies()

// Date Range Picker

const today = new Date();

const sixDaysAgo = new Date();
sixDaysAgo.setDate(today.getDate() - 31);

// Formatear en 'YYYY-MM-DD'
function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

// Rango: de hace 6 días a hoy
const dateRange = ref(`${formatDate(sixDaysAgo)} a ${formatDate(today)}`);


const searchQuery = ref("");
const suppliers = ref([]);
const maxMonto = ref(10);

const fetchAreas = async () => {
  loadingTable.value = true;
  if (!dateRange.value || !dateRange.value.includes(' a ')) return;

  try {
    const res = await $api("/reports/areas", {
      method: "GET",
      params: {
        company: selectedCompany.value,
        date: dateRange.value,
        type: selectedType.value,
        search: searchQuery.value,
      },
    });

    suppliers.value = res.data;
    maxMonto.value = res.maxMonto;

    console.log(res)
  } catch (error) {
    console.error("Error cargando proveedores:", error);
    suppliers.value = [];
    maxMonto.value = 1;
  } finally {
    loadingTable.value = false;
  }
};


watch([selectedCompany, dateRange, selectedType], fetchAreas);

const filteredAreas = computed(() => {
  if (!searchQuery.value) return suppliers.value;

  return suppliers.value.filter(item =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const calculateProgress = (monto) => {
  const numericMonto = Number(monto);
  const numericMax = Number(maxMonto.value);

  if (numericMax <= 0) return 0;

  const progress = (numericMonto / numericMax) * 100;
  return Math.floor(progress);
};

const router = useRouter()

function goToArea(areaId, areaName) {
  router.push({
    name: 'apps-report-area-company-area',
    params: { company: selectedCompany.value, area: areaName },
    state: {
      id: areaId,
      dateRange: dateRange.value,
      type : selectedType.value,
    }
  })
}

fetchAreas();
</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filtros</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>
        <VCol cols="12" sm="3">
          <VSelect
            v-model="selectedCompany"
            label="Filtrar por empresa"
            placeholder="Filtrar empresa"
            :items="companies"
            item-title="name"
            item-value="id"
            :loading="isLoading"
            :error-messages="errorMessage"
            no-data-text="No hay empresas disponibles"
          />
        </VCol>
        <VCol cols="12" sm="3">
          <AppDateTimePicker
            v-model="dateRange"
            label="Rango de fechas"
            placeholder="Selecciona el rango"
            :config="{
              mode: 'range',
              dateFormat: 'Y-m-d',
              locale: Spanish,
              maxDate: new Date(),
            }"
          />
        </VCol>
        <VCol cols="12" sm="3">
          <VSelect
            v-model="selectedType"
            label="Selecciona Tipo Orden"
            placeholder="Selecciona Tipo Orden"
            :items="[
              { title: 'Orden de Compra', value: 'OC' },
              { title: 'Orden de Servicio', value: 'OS' },
              { title: 'Todos', value: 'ALL' },
            ]"
            item-title="title"
            item-value="value"
            no-data-text="No hay empresas disponibles"
          />
        </VCol>
      </VRow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-space-between align-center flex-wrap">
      <!-- Izquierda: buscador -->
      <div class="app-user-search-filter">
        <VTextField
          v-model="searchQuery"
          placeholder="Buscar Proveedor"
          density="compact"
        />
      </div>

      <!-- Derecha: monto total -->
      <div class="text-h6 font-weight-medium">
        Total: {{ new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(totalMonto) }}
      </div>
    </VCardText>
    <VTable hover>
      <thead>
        <tr>
          <th>Código</th>
          <th>Área</th>
          <th>Monto</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="loadingTable">
          <td colspan="3" class="text-center">
            <VProgressCircular indeterminate color="primary" size="32" />
          </td>
        </tr>
        <tr
          v-for="(item, index) in filteredAreas"
          :key="index"
          class="area-row"
          @click="goToArea(item.id, item.name)"
        >
          <td>{{ item.id }}</td>
          <td>{{ item.name }}</td>
          <td class="text-center">
            <VProgressLinear
              :model-value="calculateProgress(item.MONTO_TOTAL)"
              height="19"
              color="primary"
              rounded
            >
              <strong>
                {{ Number(item.MONTO_TOTAL).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' }) }}
              </strong>
            </VProgressLinear>
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCard>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}

.row-unread {
  color: #1f1f1f;
  font-weight: bold;
}
</style>
