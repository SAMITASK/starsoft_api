<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";
import AreaFilter from "@/views/apps/report/Area/AreaFilter.vue";
import TotalOverview from "@/views/apps/report/Area/totalOverview.vue";
import { ref } from "vue";
import { $api } from "@/utils/api";

const route = useRoute();
const router = useRouter();
const dateRange = ref("");
const typeSel = ref();
const nameArea = ref(route.params.area);
const company = route.params.company;

const id = ref();

const state = router.options.history.state;
if (state && state.dateRange) {
  dateRange.value = state.dateRange;
  typeSel.value = state.type;
  id.value = state.id;
}

const page = ref(1);
const sortBy = ref();
const orderBy = ref();

const headers = [
  { title: "Provedor",
  key: 'data-table-group' 
}, 
  {
    title: "Producto",
    key: "product_name",
  },
  {
    title: "Unidad",
    key: "unidad",
  },
  {
    title: "Cantidad",
    key: "cantidad",
  },
  {
    title: "Precio Unitario (S/)",
    key: "precio_unitario",
  },
  {
    title: "Precio con IGV (S/)",
    key: "precio_igv",
  },
  {
    title: "Total (S/)",
    key: "total",
  }
];

const groupBy = [{ key: 'proveedor_name' }]

const searchQuery = ref("");


const { data: suppliersData, execute: fetchSuppliers } = await useApi(
  createUrl("/reports/products", {
    query: {
      q: searchQuery,
      company: company,
      date: dateRange,
      type: typeSel,
      area: id
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


</script>

<template>
  <VRow class="match-height">
    <VCol cols="12" md="4">
       <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Filtros</VCardTitle>
    </VCardItem>
    <VCardText>
      <VRow>
        <VCol cols="12" sm="12">
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
        <VCol cols="12" sm="12">
          <VSelect
            v-model="typeSel"
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
  </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <TotalOverview :name-area="nameArea" />
    </VCol>
  </VRow>

    <VCard class="mt-6">
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
      <VDataTableServer
        :headers="headers"
        :group-by="groupBy"
        :items="suppliers"
        :items-length="total"
        class="text-no-wrap"
        @update:options="updateOptions"
        hide-default-footer
      >
      <template #item.precio_unitario="{ item }">
        <div
          class="d-flex align-center gap-x-3"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ Number(item.precio_unitario).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' }) }}</span>
          </div>
        </div>
      </template>
      <template #item.precio_igv="{ item }">
        <div
          class="d-flex align-center gap-x-3"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ Number(item.precio_igv).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' }) }}</span>
          </div>
        </div>
      </template>
      <template #item.total="{ item }">
        <div
          class="d-flex align-center gap-x-3"
        >
          <div class="d-flex flex-column">
            <span class="text-base">{{ Number(item.total).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' }) }}</span>
          </div>
        </div>
      </template>

        <template #data-table-group="{ props, item, count }">
          <td>
            <VBtn v-bind="props" variant="text" density="comfortable">
            </VBtn>

            <span>{{ item.value }}</span>
            <span>({{ count }})</span>
          </td>
        </template>
      </VDataTableServer>
    </VCard>
</template>
