<script setup>
import { useOrderFilters } from "@/composables/useOrderFilters"

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  company: String,
  code: String,
  type: String,
  module: String,
  status: String,
  userCargo: String,
})

const emit = defineEmits([
  'update:isDialogVisible',
  'showSnackbar',
  'submit',
  'refresh',
])

// 🎯 Usar composable de filtros
const { userCargo: computedUserCargo, normalizeStatus } = useOrderFilters()

// Usar prop si viene, sino usar el del composable
const userCargoValue = computed(() => props.userCargo?.toUpperCase() || computedUserCargo.value)
const normalizedUserCargo = computed(() => userCargoValue.value.replace(/\s+/g, ' ').trim())
const currentStatus = computed(() => normalizeStatus(props.status))
const isAreaManagerRole = computed(() => normalizedUserCargo.value === 'JEFE DE AREA')
const canFinalApproveRole = computed(() => normalizedUserCargo.value === 'ADMINISTRADOR' || normalizedUserCargo.value.startsWith('GERENTE'))
const canPreApproveBool = computed(() => currentStatus.value === 'EMITIDA' && isAreaManagerRole.value)
const canApproveBool = computed(() => currentStatus.value === 'PREAPROBADA' && canFinalApproveRole.value)

const canRejectBool = computed(() => {
  return currentStatus.value === 'PREAPROBADA' && canFinalApproveRole.value
})

const emptyDetails = {
  required: {
    TDESCRI: '',
  },
  responsible: {
    RESPONSABLE_NOMBRE: '',
  },
  products: [],
}

const details = ref({ ...emptyDetails })

const isLoading = ref(false)

const resetForm = () => {
  emit('update:isDialogVisible', false)
}

const onFormSubmit = async action => {
  const payload = {
    code: props.code,
    type: props.type,
    company: props.company,
    action,
  }

  try {
    const response = await $api('/handle-approval', {
      method: 'POST',
      body: payload,
    })

    emit('update:isDialogVisible', false)

    emit('showSnackbar', { message: response.message, color: response.color })

    emit('refresh')

  } catch (error) {
    emit('showSnackbar', { message: 'Error inesperado al procesar la orden', color: 'error' })
  }
}

watch(
  () => props.isDialogVisible,
  async visible => {
    if (visible && props.code && props.type) {
      isLoading.value = true
      try {
        const res = await $api('/details-order', {
          method: 'POST',
          body: {
            code: props.code,
            type: props.type,
            company: props.company,
          },
          onResponseError({ response }) {
            throw new Error(response._data?.message || 'Error al obtener detalles')
          },
        })

        if (res && Object.keys(res).length > 0) {
          details.value = res
        } else {
          details.value = { ...emptyDetails }
        }

      } catch (err) {
        console.error('Error al cargar detalles:', err)
        details.value = { ...emptyDetails }
      } finally {
        isLoading.value = false
      }
    }
  },
)


const toolbarTitle = computed(() => {
  return `${props.type}-${props.code} - ${props.module}`
})

const formatCurrency = value => {
  const number = parseFloat(value)
  if (isNaN(number)) return ''
  
  return new Intl.NumberFormat('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    useGrouping: true, // <-- activa separador de miles
  }).format(number)
}


