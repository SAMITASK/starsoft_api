// composables/useAuth.js
export function useAuth() {
  const router = useRouter()
  const userDataCookie = useCookie('userData')
  const userData = computed(() => userDataCookie.value)
  const accessToken = useCookie('accessToken')

  // Verificar si el usuario está autenticado
  const isLoggedIn = computed(() => {
    return !!(accessToken.value && userData.value)
  })

  const logout = async () => {
    try {
      await $api('/auth/logout', { method: 'POST' })
    } catch (error) {
      console.error(error)
    } finally {
      userDataCookie.value = null
      accessToken.value = null

      await router.push('/login')
    }
  }

  const keepAlive = async () => {
    try {
      await $api('/keep-alive', { method: 'POST' })

      return true
    } catch (error) {
      console.error(error)

      if (error?.response?.status === 401) {
        await logout()
      }

      return false
    }
  }

  return { 
    logout, 
    keepAlive, 
    userData, 
    accessToken, 
    isLoggedIn // ← Exportar la verificación
  }
}
