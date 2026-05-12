<script setup>
const props = defineProps({
  isDialogVisible: Boolean,
  onContinue: Function,
  onLogout: Function,
  warningSeconds: { type: Number, default: 60 },
  remainingSeconds: { type: Number, default: 0 },
})

const emit = defineEmits(['update:isDialogVisible'])

const progressValue = computed(() => {
  if (props.warningSeconds <= 0) {
    return 0
  }

  return Math.max((props.remainingSeconds / props.warningSeconds) * 100, 0)
})

const handleContinue = () => {
  props.onContinue()
}

const handleLogout = () => {
  props.onLogout()
}
</script>
<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="500"
    persistent
    @update:model-value="val => emit('update:isDialogVisible', val)"
  >
    <VCard class="pa-6 text-center">
      <VRow class="align-center">
        <!-- Columna 1: Circular -->
        <VCol cols="12" sm="5" class="d-flex justify-center">
          <VProgressCircular
            :rotate="360"
            :size="100"
            :width="10"
            :model-value="progressValue"
            color="primary"
          >
            <strong>{{ props.remainingSeconds }}</strong>s
          </VProgressCircular>
        </VCol>

        <!-- Columna 2: Texto -->
        <VCol cols="12" sm="7" class="d-flex flex-column justify-center text-center text-sm-left">
          <h4 class="text-h6 mb-2">⚠️ Tu sesión expirará pronto</h4>
          <p class="mb-0">Por tu seguridad, se cerrará la sesión automáticamente.</p>
        </VCol>
      </VRow>

      <div class="d-flex justify-center gap-4 mt-6">
        <VBtn
          color="error"
          variant="outlined"
          @click="handleLogout"
        >
          Desconectar
        </VBtn>
        <VBtn
          color="primary"
          @click="handleContinue"
        >
          Continuar sesión
        </VBtn>
      </div>
    </VCard>
  </VDialog>
</template>
