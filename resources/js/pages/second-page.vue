<script setup>
import { ref, onMounted } from 'vue'

const order = ref(null)
const error = ref(null)

onMounted(async () => {
  try {
    const res = await fetch('api/oc')

    // Si la respuesta no es OK, intenta leer el error que vino del backend
    if (!res.ok) {
      const errorData = await res.json().catch(() => ({})) // por si no es JSON válido
      console.error('Error de backend:', errorData)

      throw new Error(errorData.message || 'Error desconocido del servidor')
    }

    // Si todo va bien, asigna la orden
    order.value = await res.json()
  } catch (err) {
    console.error('Error en la petición:', err)
    error.value = err.message
  }
})
</script>

<template>
  <div>
    <VCard title="Orden de Compra">
      <VCardText v-if="error" class="text-error">
        {{ error }}
      </VCardText>
      <VCardText v-else-if="order">
        <pre>{{ JSON.stringify(order, null, 2) }}</pre>
      </VCardText>
      <VCardText v-else>
        Cargando...
      </VCardText>
    </VCard>
  </div>
</template>
