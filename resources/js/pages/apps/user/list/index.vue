<script setup>
import AddNewUserDrawer from '@/views/apps/user/list/AddNewUserDrawer.vue'

const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

const updateOptions = options => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: 'Nombre Usuario',
    key: 'name',
  },
  {
    title: 'Correo',
    key: 'email',
  },
  {
    title: 'Cargo',
    key: 'role',
  },
  {
    title: 'Empresas',
    key: 'companies',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: usersData,
  execute: fetchUsers,
} = await useApi(createUrl('users/list', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const users = computed(() => usersData.value.users)
const totalUsers = computed(() => usersData.value.totalUsers)

const resolveUserRoleVariant = role => {
  const roleLowerCase = role.toLowerCase()
  if (roleLowerCase == 'asistente')
    return {
      color: 'error',
      icon: 'ri-computer-line',
    }
  if (roleLowerCase == 'controller')
    return {
      color: 'info',
      icon: 'ri-pie-chart-line',
    }
  if (roleLowerCase == 'soporte')
    return {
      color: 'warning',
      icon: 'ri-edit-box-line',
    }
  if (roleLowerCase == 'gerente')
    return {
      color: 'primary',
      icon: 'ri-vip-crown-line',
    }
  
  return {
    color: 'success',
    icon: 'ri-user-line',
  }
}

const resolveUserStatusVariant = stat => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'
  
  return 'primary'
}

const isAddNewUserDrawerVisible = ref(false)
const editingUser = ref(null)

const addNewUser = async userData => {

  // userListStore.addUser(userData)
  await $api('users/add', {
    method: 'POST',
    body: userData,
  })

  // Refetch User
  fetchUsers()
}

const openCreateModal = () => {
  editingUser.value = null
  isAddNewUserDrawerVisible.value = true
}

const editUser = (user) => {
  editingUser.value = user
  isAddNewUserDrawerVisible.value = true
}

const updateUser = async userData => {
  console.log('Updating user...', userData)

  await $api(`users/update/${userData.id}`, {
    method: 'PUT',
    body: userData,
  })
  fetchUsers()
}

const handleUserData = (userData) => {
  if (userData.id) {
    updateUser(userData)
  } else {
    addNewUser(userData)
  }
}

</script>

<template>
  <section>

    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap gap-4 align-center">

        <VSpacer />
        <div class="d-flex align-center gap-4 flex-wrap">
          <!-- 👉 Search  -->
          <div class="app-user-search-filter">
            <VTextField
              v-model="searchQuery"
              placeholder="Buscar Usuario"
              density="compact"
            />
          </div>
          <VBtn @click="openCreateModal()">
            Nuevo Usuario
          </VBtn>
        </div>
      </VCardText>

      <VDataTableServer
        v-model:model-value="selectedRows"
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="users"
        item-value="id"
        :items-length="totalUsers"
        :headers="headers"
        show-select
        class="text-no-wrap rounded-0"
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? resolveUserRoleVariant(item.role).color : undefined"
              class="me-3"
            >
              <VImg
                v-if="item.avatar"
                :src="item.avatar"
              />
              <span v-else>{{ avatarText(item.name) }}</span>
            </VAvatar>

            <div class="d-flex flex-column">
              <RouterLink
                :to="{ name: 'apps-user-view-id', params: { id: item.id } }"
                class="text-link text-base font-weight-medium"
              >
                {{ item.name }}
              </RouterLink>

            </div>
          </div>
        </template>
        <!-- Role -->
        <template #item.role="{ item }">
          <div class="d-flex gap-2">
            <VIcon
              :icon="resolveUserRoleVariant(item.role).icon"
              :color="resolveUserRoleVariant(item.role).color"
              size="22"
            />
            <span class="text-capitalize text-high-emphasis">{{ item.role }}</span>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="resolveUserStatusVariant(item.status)"
            size="small"
            class="text-capitalize"
          >
            {{ item.status }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">

          <IconBtn
            size="small"
             @click="editUser(item)"
          >
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>

        </template>

        <!-- Pagination -->
        <template #bottom>
          <VDivider />

          <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
            <div class="d-flex align-center gap-x-2 text-medium-emphasis text-base">
              Registros por página:
              <VSelect
                v-model="itemsPerPage"
                class="per-page-select"
                variant="plain"
                :items="[10, 20, 25, 50, 100]"
              />
            </div>

            <p class="d-flex align-center text-base text-high-emphasis me-2 mb-0">
              {{ paginationMeta({ page, itemsPerPage }, totalUsers) }}
            </p>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-left-s-line"
                variant="text"
                density="comfortable"
                color="high-emphasis"
                :disabled="page <= 1"
                @click="page <= 1 ? page = 1 : page--"
              />

              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-right-s-line"
                density="comfortable"
                variant="text"
                color="high-emphasis"
                :disabled="page >= Math.ceil(totalUsers / itemsPerPage)"
                @click="page >= Math.ceil(totalUsers / itemsPerPage) ? page = Math.ceil(totalUsers / itemsPerPage) : page++ "
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>
  <AddNewUserDrawer
    v-model:is-drawer-open="isAddNewUserDrawerVisible"
    :editing-user="editingUser"
    @user-data="handleUserData"
    @user-updated="handleUserData"
  />
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
