<script setup>
import SupplierBioPanel from "@/views/apps/supplier/view/SupplierBioPanel.vue";
import SupplierTabOverview from "@/views/apps/supplier/view/SupplierTabOverview.vue";

const route = useRoute("apps-supplier-id");
const userTab = ref(null);

const company = history.state.company;
const supplier = route.params.id;
const dateRange = history.state.dateRange || "";
const selectedType = history.state.type || "";

const { data: supplierData } = await useApi(
  `suppliers/view/${supplier}?company=${company}`
);

const filteredStats = ref(null);

const handleStatsUpdate = (stats) => {
  filteredStats.value = stats;
};
</script>

<template>
  <VRow v-if="supplierData">
    <VCol cols="12" md="12" lg="12">
      <SupplierBioPanel :supplier-data="supplierData" :filtered-stats="filteredStats"/>
    </VCol>

    <VCol cols="12" md="12" lg="12">
      <VWindow
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
          <SupplierTabOverview
            :company="company"
            :supplier="supplier"
            :dateRange="dateRange"
            :type="selectedType"
            @update:stats="handleStatsUpdate"
          />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert type="error" variant="tonal">
      Proveedor con RUC {{ route.params.id }} no encontrado!
    </VAlert>
  </div>
</template>
