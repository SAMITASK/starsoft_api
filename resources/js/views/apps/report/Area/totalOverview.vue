<script setup>

const props = defineProps({
  nameArea: {
    type: String,
    required: true
  }
})

const statistics = [  
  {
    title: 'Customers',
    stats: '8,458',
    icon: 'ri-user-star-line',
    color: 'primary',
  },
  {
    title: 'Total Profit',
    stats: '$28.5k',
    icon: 'ri-pie-chart-2-line',
    color: 'warning',
  },
  {
    title: 'Transactions',
    stats: '2,450k',
    icon: 'ri-arrow-left-right-line',
    color: 'info',
  },
]

</script>

<template>
  <VCard>
    <VCardItem>
      <h3 class="text-h3">{{nameArea}}</h3>
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
            <VIcon
              size="24"
              :icon="item.icon"
            />
          </VAvatar>

          <div class="d-flex flex-column">
            <h5 class="text-h5">
              {{ item.stats }}
            </h5>
            <div class="text-body-1">
              {{ item.title }}
            </div>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
