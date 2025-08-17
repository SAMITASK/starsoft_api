<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  company: String,
  code: String,
  type: String,
  module: String,
})

const details = ref({
  required: {
    TDESCRI: ''
  },
  responsible: {
    RESPONSABLE_NOMBRE: ''
  },
  products: []
})

const isLoading = ref(false)

const emit = defineEmits([
  'update:isDialogVisible',
  'submit',
])

const billingAddress = ref(structuredClone(toRaw(props.billingAddress)))

const resetForm = () => {
  emit('update:isDialogVisible', false)
  billingAddress.value = structuredClone(toRaw(props.billingAddress))
}

const onFormSubmit = async () => {
  const payload = {
    code: props.code,
    type: props.type,
    company: props.company,
    action: actionType.value, // 'approve' o 'reject'
  }

  try {
    const response = await $api('/handle-approval', {
      method: 'POST',
      body: payload,
    })

    // ✅ Cerrar el diálogo
    emit('update:isDialogVisible', false)

    // ✅ Solicitar actualización de la tabla
    emit('refresh')

    // ✅ (Opcional) Mostrar mensaje de éxito
    alert('Acción realizada correctamente')
  } catch (error) {
    console.error('Error al enviar acción:', error)
    alert('Hubo un error al procesar la acción.')
  }
}

watch(
  () => props.isDialogVisible,
  async (visible) => {
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

        details.value = res
        console.log(details.value)

      } catch (err) {
        console.error('Error al cargar detalles:', err)
      } finally {
        isLoading.value = false
      }
    }
  }
)


const toolbarTitle = computed(() => {
  return `${props.type}-${props.code} - ${props.module}`
})

const formatCurrency = (value) => {
  const number = parseFloat(value)
  if (isNaN(number)) return ''
  return new Intl.NumberFormat('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    useGrouping: true, // <-- activa separador de miles
  }).format(number)
}

const actionType = ref(null)

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
            <v-progress-circular indeterminate color="primary" size="40" />
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
                  label="Ruc Proveedor"
                  v-model="details.OC_CCODPRO"
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

              <VCol
                sm="12"
              >
                <VTextarea
                  label="Observación"
                  auto-grow
                  rows="1"
                  row-height="15"
                  persistent-placeholder
                  v-model="details.OC_COBSERV"
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
                <tr
                  v-for="(item, index) in details.products"
                  :key="index"
                >
                  <td class="text-center">{{ item.REQITEM_REF }}</td>
                  <td>{{ item.OC_CCODIGO }}</td>
                  <td>{{ item.OC_CDESREF }}</td>
                  <td class="text-center">{{ item.OC_CUNIDAD }}</td>
                  <td class="text-center">{{ formatCurrency(item.OC_NCANTID) }}</td>
                  <td class="text-center">{{ formatCurrency(item.OC_NPREUNI) }}</td>
                  <td class="text-center">{{ formatCurrency(item.OC_NTOTVEN) }}</td>
                  <td class="text-center">{{ formatCurrency(item.OC_NDSCPOR) }}%</td>
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
                <div class="d-flex justify-space-between align-center">
                  <!-- Botones a la izquierda -->
                  <div v-if="!details.NOMBRE_USUARIO">
                    <VBtn
                      type="submit"
                      color="success"
                      class="me-3"
                      @click="actionType = 'approve'"
                    >
                      Aceptar
                      <VIcon
                        end
                        icon="ri-checkbox-circle-line"
                      />
                    </VBtn>

                    <VBtn
                      type="submit"
                      color="error"
                      @click="actionType = 'reject'"
                    >
                      Rechazar
                      <VIcon
                        end
                        icon="ri-close-circle-line"
                      />
                    </VBtn>
                  </div>

                  <div v-else class="d-flex align-center gap-x-3">
                    <div class="d-flex flex-column">
                      <span class="text-dark font-weight-bold">
                        Aprobado por {{ details.NOMBRE_USUARIO }} el día {{ formatDate(details.FECHAHORA_CAMBIOESTADO) }}
                      </span>
                    </div>
                  </div>

                  <!-- Botón a la derecha -->
                  <div>
                    <VBtn
                      color="secondary"
                      variant="flat"
                      @click="resetForm"
                    >
                        <VIcon
                          start
                          icon="ri-logout-circle-line"
                        />
                      Cancelar
                    </VBtn>
                  </div>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </template>
      </VCardText>
    </VCard>
  </VDialog>
</template>
