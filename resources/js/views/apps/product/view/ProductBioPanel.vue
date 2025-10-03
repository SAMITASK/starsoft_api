<script setup>
const props = defineProps({
  productData: {
    type: Object,
    required: true,
  },
  filteredStats: {
    type: Object,
    default: null,
  },
});

const displayStats = computed(() => {
  return props.filteredStats || props.productData.stats;
});
</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12" md="12" lg="4">
      <VCard v-if="props.productData">
        <VCardText class="text-center pt-12 pb-6">
          <VAvatar
            rounded="lg"
            :size="80"
            :color="!props.productData.avatar ? 'primary' : undefined"
            :variant="!props.productData.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="props.productData.avatar"
              :src="props.productData.avatar"
            />
            <span v-else class="text-2xl font-weight-medium">
              {{ avatarText(props.productData.product.description) }}
            </span>
          </VAvatar>
          <!-- 👉 User fullName -->
          <h5 class="text-h5 mt-4">
            {{ props.productData.product.description }}
          </h5>
        </VCardText>

        <VCardText class="d-flex justify-center flex-wrap gap-6 pb-6">
          <!-- 👉 Done task -->
          <div class="d-flex align-center me-8">
            <VAvatar
              :size="40"
              rounded
              color="primary"
              variant="tonal"
              class="me-4"
            >
              <VIcon size="24" icon="ri-shopping-cart-line" />
            </VAvatar>

            <div>
              <h5 class="text-h5">
                {{ kFormatter(displayStats.oc) }}
              </h5>
              <span>OC</span>
            </div>
          </div>

          <!-- 👉 Done Project -->
          <div class="d-flex align-center me-4">
            <VAvatar
              :size="44"
              rounded
              color="primary"
              variant="tonal"
              class="me-4"
            >
              <VIcon size="24" icon="ri-briefcase-line" />
            </VAvatar>

            <div>
              <h5 class="text-h5">
                {{ kFormatter(displayStats.ocs) }}
              </h5>
              <span>OS</span>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="12" lg="8">
      <VCard v-if="props.productData">
        <!-- 👉 Details -->
        <VCardText class="pb-6">
          <h5 class="text-h5">Detalles</h5>

          <VDivider class="my-4" />

          <!-- 👉 User Details list -->
          <VList class="card-list">
            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Codigo: </span>
                <span class="text-body-1">
                  {{ props.productData.product.code }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Tipo: </span>
                <span class="text-body-1">
                  {{ props.productData.product.type_name }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Familia: </span>
                <span class="text-body-1">
                  {{ props.productData.product.family_name }}
                </span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Usuario: </span>
                <span class="text-body-1">{{
                  props.productData.product.user?.trim() || "No especificado"
                }}</span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Empresa: </span>
                <span class="text-body-1">{{
                  props.productData.company_name
                }}</span>
              </VListItemTitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium"> Fecha Creación: </span>
                <span class="text-body-1">{{
                  props.productData.product.date
                }}</span>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->
  </VRow>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.current-plan {
  border: 2px solid rgb(var(--v-theme-primary));
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
