// composables/useIdleTimeout.js
export function useIdleTimeout({
  timeout = 900,
  warningBefore = 60,
  onContinue,
  onLogout,
  isEnabled = true,
}) {
  const isDialogVisible = ref(false)
  const canUseBrowserApis = typeof window !== 'undefined' && typeof localStorage !== 'undefined'
  const normalizedTimeout = Math.max(Number(timeout) || 0, 0)
  const normalizedWarningBefore = Math.min(
    Math.max(Number(warningBefore) || 0, 0),
    normalizedTimeout,
  )
  const remainingSeconds = ref(normalizedWarningBefore)
  const storageKey = 'starcheck:last-activity-at'

  let warningTimer = null
  let countdownTimer = null

  const isSessionTrackingEnabled = () => {
    return typeof isEnabled === 'object'
      ? Boolean(isEnabled.value)
      : Boolean(isEnabled)
  }

  const clearWarningTimer = () => {
    if (warningTimer) {
      clearTimeout(warningTimer)
      warningTimer = null
    }
  }

  const clearCountdownTimer = () => {
    if (countdownTimer) {
      clearInterval(countdownTimer)
      countdownTimer = null
    }
  }

  const clearTimers = () => {
    clearWarningTimer()
    clearCountdownTimer()
  }

  const setLastActivityAt = timestamp => {
    if (!canUseBrowserApis) {
      return
    }

    localStorage.setItem(storageKey, String(timestamp))
  }

  const getLastActivityAt = () => {
    if (!canUseBrowserApis) {
      return Date.now()
    }

    const parsed = Number(localStorage.getItem(storageKey))

    return Number.isFinite(parsed) && parsed > 0 ? parsed : Date.now()
  }

  const hideDialog = () => {
    isDialogVisible.value = false
    clearCountdownTimer()
  }

  const handleLogout = async () => {
    clearTimers()
    hideDialog()

    if (canUseBrowserApis) {
      localStorage.removeItem(storageKey)
    }

    if (onLogout) {
      await onLogout()
    }
  }

  const startCountdown = initialSeconds => {
    clearCountdownTimer()

    remainingSeconds.value = Math.max(Math.ceil(initialSeconds), 0)

    if (remainingSeconds.value <= 0) {
      handleLogout()
      return
    }

    countdownTimer = setInterval(() => {
      remainingSeconds.value = Math.max(remainingSeconds.value - 1, 0)

      if (remainingSeconds.value <= 0) {
        handleLogout()
      }
    }, 1000)
  }

  const showWarning = secondsLeft => {
    clearWarningTimer()
    isDialogVisible.value = true
    startCountdown(secondsLeft)
  }

  const syncTimersWithCurrentActivity = () => {
    clearTimers()

    if (!isSessionTrackingEnabled()) {
      hideDialog()
      return
    }

    const now = Date.now()
    const lastActivityAt = getLastActivityAt()
    const idleSeconds = Math.floor((now - lastActivityAt) / 1000)
    const warningStartsAt = Math.max(normalizedTimeout - normalizedWarningBefore, 0)

    if (idleSeconds >= normalizedTimeout) {
      handleLogout()
      return
    }

    if (idleSeconds >= warningStartsAt) {
      showWarning(normalizedTimeout - idleSeconds)
      return
    }

    hideDialog()

    warningTimer = setTimeout(() => {
      showWarning(normalizedWarningBefore)
    }, (warningStartsAt - idleSeconds) * 1000)
  }

  const registerActivity = () => {
    if (!isSessionTrackingEnabled() || isDialogVisible.value) {
      return
    }

    setLastActivityAt(Date.now())
    syncTimersWithCurrentActivity()
  }

  const handleVisibilityChange = () => {
    if (canUseBrowserApis && document.visibilityState === 'visible') {
      syncTimersWithCurrentActivity()
    }
  }

  const events = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart']

  onMounted(() => {
    if (!canUseBrowserApis) {
      return
    }

    events.forEach(evt => window.addEventListener(evt, registerActivity, { passive: true }))
    window.addEventListener('focus', syncTimersWithCurrentActivity)
    document.addEventListener('visibilitychange', handleVisibilityChange)
  })

  onBeforeUnmount(() => {
    clearTimers()

    if (!canUseBrowserApis) {
      return
    }

    events.forEach(evt => window.removeEventListener(evt, registerActivity))
    window.removeEventListener('focus', syncTimersWithCurrentActivity)
    document.removeEventListener('visibilitychange', handleVisibilityChange)
  })

  watch(
    () => isSessionTrackingEnabled(),
    enabled => {
      if (!canUseBrowserApis) {
        return
      }

      clearTimers()

      if (!enabled) {
        hideDialog()
        return
      }

      if (!localStorage.getItem(storageKey)) {
        setLastActivityAt(Date.now())
      }

      syncTimersWithCurrentActivity()
    },
    { immediate: true },
  )

  const handleContinue = async () => {
    clearTimers()
    hideDialog()
    setLastActivityAt(Date.now())

    if (onContinue) {
      await onContinue()
    }

    syncTimersWithCurrentActivity()
  }

  return { isDialogVisible, remainingSeconds, handleContinue, handleLogout }
}
