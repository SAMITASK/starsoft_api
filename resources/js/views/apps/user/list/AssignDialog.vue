<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  user: {
    type: Object,
    required: false,
    default: null,
  },
});

const emit = defineEmits(["update:isDialogVisible"]);

const dialogVisibleUpdate = (val) => {
  emit("update:isDialogVisible", val);
};

const companyStarsoftId = ref("");
const selectedCompany = ref(null);
const isFormValid = ref(false)


const checkCompanyUser = async (companyId) => {
  if (!props.user) {
    console.warn("No hay usuario seleccionado");
    return;
  }

  try {
    const res = await $api(`/users/companyUser/${props.user.id}/${companyId}`, {
      method: "GET",
    });
    console.log("Respuesta:", res);
    companyStarsoftId.value = res || '';
  } catch (error) {
    console.error("Error:", error);
  }
};


const onSubmit = async () => {
  if (!isFormValid.value) return;

  try {
    const res = await $api('/users/addCompanyUser', {
      method: 'POST',
      body: {
        user_id: props.user.id,
        company_id: selectedCompany.value,
        user_code: companyStarsoftId.value || null,
      },
    });

    console.log('Respuesta del backend:', res);

    // Cerrar diálogo y limpiar formulario si quieres
    emit('update:isDialogVisible', false);
    selectedCompany.value = null;
    companyStarsoftId.value = '';

  } catch (error) {
    console.error('Error al agregar empresa:', error);
  }
};
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="600"
    @update:model-value="dialogVisibleUpdate"
  >
    <VCard class="share-project-dialog pa-sm-11 pa-3">
      <!-- 👉 dialog close btn -->
      <DialogCloseBtn
        size="default"
        variant="text"
        @click="emit('update:isDialogVisible', false)"
      />
      <VCardText class="pt-5">
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <div class="text-center mb-6">
            <h4 class="text-h4 mb-2">Asignar ID STARSOFT a Usuario</h4>
            <p class="text-body-1">
              Asignaras el ID STARSOFT a <b>{{ user.name }}</b> por cada
              empresa.
            </p>
          </div>

          <div class="mb-6">
            <VAutocomplete
              v-model="selectedCompany"
              :items="user.companies"
              item-title="name"
              item-value="id"
              density="compact"
              placeholder="Selecciona la empresa..."
              @update:model-value="checkCompanyUser"
            >
            </VAutocomplete>

            <VCol cols="12">
              <VTextField
                v-model="companyStarsoftId"
                :rules="[requiredValidator]"
                label="ID de usuario"
                placeholder=""
              />
            </VCol>
          </div>

          <div
            class="d-flex justify-center justify-sm-space-between align-center flex-wrap gap-3"
          >
            <div
              class="text-body-1 text-high-emphasis font-weight-medium d-flex align-center"
            ></div>

            <VBtn type="submit" prepend-icon="ri-link"> Vincular </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.share-project-dialog {
  .card-list {
    --v-card-list-gap: 1rem;
  }
}
</style>
