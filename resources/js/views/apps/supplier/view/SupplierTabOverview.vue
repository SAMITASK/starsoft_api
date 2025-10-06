<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";
import OCSupplierDialog from "@/components/dialogs/OCSupplierDialog.vue";

const props = defineProps({
  company: {
    type: String,
    required: true,
  },
  supplier: {
    type: String,
    required: true,
  },
  dateRange: {
    type: String,
    required: false,
    default: "",
  },
  type: {
    type: String,
    required: false,
  },
})


const dateRange = ref(props.dateRange);
const selectedType = ref(props.type );

const headers = [
  {
    title: 'NUMERO',
    key: 'number',
  },
  {
    title: 'Detalle',
    key: 'observ',
  },
  {
    title: 'DOCUMENTO',
    key: 'tipo_doc',
  },
  {
    title: 'Solicita',
    key: 'solicita',
  },  
  {
    title: 'Reponsable',
    key: 'responsable',
  },
  {
    title: 'Fecha',
    key: 'issue_date',
    sortable: true,
  },
  {
    title: 'Estado',
    key: 'status',
  }
]

const status = ref([
  { value: "00", title: "EMITIDA", color: "secondary" },
  { value: "01", title: "APROBADA", color: "primary" },
  { value: "03", title: "REC. PARCIALMENTE", color: "warning" },
  { value: "04", title: "REC. TOTALMENTE", color: "success" },
  { value: "05", title: "LIQUIDADA", color: "info" },
  { value: "06", title: "ANULADA", color: "error" },
]);

function getStatusColor(value) {
  const s = status.value.find((s) => s.value === value);
  return s ? s.color : "grey";
}

const itemsPerPage = ref(99999);
const page = ref(1);
const sortBy = ref();
const orderBy = ref();
const isDialogVisible = ref(false);
const selectedItem = ref(null);

const searchQuery = ref("");

const { data: ocData, execute: fetchOc } = await useApi(
  createUrl("suppliers/ocs", {
    query: {
      q: searchQuery,
      company: props.company,
      supplier: props.supplier,
      date: dateRange,
      type: selectedType,
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

const ocs = computed(() => ocData.value.orders);

const handleRowClick = (item) => {
  selectedItem.value = item
  isDialogVisible.value = true
}

function formatDate(raw) {
  const date = new Date(raw)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  const hours = String(date.getHours()).padStart(2, '0')
  const mins = String(date.getMinutes()).padStart(2, '0')
  return `${day}/${month}/${year} ${hours}:${mins}`
}

const emit = defineEmits(['update:stats'])

// Agregar después de que tengas el computed de ocs
const stats = computed(() => {
  if (!ocs.value) return { oc: 0, ocs: 0 }
  
  return {
    oc: ocs.value.filter(o => o.type === 'OC').length,
    ocs: ocs.value.filter(o => o.type === 'OS').length
  }
})

// Watch para emitir cuando cambien los stats
watch(stats, (newStats) => {
  emit('update:stats', newStats)
}, { immediate: true })
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText v-if="dateRange && dateRange.length">
          <VRow>
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

        <VDivider v-if="dateRange && dateRange.length" />
        <VCardItem>
          <div class="d-flex justify-space-between align-center flex-wrap gap-4">
            <VCardTitle>Ordenes de compra y servicio</VCardTitle>
            <div style="inline-size: 15.625rem;">
              <VTextField
                v-model="searchQuery"
                placeholder="Buscar orden"
                density="compact"
                style="inline-size: 15.625rem;"
                hover
              />
            </div>
          </div>
        </VCardItem>
        <!-- 👉 User Project List Table -->

        <!-- SECTION Datatable -->
        <VDataTable
          :search="searchQuery"
          :headers="headers"
          v-model:items-per-page="itemsPerPage"
          :items="ocs"
          item-value="number"
          class="text-no-wrap rounded-0 font-weight-medium cursor-pointer"
          hover
        >
            <template #item.number="{ item }">
              <div
                class="d-flex align-center gap-x-3"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">{{ item.number }}</span>
                </div>
              </div>
            </template>

            <template #item.observ="{ item }">
              <div
                class="d-flex align-center gap-x-3"
                style="inline-size: 400px; white-space: normal; word-wrap: break-word;"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">{{ item.observ }}</span>
                </div>
              </div>
            </template>
          
            <template #item.tipo_doc="{ item }">
              <div
                class="d-flex align-center gap-x-3"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">{{ item.tipo_doc }}</span>
                </div>
              </div>
            </template>
          
            <template #item.solicita="{ item }">
              <div
                class="d-flex align-center gap-x-3"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">{{ item.solicita }}</span>
                </div>
              </div>
            </template>    
            
            <template #item.responsable="{ item }">
              <div
                class="d-flex align-center gap-x-3"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">{{ item.responsable }}</span>
                </div>
              </div>
            </template>             

            <template #item.issue_date="{ item }">
              <div 
                class="d-flex align-center gap-x-3"
                @click="handleRowClick(item)"
              >
                <div class="d-flex flex-column">
                  <span class="text-base">
                    {{
                      new Date(item.issue_date).getFullYear() < '2000'
                        ? '-----------------'
                        : formatDate(item.issue_date)
                    }}
                  </span>
                </div>
              </div>
            </template>

            <template #item.status="{ item }">
            <div
              class="d-flex align-center gap-x-3"
            >
              <div class="d-flex flex-column">
                <VChip
                  :color="getStatusColor(item.status)"
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
          
          <template #bottom />
        </VDataTable>
      </VCard>
      <OCSupplierDialog
        v-model:isDialogVisible="isDialogVisible"
        :company="company"
        :code="selectedItem?.number"
        :type="selectedItem?.tipo_doc"
        @refresh="fetchOc"
      />  
    </VCol>

  </VRow>
</template>
