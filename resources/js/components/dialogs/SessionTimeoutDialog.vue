<script setup>
const props = defineProps({
  isDialogVisible: Boolean,
  onContinue: Function,
  onLogout: Function,
  warningSeconds: { type: Number, default: 60 }
})

const emit = defineEmits(['update:isDialogVisible'])

const totalSeconds = props.warningSeconds
const remainingSeconds = ref(totalSeconds)
const progressValue = ref(100)
let interval = null 

const startTimer = () => {
  interval = setInterval(() => {
    if (remainingSeconds.value <= 0) {
      clearInterval(interval)
      props.onLogout()
    } else {
      remainingSeconds.value--
      progressValue.value = (remainingSeconds.value / totalSeconds) * 100
    }
  }, 1000)
}

watch(
  () => props.isDialogVisible,
  (val) => {
    if (val) {
      remainingSeconds.value = totalSeconds
      progressValue.value = 100
      startTimer()
    } else if (interval) {
      clearInterval(interval)
    }
  }
)

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
            <strong>{{ remainingSeconds }}</strong>s
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
