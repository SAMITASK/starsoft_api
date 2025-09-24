<script setup>
import { useTheme } from 'vuetify'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import {
  initConfigStore,
  useConfigStore,
} from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import SessionDialog from '@/components/dialogs/SessionTimeoutDialog.vue'
import { useIdleTimeout } from '@/composables/useIdleTimeout'
import { useAuth } from '@/composables/useAuth'
import { useRoute } from 'vue-router'

const { global } = useTheme()
const route = useRoute()
// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()

const { userData, logout, keepAlive } = useAuth()
const idleTimeout = Number(import.meta.env.VITE_SESSION_IDLE_TIMEOUT) || 900
const warningBefore = Number(import.meta.env.VITE_SESSION_WARNING_BEFORE) || 60

const handleSessionContinue = async () => {
  await keepAlive()
}
const handleSessionLogout = async () => {
  await logout()
}

const { isDialogVisible, handleContinue, handleLogout } = useIdleTimeout({
  timeout: idleTimeout,
  warningBefore,
  onContinue: handleSessionContinue,
  onLogout: handleSessionLogout
})

</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      
      <SessionDialog
        v-if="route.name !== 'login'"
        v-model:isDialogVisible="isDialogVisible"
        :onContinue="handleContinue"
        :onLogout="handleLogout"
        :warning-seconds="warningBefore"
      />

      <ScrollToTop />
    </VApp>
  </VLocaleProvider>
</template>