const
  formatDateHour = fechaStr => {
    if (!fechaStr) return ''

    const fecha = new Date(fechaStr)

    const dia = String(fecha.getDate()).padStart(2, '0')
    const mes = String(fecha.getMonth() + 1).padStart(2, '0') // meses empiezan en 0
    const anio = fecha.getFullYear()
    const hora = String(fecha.getHours()).padStart(2, '0')
    const minutos = String(fecha.getMinutes()).padStart(2, '0')

    return `${dia}/${mes}/${anio} ${hora}:${minutos}`
  }
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900 "
    :model-value="props.isDialogVisible"
    @update:model-value="val => $emit('update:isDialogVisible', val)"
  >
    <VCard>
      <div>
        <VToolbar color="primary">
          <VToolbarTitle> {{ toolbarTitle }} </VToolbarTitle>

          <VSpacer />

          <VBtn
            icon
            variant="plain"
            @click="resetForm"
          >
            <VIcon
              color="white"
              icon="ri-close-line"
            />
          </VBtn>
        </VToolbar>
      </div>

      <VCardText class="pt-5">
        <template v-if="isLoading">
          <div class="d-flex justify-center align-center py-10">
            <VProgressCircular
              indeterminate
              color="primary"
              size="40"
            />
            <span class="ms-4">Cargando información...</span>
          </div>
        </template>        
        <!-- 👉 Form -->
        <template v-else>
          <VForm @submit.prevent="onFormSubmit">
            <VRow>
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.OC_CCODPRO"
                  label="Ruc Proveedor"
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="8"
              >
                <VTextField
                  v-model="details.OC_CRAZSOC"
                  label="Razon Social Proveedor"
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.OC_DFECDOC"
                  label="Fecha de Emision"
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.OC_DFECENT"
                  label="Fecha de Entrega"
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.OC_CCODMON"
                  label="Moneda"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="3"
              >
                <VTextField
                  v-model="details.OC_NTIPCAM"
                  :prefix="details.OC_CCONVER"
                  label="Tipo de Cambio"
                  readonly
                  density="compact"
                />
              </VCol>
              <VCol
                cols="12"
                md="5"
              >
                <VTextField
                  v-model="details.OC_CFORPAG"
                  label="Forma de Pago"
                  persistent-placeholder
                  readonly
                  density="compact"
                />
              </VCol> 
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.required.TDESCRI"
                  label="Solicitado por"
                  persistent-placeholder
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="details.responsible.RESPONSABLE_NOMBRE"
                  label="Responsable de Compra"
                  persistent-placeholder
                  readonly
                  density="compact"
                />
              </VCol>
              <VCol
                cols="12"
                md="8"
              >
                <VTextField
                  v-model="details.OC_CFACNOMBRE"
                  label="Facturar a nombre de"
                  persistent-placeholder
                  readonly
                  density="compact"
                />
              </VCol>

              <VCol sm="12">
                <VTextarea
                  v-model="details.OC_COBSERV"
                  label="Observación"
                  auto-grow
                  rows="1"
                  row-height="15"
                  persistent-placeholder
                  readonly
                  density="compact"
                />
              </VCol>
            </VRow>
            
            <!-- 👉 table products -->

            <VTable class="invoice-preview-table border text-high-emphasis overflow-hidden mt-6 mb-6">
              <thead>
                <tr>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    #
                  </th>  
                  <th scope="col">
                    CODIGO
                  </th>
                  <th scope="col">
                    DESCRIPCION
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    UNI.
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    CANTIDAD
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    PU
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    PRECIO
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    %DESC
                  </th>
                  <th
                    scope="col"
                    class="text-center"
                  >
                    TOTAL
                  </th>                                            
                </tr>
              </thead>

              <tbody>
                <tr v-if="details.products.length === 0">
                  <td
                    colspan="9"
                    class="text-center text-grey"
                  >
                    No se encontraron productos
                  </td>
                </tr>
                <tr
                  v-for="(item, index) in details.products"
                  v-else
                  :key="index"
                >
                  <td class="text-center">
                    {{ item.REQITEM_REF }}
                  </td>
                  <td>{{ item.OC_CCODIGO }}</td>
                  <td>{{ item.OC_CDESREF }}</td>
                  <td class="text-center">
                    {{ item.OC_CUNIDAD }}
                  </td>
                  <td class="text-center">
                    {{ formatCurrency(item.OC_NCANTID) }}
                  </td>
                  <td class="text-center">
                    {{ formatCurrency(item.OC_NPREUNI) }}
                  </td>
                  <td class="text-center">
                    {{ formatCurrency(item.OC_NTOTVEN) }}
                  </td>
                  <td class="text-center">
                    {{ formatCurrency(item.OC_NDSCPOR) }}%
                  </td>
                  <td class="text-center">
                    {{
                      formatCurrency(
                        parseFloat(item.OC_NPREUNI) *
                          parseFloat(item.OC_NCANTID) *
                          (1 - parseFloat(item.OC_NDSCPOR) / 100)
                      )
                    }}
                  </td>
                </tr>
              </tbody>
            </VTable>

            <VRow>
              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="Importe"
                  :value="formatCurrency(details.OC_NIMPORT)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="Total"
                  :value="formatCurrency(details.OC_NIMPORT)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="I.G.V."
                  :value="formatCurrency(details.OC_NIGV)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="Descuento"
                  :value="formatCurrency(details.OC_NDESCUE)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="Percepción"
                  :value="formatCurrency(details.OC_NIMPPERC)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>

              <VCol
                cols="6"
                md="4"
              >
                <VTextField
                  label="Compra"
                  :value="formatCurrency(details.OC_NVENTA)"
                  readonly
                  persistent-placeholder
                  density="compact"
                />
              </VCol>
              <!-- 👉 Submit and Cancel button -->
              <VCol cols="12">
                <VRow
                  class="align-center"
                  justify="space-between"
                  align="center"
                  no-gutters
                >
                  <!-- 🎯 JEFE DE AREA - EMITIDA (Dar visto bueno) -->
                  <VCol
                    v-if="canPreApproveBool"
                    cols="12"
                    sm="auto"
                    class="mb-3"
                  >
                    <div class="d-flex flex-wrap gap-2">
                      <VBtn
                        color="warning"
                        @click="onFormSubmit('pre_approve')"
                      >
                        ✋ Dar Visto Bueno
                        <VIcon
                          end
                          icon="ri-hand-heart-line"
                        />
                      </VBtn>
                    </div>
                  </VCol>

                  <!-- 🎯 GERENTE/ADMINISTRADOR - PREAPROBADA (Aprobar) -->
                  <VCol
                    v-else-if="canApproveBool"
                    cols="12"
                    sm="auto"
                    class="mb-3"
                  >
                    <div class="d-flex flex-wrap gap-2">
                      <VBtn
                        color="success"
                        @click="onFormSubmit('approve')"
                      >
                        Aprobar
                        <VIcon
                          end
                          icon="ri-checkbox-circle-line"
                        />
                      </VBtn>
                      <VBtn
                        v-if="canRejectBool"
                        color="error"
                        @click="onFormSubmit('reject')"
                      >
                        Rechazar
                        <VIcon
                          end
                          icon="ri-close-circle-line"
                        />
                      </VBtn>
                    </div>
                  </VCol>

                  <!-- Si está PREAPROBADA (esperando aprobación final) -->
                  <VCol
                    v-else-if="currentStatus === 'PREAPROBADA'"
                    cols="12"
                    sm="auto"
                  >
                    <span class="text-warning font-weight-bold">
                      ⏳ Pendiente de aprobación final
                    </span>
                  </VCol>

                  <!-- Si está APROBADA -->
                  <VCol
                    v-else-if="currentStatus === 'APROBADA'"
                    cols="12"
                    sm="auto"
                  >
                    <span class="text-success font-weight-bold">
                      ✅ Aprobada por {{ details.NOMBRE_USUARIO }} el {{ formatDateHour(details.FECHAHORA_CAMBIOESTADO) }}
                    </span>
                  </VCol>

                  <!-- Si está RECHAZADO -->
                  <VCol
                    v-else-if="currentStatus === 'RECHAZADO'"
                    cols="12"
                    sm="auto"
                  >
                    <span class="text-error font-weight-bold">
                      ❌ Rechazado por {{ details.NOMBRE_USUARIO }} el {{ formatDateHour(details.FECHAHORA_CAMBIOESTADO) }}
                    </span>
                  </VCol>

                  <!-- Botón Cancelar -->
                  <VCol
                    cols="12"
                    sm="auto"
                  >
                    <VBtn
                      color="secondary"
                      variant="flat"
                      block
                      @click="resetForm"
                    >
                      <VIcon
                        start
                        icon="ri-logout-circle-line"
                      />
                      Cancelar
                    </VBtn>
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </VForm>
        </template>
      </VCardText>
    </VCard>
  </VDialog>
</template>
