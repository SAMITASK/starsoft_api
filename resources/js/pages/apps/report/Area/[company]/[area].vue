<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";
import TotalOverview from "@/views/apps/report/Area/totalOverview.vue";

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
  { title: "Provedor", key: "data-table-group" },
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
  },
];

const groupBy = [{ key: "proveedor_name" }];

const searchQuery = ref("");

const { data: suppliersData, execute: fetchSuppliers } = await useApi(
  createUrl("/reports/products", {
    query: {
      q: searchQuery,
      company: company,
      date: dateRange,
      type: typeSel,
      area: id,
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

const goToSupplier = (item) => {
  router.push({
    name: 'apps-supplier-id',
    params: { id: item.items[0].raw.proveedor_id },
    state: { 
      company,
      dateRange: dateRange.value,
      type : typeSel.value,
     }
  })
}

const goToProduct = (item) => {
  console.log(item.product_id)
  router.push({
    name: 'apps-product-id',
    params: { id: item.product_id },
    state: { 
      company,
      dateRange: dateRange.value,
      type : typeSel.value,
    }
  })
}

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
    <VCardItem>
      <div class="d-flex justify-space-between align-center flex-wrap gap-4">
        <VCardTitle>Ordenes de compra y servicio</VCardTitle>
        <div style="inline-size: 15.625rem;">
          <VTextField
            v-model="searchQuery"
            placeholder="Buscar Proveedor o Producto"
            density="compact"
            style="inline-size: 15.625rem;"
            hover
          />
        </div>
      </div>
    </VCardItem>
    <VDataTableServer
      :headers="headers"
      :group-by="groupBy"
      :items="suppliers"
      :items-length="total"
      class="text-no-wrap"
      @update:options="updateOptions"
      hide-default-footer
    >
      <template #item.product_name="{ item }">
        <span 
          class="cursor-pointer text-primary"
          @click="goToProduct(item)"
        >
          {{ item.product_name }}
        </span>
      </template>
      <template #item.precio_unitario="{ item }">
        <div class="d-flex align-center gap-x-3">
          <div class="d-flex flex-column">
            <span class="text-base">{{
              Number(item.precio_unitario).toLocaleString("es-PE", {
                style: "currency",
                currency: "PEN",
              })
            }}</span>
          </div>
        </div>
      </template>
      <template #item.precio_igv="{ item }">
        <div class="d-flex align-center gap-x-3">
          <div class="d-flex flex-column">
            <span class="text-base">{{
              Number(item.precio_igv).toLocaleString("es-PE", {
                style: "currency",
                currency: "PEN",
              })
            }}</span>
          </div>
        </div>
      </template>
      <template #item.total="{ item }">
        <div class="d-flex align-center gap-x-3">
          <div class="d-flex flex-column">
            <span class="text-base">{{
              Number(item.total).toLocaleString("es-PE", {
                style: "currency",
                currency: "PEN",
              })
            }}</span>
          </div>
        </div>
      </template>

      <template #data-table-group="{ props, item, count }">
        <td class="cursor-pointer">
          <VBtn v-bind="props" variant="text" density="comfortable"> </VBtn>

          <span @click="goToSupplier(item)">{{ item.value }}</span>
          <span @click="goToSupplier(item)">({{ count }})</span>
        </td>
      </template>
    </VDataTableServer>
  </VCard>
</template>
