// composables/useAuth.js
export function useAuth() {
  const router = useRouter()
  const userData = ref(null)
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

    useCookie('userData').value = null 
    useCookie('accessToken').value = null

      await router.push('/login')
    }
  }

  const keepAlive = async () => {
    try {
      await $api('/keep-alive', { method: 'POST' })
      console.log('Sesión renovada ✅')
    } catch (error) {
      console.error(error)
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
