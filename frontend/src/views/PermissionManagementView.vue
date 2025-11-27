<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Page header -->
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="sm:flex sm:items-baseline sm:justify-between">
          <div>
            <h1 class="text-base/7 font-semibold text-gray-900 dark:text-white">
              Permission management
            </h1>
            <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-300">
              Assign roles and fine‑grained permissions to people in your workspace.
            </p>
          </div>
          <p
            v-if="metaUsers && metaUsers.total"
            class="mt-3 text-xs/5 text-gray-500 sm:mt-0 dark:text-gray-400"
          >
            {{ metaUsers.total }} user{{ metaUsers.total === 1 ? '' : 's' }} in this workspace
          </p>
        </div>

        <p v-if="error && !selectedUser" class="mt-3 text-sm/6 text-red-400">
          {{ error }}
        </p>
      </header>

      <!-- Main layout -->
      <div
        class="grid gap-6 lg:grid-cols-[minmax(0,260px)_minmax(0,1fr)] xl:grid-cols-[minmax(0,280px)_minmax(0,1fr)]"
      >
        <!-- People sidebar -->
        <section
          class="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
        >
          <div class="flex items-center justify-between gap-x-3">
            <div>
              <h2 class="text-sm/6 font-semibold text-gray-900 dark:text-white">
                People
              </h2>
              <p class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                Choose a person to edit their roles and direct permissions.
              </p>
            </div>
          </div>

          <!-- Search -->
          <div class="mt-4">
            <label
              for="user-search"
              class="sr-only"
            >
              Search users
            </label>
            <div class="relative">
              <input
                id="user-search"
                v-model="userSearch"
                type="search"
                placeholder="Search by name or email"
                class="block w-full rounded-md bg-white px-3 py-1.5 text-sm/6 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 dark:bg-gray-900 dark:text-white dark:outline-white/15 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
              />
            </div>
          </div>

          <!-- Users list -->
          <div class="mt-4">
            <div
              v-if="loading && !users.length"
              class="py-6 text-center text-xs/5 text-gray-500 dark:text-gray-400"
            >
              Loading users…
            </div>

            <div
              v-else-if="!filteredUsers.length"
              class="py-6 text-center text-xs/5 text-gray-500 dark:text-gray-400"
            >
              No users found.
            </div>

            <ul
              v-else
              role="list"
              class="-mx-2 max-h-[460px] space-y-1 overflow-y-auto pt-1"
            >
              <li
                v-for="user in filteredUsers"
                :key="user.id"
              >
                <button
                  type="button"
                  :class="[
                    user.id === selectedUserId
                      ? 'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white'
                      : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white',
                    'flex w-full items-center justify-between gap-x-3 rounded-md px-2.5 py-2 text-left text-sm/6',
                  ]"
                  @click="onSelectUser(user.id)"
                >
                  <div class="min-w-0">
                    <p class="truncate font-medium">
                      {{ user.name || 'Unknown user' }}
                    </p>
                    <p class="truncate text-xs/5 text-gray-500 dark:text-gray-400">
                      {{ user.email || 'No email' }}
                    </p>
                  </div>
                  <div class="flex shrink-0 flex-col items-end gap-y-1">
                    <span
                      v-if="user.id === selectedUserId && (selectedRoleIds.length || selectedPermissionIds.length)"
                      class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/40"
                    >
                      <span v-if="selectedRoleIds.length">
                        {{ selectedRoleIds.length }} role{{ selectedRoleIds.length === 1 ? '' : 's' }}
                      </span>
                      <span v-if="selectedRoleIds.length && selectedPermissionIds.length" class="mx-1 text-indigo-400/70">
                        •
                      </span>
                      <span v-if="selectedPermissionIds.length">
                        {{ selectedPermissionIds.length }} direct
                      </span>
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-white/10"
                    >
                      No overrides
                    </span>
                  </div>
                </button>
              </li>
            </ul>
          </div>
        </section>

        <!-- Permissions editor -->
        <section class="space-y-4">
          <!-- Empty state -->
          <div
            v-if="!selectedUser && !loading"
            class="flex h-full items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center text-sm/6 text-gray-500 dark:border-white/15 dark:bg-gray-900/40 dark:text-gray-400"
          >
            <div class="max-w-sm space-y-3">
              <p class="font-semibold text-gray-900 dark:text-white">
                Select a user to begin
              </p>
              <p>
                Choose a person from the list on the left to view and edit their roles and direct permissions.
              </p>
            </div>
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <!-- User summary -->
            <div
              class="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
            >
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                  <p class="text-sm/6 font-semibold text-gray-900 dark:text-white">
                    {{ selectedUser?.name || 'Unknown user' }}
                  </p>
                  <p class="mt-0.5 truncate text-xs/5 text-gray-500 dark:text-gray-400">
                    {{ selectedUser?.email || 'No email address' }}
                  </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-medium">
                  <span
                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10"
                  >
                    {{ selectedRoleIds.length }} role{{ selectedRoleIds.length === 1 ? '' : 's' }}
                  </span>
                  <span
                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10"
                  >
                    {{ selectedPermissionIds.length }} direct permission{{ selectedPermissionIds.length === 1 ? '' : 's' }}
                  </span>
                </div>
              </div>

              <p v-if="error" class="mt-3 text-xs/5 text-red-400">
                {{ error }}
              </p>
              <p v-if="success" class="mt-3 text-xs/5 text-emerald-400">
                {{ success }}
              </p>
            </div>

            <!-- Role & permission columns -->
            <div class="grid gap-4 md:grid-cols-2">
              <!-- Roles -->
              <div
                class="flex flex-col rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
              >
                <div class="flex items-center justify-between gap-x-3">
                  <div>
                    <h2 class="text-sm/6 font-semibold text-gray-900 dark:text-white">
                      Roles
                    </h2>
                    <p class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                      Assign one or more roles that bundle common permissions.
                    </p>
                  </div>
                  <button
                    type="button"
                    class="text-xs/5 font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    @click="selectedRoleIds = roles.map((r) => r.id)"
                    v-if="roles.length"
                  >
                    Select all
                  </button>
                </div>

                <div class="mt-3 flex-1 overflow-y-auto space-y-4">
                  <div
                    v-if="!platformRoles.length && !companyRoles.length"
                    class="py-4 text-xs/5 text-gray-500 dark:text-gray-400"
                  >
                    No roles configured.
                  </div>

                  <div v-if="companyRoles.length" class="space-y-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                      Company roles
                    </p>
                    <ul class="space-y-1">
                      <li
                        v-for="role in companyRoles"
                        :key="role.id"
                      >
                        <label
                          class="flex cursor-pointer items-start gap-x-3 rounded-md px-2 py-1 hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                          <input
                            v-model="selectedRoleIds"
                            :value="role.id"
                            type="checkbox"
                            class="mt-1 size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-gray-900"
                          />
                          <div class="min-w-0">
                            <p class="text-sm/6 font-medium text-gray-900 dark:text-gray-100">
                              {{ role.name }}
                            </p>
                            <p
                              v-if="role.description"
                              class="text-xs/5 text-gray-500 dark:text-gray-400"
                            >
                              {{ role.description }}
                            </p>
                          </div>
                          <span class="text-[11px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                            Company
                          </span>
                        </label>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Direct permissions -->
              <div
                class="flex flex-col rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
              >
                <div class="flex items-center justify-between gap-x-3">
                  <div>
                    <h2 class="text-sm/6 font-semibold text-gray-900 dark:text-white">
                      Direct permissions
                    </h2>
                    <p class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                      Fine‑tune additional permissions on top of the user&rsquo;s roles.
                    </p>
                  </div>
                  <button
                    type="button"
                    class="text-xs/5 font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    @click="selectedPermissionIds = []"
                    v-if="permissions.length"
                  >
                    Clear all
                  </button>
                </div>

                <div class="mt-3 flex-1 overflow-y-auto space-y-4">
                  <div
                    v-if="!permissions.length"
                    class="py-4 text-xs/5 text-gray-500 dark:text-gray-400"
                  >
                    No direct permissions available.
                  </div>
                  <div
                    v-else
                    v-for="group in groupedPermissions"
                    :key="group.key"
                    class="space-y-2 rounded-lg border border-gray-100 p-3 dark:border-white/10"
                  >
                    <div class="flex items-center justify-between">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ group.label }}
                      </p>
                      <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ group.items.length }} perm</span>
                    </div>
                    <ul class="space-y-1">
                      <li
                        v-for="permission in group.items"
                        :key="permission.id"
                      >
                        <label
                          class="flex cursor-pointer items-start gap-x-3 rounded-md px-2 py-1 hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                          <input
                            v-model="selectedPermissionIds"
                            :value="permission.id"
                            type="checkbox"
                            class="mt-1 size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-gray-900"
                          />
                          <div class="min-w-0">
                            <p
                              class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-800 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:ring-white/10"
                            >
                              {{ permission.display || permission.name }}
                            </p>
                            <p
                              v-if="permission.description"
                              class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400"
                            >
                              {{ permission.description }}
                            </p>
                          </div>
                        </label>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Save bar -->
            <div
              class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
            >
              <div class="text-xs/5 text-gray-500 dark:text-gray-400">
                Editing permissions for
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                  {{ currentUserLabel }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <button
                  type="button"
                  class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10 dark:hover:bg-white/5"
                  :disabled="saving || !selectedUserId"
                  @click="reloadCurrentUser"
                >
                  Reset
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
                  :disabled="saving || !selectedUserId"
                  @click="handleSave"
                >
                  <span v-if="saving">Saving…</span>
                  <span v-else>Save changes</span>
                </button>
              </div>
            </div>
          </div>

          <div
            v-if="loading && selectedUser"
            class="text-xs/5 text-gray-500 dark:text-gray-400"
          >
            Loading permissions…
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import {
  fetchPermissionMeta,
  fetchUserPermissions,
  fetchUsersForAdmin,
  updateUserPermissions,
} from '../api'

const users = ref([])
const metaUsers = ref(null)
const roles = ref([])
const permissions = ref([])

const selectedUserId = ref(null)
const selectedRoleIds = ref([])
const selectedPermissionIds = ref([])

const userSearch = ref('')

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const success = ref(null)

const selectedUser = computed(() =>
  users.value.find((u) => u.id === selectedUserId.value) || null,
)

const currentUserLabel = computed(() => {
  if (!selectedUser.value) return ''
  return `${selectedUser.value.name} (${selectedUser.value.email})`
})

const filteredUsers = computed(() => {
  if (!userSearch.value.trim()) return users.value

  const term = userSearch.value.trim().toLowerCase()
  return users.value.filter((user) => {
    const name = (user.name || '').toLowerCase()
    const email = (user.email || '').toLowerCase()
    return name.includes(term) || email.includes(term)
  })
})

const platformRoles = computed(() => roles.value.filter((r) => r.role_scope === 'platform'))
const companyRoles = computed(() => roles.value.filter((r) => r.role_scope === 'company'))

const groupedPermissions = computed(() => {
  const groups = {}
  const friendly = (name) => name.replace(/\./g, ' ').replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase())

  permissions.value.forEach((p) => {
    const name = p.name || ''
    const key = name.includes('.') ? name.split('.')[0] : 'general'
    if (!groups[key]) {
      groups[key] = { key, label: key === 'general' ? 'General' : friendly(key), items: [] }
    }
    groups[key].items.push({
      ...p,
      display: friendly(name),
    })
  })
  return Object.values(groups)
    .map((g) => ({
      ...g,
      items: g.items.sort((a, b) => a.display.localeCompare(b.display)),
    }))
    .sort((a, b) => a.label.localeCompare(b.label))
})

