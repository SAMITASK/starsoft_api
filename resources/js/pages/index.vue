<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

import ApexChartExpenseRatio from "@/views/charts/apex-chart/ApexChartExpenseRatio.vue";
import ApexChartHorizontalBar from '@/views/charts/apex-chart/ApexChartHorizontalBar.vue'

definePage({
  meta: {
    requiresAuth: true,
  },
});

const userData = useCookie("userData");

const isManagerOrAdmin = computed(() =>
  ["GERENTE", "ADMINISTRADOR"].includes(userData.value?.cargo)
);

const selectedCompany = ref(useCookie('userData').value?.company_default || "003");
const selectedType = ref("OC");
const selectedStaff = ref(null);
const companies = ref([]);
const selectedArea = ref(null);
const staff = ref([]);
const areas = ref([]);

const isLoading = ref(true);
const isLoadingAreas = ref(false);
const isLoadingStaff = ref(false);
const errorMessage = ref(null);
const errorMessageAreas = ref(null);
const errorMessageStaff = ref(null);

async function fetchData(url, params, loadingRef, errorRef) {
  loadingRef.value = true;
  errorRef.value = null;

  try {
    const query = new URLSearchParams(params).toString();
    const fullUrl = query ? `${url}?${query}` : url;
    return await $api(fullUrl);
  } catch (err) {
    errorRef.value = err.message || "Error al cargar datos";
    throw err;
  } finally {
    loadingRef.value = false;
  }
}

async function loadCompanies() {
  const data = await fetchData("/users/companies", {}, isLoading, errorMessage);
  companies.value = data;
}

loadCompanies();

watch(selectedCompany, async (company) => {
  if (!company) {
    areas.value = [];
    selectedArea.value = null;
    staff.value = [];
    selectedStaff.value = null;
    return;
  }

  areas.value = [];
  selectedArea.value = null;
  staff.value = [];
  selectedStaff.value = null;

  const [staffData] = await Promise.all([
    fetchData("/staff", { company }, isLoadingStaff, errorMessageStaff),
  ]);

  staff.value = staffData;
});

watch(selectedStaff, () => {
  selectedArea.value = null;
});

const today = new Date();

const sixDaysAgo = new Date();
sixDaysAgo.setDate(today.getDate() - 31);

function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

const dateRange = ref(`${formatDate(sixDaysAgo)} a ${formatDate(today)}`);
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
        <VCol cols="12" sm="2" v-if="isManagerOrAdmin">
          <VSelect
            v-model="selectedStaff"
            label="Filtrar por encargado"
            placeholder="Filtrar encargado"
            :items="staff"
            item-title="name"
            item-value="id"
            :loading="isLoadingStaff"
            :error-messages="errorMessageStaff"
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay encargados disponibles"
          />
        </VCol>
        <VCol cols="12" sm="2">
          <VSelect
            v-model="selectedArea"
            label="Filtrar por area"
            placeholder="Filtrar por area"
            :items="areas"
            item-title="name"
            item-value="id"
            :loading="isLoadingAreas"
            :error-messages="errorMessageAreas"
            clearable
            clear-icon="ri-close-line"
            no-data-text="No hay areas disponibles"
          />
        </VCol>
        <VCol cols="12" sm="2">
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
  </VCard>
  <VRow>
    <VCol cols="12" md="6">
      <VCard title="Monto Areas" subtitle="Porcentaje y monto total por Area">
        <VCardText style="position: relative; min-block-size: 500px;">
          <ApexChartExpenseRatio
            :company="selectedCompany"
            :date-range="dateRange"
            :staff="selectedStaff"
            :type="selectedType"
            url-params="/reports/areas"
            value-type="money"
          />
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="12" md="6">
      <VCard
        title="OC/OS Areas"
        subtitle="Porcentaje y Cantidad de OC/OS total por Area"
      >
        <VCardText style="position: relative; min-block-size: 500px;">
          <ApexChartExpenseRatio
            :company="selectedCompany"
            :date-range="dateRange"
            :staff="selectedStaff"
            :type="selectedType"
            url-params="/reports/areas-by-orders"
            value-type="count"
          />
        </VCardText>
      </VCard>
    </VCol>
    <VCol
      cols="12"
      md="6"
    >
      <VCard title="Ultimos 5 Meses">
        <VCardText>
          <ApexChartHorizontalBar  
            :company="selectedCompany"
            :staff="selectedStaff"
            :type="selectedType"
            url-params="/reports/monthly"
            value-type="money"/>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
