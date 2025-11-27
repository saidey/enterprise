<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchPermissionMeta, fetchRoles, fetchRole, updateRole, createRole } from '../api'

const roles = ref([])
const permissions = ref([])
const selectedRole = ref(null)
const selectedPermissionIds = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')
const nameInput = ref('')
const descInput = ref('')
const createMode = ref(false)
const newRoleName = ref('')
const newRoleDesc = ref('')

const groupedPermissions = computed(() => {
  const groups = {}
  permissions.value.forEach((p) => {
    const key = (p.name || '').split('.')[0] || 'general'
    if (!groups[key]) {
      groups[key] = { key, label: key.toUpperCase(), items: [] }
    }
    groups[key].items.push(p)
  })
  return Object.values(groups).sort((a, b) => a.label.localeCompare(b.label))
})

async function loadMeta() {
  loading.value = true
  error.value = ''
  try {
    const [metaRes, rolesRes] = await Promise.all([fetchPermissionMeta(), fetchRoles()])
    permissions.value = metaRes.data.permissions || []
    roles.value = rolesRes.data.data || []
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load roles.'
  } finally {
    loading.value = false
  }
}

async function selectRole(role) {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const { data } = await fetchRole(role.id)
    selectedRole.value = data.data || data
    selectedPermissionIds.value = (selectedRole.value.permissions || []).map((p) => p.id)
    nameInput.value = selectedRole.value.name
    descInput.value = selectedRole.value.description || ''
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load role.'
  } finally {
    loading.value = false
  }
}

async function saveRole() {
  if (!selectedRole.value) return
  success.value = ''
  error.value = ''
  loading.value = true
  try {
    await updateRole(selectedRole.value.id, {
      name: nameInput.value,
      description: descInput.value,
      permission_ids: selectedPermissionIds.value,
    })
    success.value = 'Role permissions updated.'
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to update role.'
  } finally {
    loading.value = false
  }
}

async function createRoleAction() {
  if (!newRoleName.value.trim()) {
    error.value = 'Role name is required.'
    return
  }
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    await createRole({
      name: newRoleName.value,
      description: newRoleDesc.value,
      role_scope: 'company',
      permission_ids: selectedPermissionIds.value,
    })
    newRoleName.value = ''
    newRoleDesc.value = ''
    createMode.value = false
    success.value = 'Role created.'
    await loadMeta()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to create role.'
  } finally {
    loading.value = false
  }
}

onMounted(loadMeta)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Admin / Roles</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Role management</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Create roles and assign permissions.</p>
          </div>
        </div>
      </header>

      <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-if="success" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ success }}
      </div>

      <div class="grid gap-6 md:grid-cols-[300px,1fr]">
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Roles</h2>
            <button
              type="button"
              class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              @click="createMode = !createMode"
            >
              {{ createMode ? 'Close' : 'New role' }}
            </button>
          </div>
          <div class="mt-3 space-y-1">
            <button
              v-for="role in roles"
              :key="role.id"
              type="button"
              class="w-full rounded-md px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/5"
              :class="role.id === selectedRole?.id ? 'bg-gray-100 dark:bg-white/10' : ''"
              @click="selectRole(role)"
            >
              <div class="font-semibold text-gray-900 dark:text-white">{{ role.name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ role.role_scope || '—' }}</div>
            </button>
          </div>
          <div v-if="createMode" class="mt-4 space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Role name</label>
            <input v-model="newRoleName" type="text" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
            <textarea v-model="newRoleDesc" rows="2" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
            <button
              type="button"
              class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              @click="createRoleAction"
            >
              Save role
            </button>
          </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div v-if="!selectedRole" class="text-sm text-gray-500 dark:text-gray-400">Select a role to edit permissions.</div>
          <div v-else class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Role name</label>
                <input v-model="nameInput" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
                <textarea v-model="descInput" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Direct permissions for this role</h3>
                <button
                  type="button"
                  class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                  @click="selectedPermissionIds = []"
                >
                  Clear
                </button>
              </div>
              <div class="grid gap-3 md:grid-cols-2">
                <div
                  v-for="group in groupedPermissions"
                  :key="group.key"
                  class="rounded-lg border border-gray-100 p-3 dark:border-white/10"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                      {{ group.label }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ group.items.length }}</span>
                  </div>
                  <ul class="mt-2 space-y-1">
                    <li
                      v-for="permission in group.items"
                      :key="permission.id"
                    >
                      <label class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1 hover:bg-gray-50 dark:hover:bg-white/5">
                        <input
                          v-model="selectedPermissionIds"
                          :value="permission.id"
                          type="checkbox"
                          class="mt-1 size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-gray-900"
                        />
                        <div class="min-w-0">
                          <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ permission.name }}</p>
                          <p v-if="permission.description" class="text-[11px] text-gray-500 dark:text-gray-400">
                            {{ permission.description }}
                          </p>
                        </div>
                      </label>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="saveRole"
              >
                Save role
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>
