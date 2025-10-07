<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

const router = useRouter()


// Valores seleccionados
const selectedCompany = ref(useCookie('userData').value?.company_default || "003");
const selectedType = ref(null)
const selectedFamily = ref(null)

// Opciones para los selects
const companies = ref([])
const types = ref([])
const families = ref([])

// Estados de carga y error (individuales para cada select)
const isLoading = ref(false)
const isLoadingTypes = ref(false)
const isLoadingFamilies = ref(false)

const errorMessage = ref(null)
const errorMessageTypes = ref(null)
const errorMessageFamilies = ref(null)

// Función genérica para cargar datos
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

// Cargar empresas
async function loadCompanies() {
  companies.value = await fetchData('/users/companies', {}, isLoading, errorMessage)
}

// Watchers
watch(selectedCompany, async (company) => {
  if (!company) {
    types.value = []
    families.value = []
    selectedType.value = null
    selectedFamily.value = null
    return
  }
  
  // Cargar tipos y familias en paralelo (mejor performance)
  const [typesData, familiesData] = await Promise.all([
    fetchData('/types', { company }, isLoadingTypes, errorMessageTypes),
    fetchData('/families', { company }, isLoadingFamilies, errorMessageFamilies)
  ])
  
  types.value = typesData
  families.value = familiesData
}, { immediate: true })

// Watcher para type (solo limpia family si es necesario)
watch(selectedType, (type) => {
  if (!type) {
    selectedFamily.value = null
  }
})

// Carga inicial
loadCompanies()


// Date Range Picker

const today = new Date();

// Hace 6 días
const sixDaysAgo = new Date();
sixDaysAgo.setDate(today.getDate() -365);

// Formatear en 'YYYY-MM-DD'
function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

// Rango: de hace 6 días a hoy
const dateRange = ref(`${formatDate(sixDaysAgo)} a ${formatDate(today)}`);

// End Date Range Picker


// Data table options
const itemsPerPage = ref(10);
const page = ref(1);
const sortBy = ref();
const orderBy = ref();
const selectedRows = ref([]);

// Headers
const headers = [
  {
    title: "CODIGO",
    key: "code",
  },
  {
    title: "DESCRIPCION",
    key: "description",
  },
  {
    title: "UNIDAD",
    key: "measure",
  },
  {
    title: "TIPO",
    key: "type_name",
  },
  {
    title: "FAMILIA",
    key: "family_name",
  },
  {
    title: "USUARIO",
    key: "user",
  },
  {
    title: "FECHA",
    key: "date",
    sortable: true,
  },
];

const searchQuery = ref("");
const selectedSwitch = ref(false);


const { data: productsData, execute: fetchSuppliers } = await useApi(
  createUrl("products", {
    query: {
      q: searchQuery,
      company: selectedCompany,
      type: selectedType,
      family: selectedFamily,
      date: dateRange,
      ignoreDateFilter: selectedSwitch,
      itemsPerPage,
      page,
      sortBy,
      orderBy,
    },
  })
);

const updateOptions = (options) => {
  page.value = options.page;
  sortBy.value = options.sortBy[0]?.key;
  orderBy.value = options.sortBy[0]?.order;
};


const suppliers = computed(() => productsData.value.products);
const total = computed(() => productsData.value.total);

const handleRowClick = (item) => {
  router.push({
      name: 'apps-product-id',
      params: { id: item.code },
      state: { company: selectedCompany.value }, // <-- aquí pasas el objeto completo
    })
}

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
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay empresas disponibles"
          />
        </VCol>

        <VCol cols="12" sm="3">
          <VSelect
            v-model="selectedType"
            label="Filtrar por tipo"
            placeholder="Filtrar por tipo"
            :items="types"
            item-title="name"
            item-value="id"
            :loading="isLoadingTypes"
            :error-messages="errorMessageTypes"
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay tipos disponibles"
          />
        </VCol>

        <VCol cols="12" sm="3">
          <VSelect
            v-model="selectedFamily"
            label="Filtrar por familia"
            placeholder="Filtrar por familia"
            :items="families"
            item-title="name"
            item-value="id"
            :loading="isLoadingFamilies"
            :error-messages="errorMessageFamilies"
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay familias disponibles"
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
          <VCol cols="12" sm="11" class="d-flex justify-end align-center">
            <VSwitch
              v-model="selectedSwitch"
              label="Mostrar Todos"
              label-position="start"
              inset
              style="transform: scale(1.1);"
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
            placeholder="Buscar Producto"
            density="compact"
          />
        </div>
      </div>
    </VCardText>

    <!-- SECTION datatable -->
    <VDataTableServer
      v-model:model-value="selectedRows"
      v-model:items-per-page="itemsPerPage"
      v-model:page="page"
      :headers="headers"
      show-select
      :items="suppliers"
      :items-length="total"
      class="text-no-wrap rounded-0"
      @update:options="updateOptions"
      hover
    >
        <template #item.code="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.code }}</span>
          </div>
        </div>
      </template>
      <template #item.description="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.description }}</span>
          </div>
        </div>
      </template>

            <template #item.type_name="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.type_name }}</span>
          </div>
        </div>
      </template>

      <template #item.measure="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.measure }}</span>
          </div>
        </div>
      </template>

      <template #item.family="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.family }}</span>
          </div>
        </div>
      </template>

      <template #item.user="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.user }}</span>
          </div>
        </div>
      </template>

      <!-- Pagination -->
      <template #bottom>
        <VDivider />

        <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
          <div
            class="d-flex align-center gap-x-2 text-medium-emphasis text-base"
          >
            Registros por página:
            <VSelect
              v-model="itemsPerPage"
              class="per-page-select"
              variant="plain"
              :items="[10, 20, 25, 50, 100]"
            />
          </div>

          <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
            {{ paginationMeta({ page, itemsPerPage }, total) }}
          </p>

          <div class="d-flex gap-x-2 align-center me-2">
            <VBtn
              class="flip-in-rtl"
              icon="ri-arrow-left-s-line"
              variant="text"
              density="comfortable"
              color="high-emphasis"
              :disabled="page <= 1"
              @click="page <= 1 ? (page = 1) : page--"
            />

            <VBtn
              class="flip-in-rtl"
              icon="ri-arrow-right-s-line"
              density="comfortable"
              variant="text"
              color="high-emphasis"
              :disabled="page >= Math.ceil(total / itemsPerPage)"
              @click="
                page >= Math.ceil(total / itemsPerPage)
                  ? (page = Math.ceil(total / itemsPerPage))
                  : page++
              "
            />
          </div>
        </div>
      </template>
    </VDataTableServer>
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