const loadInitial = async () => {
  loading.value = true
  error.value = null
  try {
    const [usersRes, metaRes] = await Promise.all([
      fetchUsersForAdmin({ per_page: 100 }),
      fetchPermissionMeta(),
    ])

    const userPayload = usersRes.data
    users.value = userPayload.data || []
    metaUsers.value = {
      current_page: userPayload.current_page,
      last_page: userPayload.last_page,
      total: userPayload.total,
      per_page: userPayload.per_page,
    }

    const metaPayload = metaRes.data
    roles.value = metaPayload.roles || []
    permissions.value = metaPayload.permissions || []
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load permission data.'
  } finally {
    loading.value = false
  }
}

const loadUserDetails = async (userId) => {
  if (!userId) return

  loading.value = true
  error.value = null
  success.value = null

  try {
    const { data } = await fetchUserPermissions(userId)
    selectedRoleIds.value = (data.roles || [])
      .filter((r) => r.role_scope === 'company')
      .map((r) => r.id)
    selectedPermissionIds.value = (data.permissions || []).map((p) => p.id)
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load user roles and permissions.'
  } finally {
    loading.value = false
  }
}

const onSelectUser = async (userId) => {
  if (selectedUserId.value === userId) return
  selectedUserId.value = userId
  await loadUserDetails(userId)
}

const reloadCurrentUser = async () => {
  if (!selectedUserId.value) return
  await loadUserDetails(selectedUserId.value)
}

const handleSave = async () => {
  if (!selectedUserId.value) return

  saving.value = true
  error.value = null
  success.value = null

  try {
    // Only allow company roles to be assigned from this screen
    const companyOnlyRoles = selectedRoleIds.value.filter((id) => {
      const role = roles.value.find((r) => r.id === id)
      return role?.role_scope === 'company'
    })

    await updateUserPermissions(selectedUserId.value, {
      role_ids: companyOnlyRoles,
      permission_ids: selectedPermissionIds.value,
    })
    success.value = 'Changes saved.'
  } catch (e) {
    console.error(e)
    error.value = 'Failed to save changes.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadInitial()
})
</script>
