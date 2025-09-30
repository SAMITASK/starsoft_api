<script setup>
import { Spanish } from "flatpickr/dist/l10n/es.js";

const props = defineProps({
  dateRange: String,
  typeSel: String,
});

const emit = defineEmits(["update:dateRange", "update:typeSel"]);

const dateRange = ref(props.dateRange || "");
const typeSel = ref(props.typeSel || "OC");
</script>

<template>
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
</template>
