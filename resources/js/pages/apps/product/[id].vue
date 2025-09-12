<script setup>
import ProductBioPanel from '@/views/apps/product/view/ProductBioPanel.vue'
import ProductTabOverview from '@/views/apps/product/view/ProductTabOverview.vue'

const route = useRoute('apps-supplier-id')
const userTab = ref(null)



const company = history.state.company;
const product = route.params.id;

const { data: productData } = await useApi(`products/view/${product}?company=${company}`)

</script>

<template>
  <VRow v-if="productData">
    <VCol
      cols="12"
      md="12"
      lg="12"
    >
      <ProductBioPanel :product-data="productData" />
    </VCol>

    <VCol
      cols="12"
      md="12"
      lg="12"
    >
      <VWindow
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
        <ProductTabOverview :company="company" :product="product" />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Product with ID  {{ route.params.id }} not found!
    </VAlert>
  </div>
</template>
