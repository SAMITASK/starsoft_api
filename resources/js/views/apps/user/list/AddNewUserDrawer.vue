<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  editingUser: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'userData',
  'userUpdated',
])

const isFormValid = ref(false)
const refForm = ref()
const fullName = ref('')
const email = ref('')
const cargo = ref('')
const status = ref()
const password = ref('')
const companies = ref([])
const company = ref()
const isEditing = ref(false)

watch(() => props.editingUser, (newUser) => {
  if (newUser) {
    isEditing.value = true
    fullName.value = newUser.name || newUser.fullName || ''
    email.value = newUser.email || ''
    cargo.value = newUser.role || newUser.cargo || ''
    status.value = newUser.status || ''
    company.value = newUser.company_ids || []
    password.value = ''
  } else {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    isEditing.value = false
  }
}, { immediate: true })

onMounted(async () => {
  await loadCompanies()
})

const loadCompanies = async () => {
  try {
    const res = await $api('/companies', {
      method: 'GET',
      onResponseError({ response }) {
        throw new Error(response._data?.message || 'Error al obtener empresas')
      },
    })

    if (!Array.isArray(res)) {
      throw new Error('Formato de datos inválido')
    }

    companies.value = res.map((company) => ({
      title: company.name,
      value: company.id,
      rawData: company,
    }))

  } catch (error) {
    console.error('Error cargando empresas:', error)
    companies.value = []
  }
}

const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    isEditing.value = false
  })
}


const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const userData = {
        fullName: fullName.value,
        cargo: cargo.value,
        company: company.value,
        email: email.value,
        status: status.value,
        avatar: '',
      }

      if (!isEditing.value) {
        userData.password = password.value
      }

      if (isEditing.value && props.editingUser) {
        userData.id = props.editingUser.id
        emit('userUpdated', userData)
      } else {
        emit('userData', userData)
      }
      
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        isEditing.value = false
      })
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title dinámico -->
    <AppDrawerHeaderSection
      :title="isEditing ? 'Editar Usuario' : 'Agregar Usuario'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Full name -->
              <VCol cols="12">
                <VTextField
                  v-model="fullName"
                  :rules="[requiredValidator]"
                  label="Nombres y Apellidos"
                  placeholder="John Doe"
                />
              </VCol>

              <!-- 👉 Cargo -->
              <VCol cols="12">
                <VTextField
                  v-model="cargo"
                  :rules="[requiredValidator]"
                  label="Cargo"
                  placeholder="Gerente"
                />
              </VCol>
              

              <!-- 👉 Email -->
              <VCol cols="12">
                <VTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="Email"
                  placeholder="johndoe@email.com"
                  :disabled="isEditing"
                />
              </VCol>

              <!-- 👉 Password (solo para nuevo usuario) -->
              <VCol cols="12">
                <VTextField
                  v-model="password"
                  :rules="isEditing ? [] : [requiredValidator]"
                  label="Contraseña"
                  placeholder="********"
                  type="password"
                />
              </VCol>

              <!-- 👉 Companies -->
              <VCol cols="12">
                <VSelect
                  v-model="company"
                  label="Selecciona empresa"
                  placeholder="Selecciona empresa"
                  multiple
                  :rules="[requiredValidator]"
                  :items="companies"
                />
              </VCol>

              <!-- 👉 Status -->
              <VCol cols="12">
                <VSelect
                  v-model="status"
                  label="Seleccionar Estado"
                  placeholder="Seleccionar Estado"
                  :rules="[requiredValidator]"
                  :items="[{ title: 'Activo', value: 'active' }, { title: 'Inactivo', value: 'inactive' }]"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-4"
                >
                  {{ isEditing ? 'Actualizar' : 'Guardar' }}
                </VBtn>
                <VBtn
                  type="reset"
                  variant="outlined"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Cancelar
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
