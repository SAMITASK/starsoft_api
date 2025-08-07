<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";
import OCDetailDialog from "@/components/dialogs/OCDetailDialog.vue";
//Companies
const selectedCompany = ref("003");
const companies = ref([]);
const isLoading = ref(true);
const errorMessage = ref(null); 

onMounted(async () => {
  try {
    const res = await $api(import.meta.env.VITE_API_BASE_URL + '/companies', {
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
sixDaysAgo.setDate(today.getDate() - 6);

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

const status = ref([
  { title: "EMITIDA", value: "EMITIDA", color: "secondary" },
  { title: "APROBADA", value: "APROBADA", color: "primary" },
  { title: "RECHAZADO", value: "RECHAZADO", color: "error" },
]);

function getStatusColor(value) {
  const s = status.value.find((s) => s.value === value);
  return s ? s.color : "grey";
}

// Data table options
const itemsPerPage = ref(30);
const page = ref(1);
const sortBy = ref();
const orderBy = ref();
const isDialogVisible = ref(false);
const selectedItem = ref(null);
const selectedRows = ref([]);

// Headers
const headers = [
  {
    title: "Empresa",
    key: "company",
  },
  {
    title: "Modulo",
    key: "module",
  },
  {
    title: "Tipo",
    key: "type",
  },
  {
    title: "Codigo",
    key: "code",
  },
  {
    title: "Asunto",
    key: "issue",
  },
  {
    title: "Fecha Envio",
    key: "issue_date",
    sortable: true,
  },
  {
    title: "Estado",
    key: "status",
  },
];

const searchQuery = ref("");
const selectedStatus = ref("EMITIDA");
const selectedType = ref();

const { data: ocsData, execute: fetchOcs } = await useApi(
  createUrl("purchase-orders", {
    query: {
      q: searchQuery,
      company: selectedCompany,
      status: selectedStatus,
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

const ocs = computed(() => ocsData.value.ocs);
const total = computed(() => ocsData.value.total);

const handleRowClick = (item) => {
  selectedItem.value = item
  isDialogVisible.value = true
  markAsRead(item)
}

const markAsRead = async (item) => {
  if (!item.read) {
    item.read = true

    try {
      await $api('/mark-as-read', {
        method: 'POST',
        body: {
          code: item.code,
          type: item.type,
          company: item.company,
        },
      })

      await fetchOcs()
    } catch (error) {
      console.error('Error al marcar como leído', error)
    }
  }
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
          <VSelect
            v-model="selectedStatus"
            label="Select Status"
            placeholder="Select Status"
            :items="status"
            clearable
            clear-icon="ri-close-line"
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
      :items="ocs"
      :items-length="total"
      class="text-no-wrap rounded-0"
      @update:options="updateOptions"
      hover
    >
      <template #item.company="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="inline-size: 300px; white-space: normal; word-wrap: break-word;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.company_name }}</span>
          </div>
        </div>
      </template>

      <template #item.module="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.module }}</span>
          </div>
        </div>
      </template>

      <template #item.type="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.type }}</span>
          </div>
        </div>
      </template>

      <template #item.code="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.code }}</span>
          </div>
        </div>
      </template>

      <template #item.issue="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.issue }}</span>
          </div>
        </div>
      </template>

      <template #item.issue_date="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ item.issue_date }}</span>
          </div>
        </div>
      </template>

      <template #item.status="{ item }">
        <div
          class="d-flex align-center gap-x-3"
          :class="{ 'row-unread': item.read === false }"
          style="cursor: pointer;"
          @click="handleRowClick(item)"
        >
          <div class="d-flex flex-column">
            <VChip
              :color="getStatusColor(item.status)"
              class="text-white"
              dense
              outlined
            >
              {{
                status.find((s) => s.value === item.status)?.title ??
                item.status
              }}
            </VChip>
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

  <OCDetailDialog
    v-model:isDialogVisible="isDialogVisible"
    :company="selectedItem?.company"
    :code="selectedItem?.code"
    :type="selectedItem?.type"
    :module="selectedItem?.module"
    @refresh="fetchOcs"
  />

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
