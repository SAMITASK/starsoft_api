<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

//Companies
const selectedCompany = ref("003");
const selectedType = ref("OC");
const companies = ref([]);
const isLoading = ref(true);
const errorMessage = ref(null);

onMounted(async () => {
  try {
    const res = await $api("/users/companies", {
      method: "GET",
      onResponseError({ response }) {
        throw new Error(response._data?.message || "Error al obtener empresas");
      },
    });

    if (!Array.isArray(res)) {
      throw new Error("Formato de datos inválido");
    }

    companies.value = res.map((company) => ({
      title: company.name,
      value: company.id,
      rawData: company,
    }));
  } catch (error) {
    console.error("Error cargando empresas:", error);
    errorMessage.value =
      "No se pudieron cargar las empresas. Intente nuevamente.";
    companies.value = [];
  } finally {
    isLoading.value = false;
  }
});

// End Companies

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

const fetchSuppliers = async () => {
  if (!dateRange.value || !dateRange.value.includes(' a ')) return;

  try {
    const res = await $api("/reports/suppliers", {
      method: "GET",
      params: {
        company: selectedCompany.value,
        date: dateRange.value,
        type: selectedType.value,
      },
    });

    suppliers.value = res;
  } catch (error) {
    console.error("Error cargando proveedores:", error);
    suppliers.value = [];
  }
};

watch([selectedCompany, dateRange, selectedType], fetchSuppliers);

const filteredSuppliers = computed(() => {
  if (!searchQuery.value) return suppliers.value;

  return suppliers.value.filter(item =>
    item.NOMBRE_PROVEEDOR.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

fetchSuppliers();
</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filtros</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>
        <VCol cols="12" sm="4">
          <VSelect
            v-model="selectedCompany"
            label="Selecciona empresa"
            placeholder="Selecciona empresa"
            :items="companies"
            item-title="title"
            item-value="value"
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

    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <VSpacer />
      <div class="d-flex align-center gap-4 flex-wrap">
        <div class="app-user-search-filter">
          <VTextField
            v-model="searchQuery"
            placeholder="Buscar Proveedor"
            density="compact"
          />
        </div>
      </div>
    </VCardText>+
    <VTable
      class="invoice-preview-table border text-high-emphasis overflow-hidden mb-6"
    >
      <thead>
        <tr>
          <th>#</th>
          <th scope="col">PROVEEDOR</th>
          <th scope="col" class="text-center">MONTO</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="filteredSuppliers.length === 0">
          <td colspan="3" class="text-center text-muted">
            No se encontraron proveedores
          </td>
        </tr>
        <tr v-for="(item, index) in filteredSuppliers" :key="index">
          <td>{{ index + 1 }}</td>
          <td>{{ item.NOMBRE_PROVEEDOR }}</td>
          <td class="text-center">
            {{ Number(item.MONTO_TOTAL).toLocaleString("es-PE", { style: "currency", currency: "PEN" }) }}
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
