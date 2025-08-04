<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

const router = useRouter()


//Companies
const selectedCompany = ref("003");
const companies = ref([]);
const isLoading = ref(true);
const errorMessage = ref(null);

onMounted(async () => {
  try {
    const res = await $api('/api/companies', {
      method: 'GET',
      onResponseError({ response }) {
        throw new Error(response._data?.message || 'Error al obtener empresas')
      },
    })

    if (!Array.isArray(res)) {
      throw new Error('Formato de datos inválido')
    }

    companies.value = res.map((company) => ({
      title: company.id,
      value: company.name,
      rawData: company,
    }))
  } catch (error) {
    console.error('Error cargando empresas:', error)
    errorMessage.value = 'No se pudieron cargar las empresas. Intente nuevamente.'
    companies.value = []
  } finally {
    isLoading.value = false
  }
})

// End Companies

// Date Range Picker

const today = new Date();

// Hace 6 días
const sixDaysAgo = new Date();
sixDaysAgo.setDate(today.getDate() - 30);

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
    title: "RUC",
    key: "ruc",
  },
  {
    title: "RAZON SOCIAL",
    key: "reason",
  },
  {
    title: "USUARIO",
    key: "user",
  },
  {
    title: "FECHA",
    key: "issue_date",
    sortable: true,
  },
];

const searchQuery = ref("");

const { data: suppliersData, execute: fetchSuppliers } = await useApi(
  createUrl("suppliers", {
    query: {
      q: searchQuery,
      company: selectedCompany,
      date: dateRange,
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


const suppliers = computed(() => suppliersData.value.suppliers);
const total = computed(() => suppliersData.value.total);

const handleRowClick = (item) => {
  router.push({
      name: 'apps-supplier-id',
      params: { id: item.code },
      state: { company: selectedCompany.value }, // <-- aquí pasas el objeto completo
    })
}

</script>

<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardItem> Filtros </VCardItem>
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
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay empresas disponibles"
          />
        </VCol>

        <VCol cols="12" sm="4">
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
      </VRow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <VSpacer />
      <div class="d-flex align-center gap-4 flex-wrap">
        <div class="app-user-search-filter">
          <VTextField
            v-model="searchQuery"
            placeholder="Buscar OC"
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
      <template #item.ruc="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.ruc }}</span>
          </div>
        </div>
      </template>

      <template #item.reason="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.reason }}</span>
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

      <template #item.issue_date="{ item }">
        <div
          class="d-flex align-center gap-x-3 cursor-pointer"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.issue_date }}</span>
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
