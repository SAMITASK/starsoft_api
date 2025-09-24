// composables/useIdleTimeout.js
export function useIdleTimeout({ timeout = 900, warningBefore = 60, onContinue, onLogout }) {
  const isDialogVisible = ref(false)
  let idleTimer = null

  function resetTimer() {
    clearTimeout(idleTimer)
    idleTimer = setTimeout(() => {
      isDialogVisible.value = true
    }, (timeout - warningBefore) * 1000)
  }

  const events = ['click', 'mousemove', 'keydown']

  onMounted(() => {
    events.forEach(evt => window.addEventListener(evt, resetTimer))
    resetTimer()
  })

  onBeforeUnmount(() => {
    clearTimeout(idleTimer)
    events.forEach(evt => window.removeEventListener(evt, resetTimer))
  })

  const handleContinue = async () => {
    if (onContinue) await onContinue()
    resetTimer()
    isDialogVisible.value = false
  }

  const handleLogout = () => {
    if (onLogout) onLogout()
    isDialogVisible.value = false

  }

  return { isDialogVisible, handleContinue, handleLogout }
}
