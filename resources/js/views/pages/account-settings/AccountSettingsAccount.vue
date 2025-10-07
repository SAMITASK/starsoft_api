<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
// 📦 Datos iniciales
const defaultAccountData = {
  avatarImg: avatar1,
  password: "",
};

// 📱 Refs reactivas
const accountDataLocal = ref(structuredClone(defaultAccountData));
const user = ref({});
const companies = ref([]);

const refInputEl = ref(null);
const isLoading = ref(false);
const errorMessage = ref("");

// Snackbar
const isSnackbarVisible = ref(false);
const colorSnackbar = ref("primary");
const messageSnackbar = ref("");

const showSnackbar = ({ message, color = "success" }) => {
  messageSnackbar.value = message;
  colorSnackbar.value = color;
  isSnackbarVisible.value = true;
};

async function fetchData(url, params = {}) {
  isLoading.value = true;
  errorMessage.value = "";
  try {
    const query = new URLSearchParams(params).toString();
    const fullUrl = query ? `${url}?${query}` : url;
    return await $api(fullUrl);
  } catch (err) {
    errorMessage.value = err.message || "Error al cargar datos";
    console.error(err);
    return [];
  } finally {
    isLoading.value = false;
  }
}

onMounted(async () => {
  [user.value, companies.value] = await Promise.all([
    fetchData("/auth/user"),
    fetchData("/users/companies"),
  ]);
});

const resetForm = () => {
  accountDataLocal.value = structuredClone(defaultAccountData);
};

const changeAvatar = (e) => {
  const file = e.target.files?.[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = () => {
    accountDataLocal.value.avatarImg = reader.result;
  };
  reader.readAsDataURL(file);
};

const resetAvatar = () => {
  accountDataLocal.value.avatarImg = defaultAccountData.avatarImg;
};

const saveChanges = async () => {
  const userData = {
    id: user.value.id,
    fullName: user.value.name,
    cargo: user.value.cargo,
    email: user.value.email,
    status: user.value.status,
    company: user.value.company_default,
    password: accountDataLocal.value.password || null,
  };
  try {
    await $api(`/users/update-profile/${userData.id}`, {
      method: "PUT",
      body: userData,
    });

    useCookie("userData").value = {
      ...useCookie("userData").value,
      name: user.value.name,
      email: user.value.email,
      company_default: user.value.company_default,
    };

    showSnackbar({
      message: "✅ Usuario actualizado correctamente",
      color: "success",
    });
  } catch (error) {
    console.error("❌ Error al actualizar usuario:", error);
    showSnackbar({
      message: "Error al actualizar usuario",
      color: "error",
    });
  }
};
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <!-- Avatar + Upload -->
          <div class="d-flex mb-10">
            <VAvatar
              rounded
              size="100"
              class="me-6"
              :image="accountDataLocal.avatarImg"
            />

            <form class="d-flex flex-column justify-center gap-4">
              <div class="d-flex flex-wrap gap-4">
                <VBtn color="primary" @click="refInputEl?.click()">
                  <VIcon icon="ri-upload-cloud-line" class="d-sm-none" />
                  <span class="d-none d-sm-block">Subir Nueva Foto</span>
                </VBtn>

                <input
                  ref="refInputEl"
                  type="file"
                  accept=".jpeg,.png,.jpg,.gif"
                  hidden
                  @change="changeAvatar"
                />

                <VBtn
                  color="error"
                  variant="outlined"
                  @click.prevent="resetAvatar"
                >
                  <VIcon icon="ri-refresh-line" class="d-sm-none" />
                  <span class="d-none d-sm-block">Reset</span>
                </VBtn>
              </div>
              <p class="text-body-1 mb-0">
                Permite JPG, GIF o PNG. Tamaño máximo: 800KB
              </p>
            </form>
          </div>

          <!-- Formulario -->
          <VForm @submit.prevent="saveChanges">
            <VRow>
              <VCol md="6" cols="12">
                <VTextField
                  v-model="user.name"
                  label="Nombres"
                  placeholder="John Doe"
                />
              </VCol>

              <VCol md="6" cols="12">
                <VTextField v-model="user.cargo" label="Cargo" readonly />
              </VCol>

              <VCol md="6" cols="12">
                <VTextField
                  v-model="user.email"
                  label="E-mail"
                  placeholder="example@email.com"
                  type="email"
                />
              </VCol>

              <VCol md="6" cols="12">
                <VTextField
                  v-model="accountDataLocal.password"
                  label="Contraseña"
                  type="password"
                />
              </VCol>

              <VCol md="6" cols="12">
                <VSelect
                  v-model="user.company_default"
                  label="Empresa Principal"
                  :items="companies"
                  item-title="name"
                  item-value="id"
                  placeholder="Selecciona una empresa"
                  :menu-props="{ maxHeight: 200 }"
                />
              </VCol>

              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn color="primary" type="submit">Guardar Cambios</VBtn>
              </VCol>
            </VRow>
          </VForm>

          <!-- Error y Loader -->
          <VAlert v-if="errorMessage" type="error" class="mt-4" dense text>
            {{ errorMessage }}
          </VAlert>
          <VProgressLinear
            v-if="isLoading"
            indeterminate
            color="primary"
            class="mt-2"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    :timeout="3000"
    location="top"
    :color="colorSnackbar"
  >
    {{ messageSnackbar }}
  </VSnackbar>
</template>
