<script setup lang="ts">
import UserTabSecurity from '@/views/apps/user/view/UserTabSecurity.vue'

const route = useRoute('apps-user-view-id')

const userTab = ref(null)

const tabs = [
  // { icon: 'tabler-users', title: 'Account' },
  { icon: 'tabler-lock', title: 'Security' },
  // { icon: 'tabler-bookmark', title: 'Billing & Plan' },
  // { icon: 'tabler-bell', title: 'Notifications' },
  // { icon: 'tabler-link', title: 'Connections' },
]

const { data: userData } = await useApi<any>(`/apps/users/${route.params.id}`)
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="12"
    >
      <VWindow
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >

        <VWindowItem>
          <UserTabSecurity />
        </VWindowItem>

      </VWindow>
    </VCol>
  </VRow>
</template>
