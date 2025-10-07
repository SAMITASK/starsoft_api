<script setup>

const props = defineProps({
  nameArea: String,
  products: Number,
  suppliers: Number,
  money: Number,
})

const statistics = computed(() => {
  const formatCurrency = value =>
    value?.toLocaleString("es-PE", { style: "currency", currency: "PEN" }) ?? "S/ 0.00"

  return [
    {
      title: "Monto Total",
      stats: formatCurrency(props.money),
      icon: "ri-wallet-3-line",
      color: "primary",
    },
    {
      title: "Cantidad Proveedores",
      stats: props.suppliers ?? 0,
      icon: "ri-store-3-line",
      color: "warning",
    },
    {
      title: "Cantidad Productos",
      stats: props.products ?? 0,
      icon: "ri-shopping-cart-2-line",
      color: "info",
    },
  ]
})
</script>

<template>
  <VCard>
    <VCardItem>
      <h3 class="text-h3">{{ props.nameArea }}</h3>
    </VCardItem>

    <VCardText>
      <div class="d-flex justify-space-between flex-column flex-sm-row gap-4 flex-wrap">
        <div
          v-for="item in statistics"
          :key="item.title"
          class="d-flex align-center"
        >
          <VAvatar
            :color="item.color"
            rounded
            variant="tonal"
            size="80"
            class="me-3"
          >
            <VIcon size="30" :icon="item.icon" />
          </VAvatar>

          <div>
            <h5 class="text-h5 mb-1">{{ item.stats }}</h5>
            <span class="text-body-1">{{ item.title }}</span>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
