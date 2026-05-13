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

const isManagementRole = computed(() => {
  const role = props.user?.role || props.user?.cargo || ''

  return role.toUpperCase().startsWith('GERENTE')
})

const assignmentTitle = computed(() => (
  isManagementRole.value
    ? 'Asignar Correo Aprobador por Empresa'
    : 'Asignar ID STARSOFT a Usuario'
))

const assignmentDescription = computed(() => (
  isManagementRole.value
    ? `Asignarás el ID STARSOFT y el correo aprobador de ${props.user?.name || 'este usuario'} por cada empresa.`
    : `Asignarás el ID STARSOFT a ${props.user?.name || 'este usuario'} por cada empresa.`
))

const dialogVisibleUpdate = (val) => {
  emit("update:isDialogVisible", val);
};

const companyStarsoftId = ref("");
const approvalEmail = ref("");
const selectedCompany = ref(null);
const isFormValid = ref(false)

const resetAssignmentForm = () => {
  selectedCompany.value = null
  companyStarsoftId.value = ''
  approvalEmail.value = ''
}

const checkCompanyUser = async (companyId) => {
  if (!props.user) {
    console.warn("No hay usuario seleccionado");
    return;
  }

  try {
    const res = await $api(`/users/companyUser/${props.user.id}/${companyId}`, {
      method: "GET",
    });
    companyStarsoftId.value = res?.user_code || '';
    approvalEmail.value = res?.approval_email || '';
  } catch (error) {
    console.error("Error:", error);
  }
};

watch(() => props.user?.id, () => {
  resetAssignmentForm()
})

watch(() => props.isDialogVisible, visible => {
  if (!visible) {
    resetAssignmentForm()
  }
})


const onSubmit = async () => {
  if (!isFormValid.value) return;

  try {
    const res = await $api('/users/addCompanyUser', {
      method: 'POST',
      body: {
        user_id: props.user.id,
        company_id: selectedCompany.value,
        user_code: companyStarsoftId.value || null,
        approval_email: approvalEmail.value || null,
      },
    });

    // Cerrar diálogo y limpiar formulario si quieres
    emit('update:isDialogVisible', false);
    resetAssignmentForm()

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
            <h4 class="text-h4 mb-2">{{ assignmentTitle }}</h4>
            <p class="text-body-1">
              {{ assignmentDescription }}
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

              <VTextField
                v-if="isManagementRole"
                v-model="approvalEmail"
                :rules="[requiredValidator, emailValidator]"
                label="Correo aprobador"
                placeholder="correo@empresa.com"
                class="mt-4"
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
