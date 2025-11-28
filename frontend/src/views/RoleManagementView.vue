<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchRoles, fetchRole, fetchPermissionMeta, updateRole, createRole } from '../api'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { ChevronUpDownIcon } from '@heroicons/vue/16/solid'
import { CheckIcon } from '@heroicons/vue/20/solid'

const roles = ref([])
const permissions = ref([])
const selectedRoleId = ref('')
const selectedRole = computed(() => roles.value.find((r) => r.id === selectedRoleId.value) || null)
const selectedPermissionIds = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')
const isCreating = ref(false)
const newRole = ref({ name: '', description: '' })

const groupedPermissions = computed(() => {
  const groups = {}
  const friendly = (name) => name.replace(/\./g, ' ').replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase())
  permissions.value.forEach((p) => {
    const key = (p.name || '').split('.')[0] || 'General'
    if (!groups[key]) groups[key] = { key, label: friendly(key), items: [] }
    groups[key].items.push({ ...p, display: friendly(p.name || '') })
  })
  return Object.values(groups).map((g) => ({
    ...g,
    items: g.items.sort((a, b) => a.display.localeCompare(b.display)),
  }))
})

async function loadMeta() {
  loading.value = true
  error.value = ''
  try {
    const [rolesRes, metaRes] = await Promise.all([fetchRoles(), fetchPermissionMeta()])
    roles.value = rolesRes.data.data || []
    permissions.value = metaRes.data.permissions || []
    if (!selectedRoleId.value && roles.value.length) {
      selectedRoleId.value = roles.value[0].id
      await loadRole(selectedRoleId.value)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load roles.'
  } finally {
    loading.value = false
  }
}

async function loadRole(id) {
  if (!id) return
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const { data } = await fetchRole(id)
    const role = data.data || data
    selectedPermissionIds.value = (role.permissions || [])
      .filter(Boolean)
      .map((p) => p.id)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load role.'
  } finally {
    loading.value = false
  }
}

async function saveRole() {
  if (!selectedRole.value) return
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    await updateRole(selectedRole.value.id, {
      name: selectedRole.value.name,
      description: selectedRole.value.description,
      permission_ids: selectedPermissionIds.value,
    })
    success.value = 'Role updated.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update role.'
  } finally {
    loading.value = false
  }
}

async function createRoleAction() {
  if (!newRole.value.name.trim()) {
    error.value = 'Role name is required.'
    return
  }
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    await createRole({
      name: newRole.value.name,
      description: newRole.value.description,
      role_scope: 'company',
      permission_ids: selectedPermissionIds.value,
    })
    newRole.value = { name: '', description: '' }
    isCreating.value = false
    await loadMeta()
    success.value = 'Role created.'
  } catch (err) {
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
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Admin / Roles</p>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Role management</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Select a role, adjust permissions, or create a new one.</p>
          </div>
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            @click="isCreating = !isCreating"
          >
            {{ isCreating ? 'Close' : 'New role' }}
          </button>
        </div>
      </header>

      <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-if="success" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ success }}
      </div>

      <div class="grid gap-4 md:grid-cols-[280px,1fr]">
        <aside class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <Listbox v-model="selectedRoleId" @update:modelValue="loadRole">
            <ListboxLabel class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Select role
            </ListboxLabel>
            <div class="relative mt-2">
              <ListboxButton
                class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-gray-800/50 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
              >
                <span class="col-start-1 row-start-1 flex items-center gap-3 pr-6">
                  <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                    {{ selectedRole?.name?.[0] || '?' }}
                  </span>
                  <span class="block truncate">{{ selectedRole?.name || 'Choose a role' }}</span>
                </span>
                <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
              </ListboxButton>
              <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                <ListboxOptions
                  class="absolute z-10 mt-1 max-h-64 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline-1 outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                >
                  <ListboxOption v-for="role in roles" :key="role.id" :value="role.id" v-slot="{ active, selected }">
                    <li
                      :class="[
                        active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                        'relative cursor-default py-2 pr-9 pl-3 select-none',
                      ]"
                    >
                      <div class="flex items-center gap-3">
                        <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                          {{ role.name?.[0] || '?' }}
                        </span>
                        <div class="min-w-0">
                          <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ role.name }}</span>
                          <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ role.description || 'No description' }}</span>
                        </div>
                      </div>
                      <span
                        v-if="selected"
                        :class="[
                          active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                          'absolute inset-y-0 right-0 flex items-center pr-4',
                        ]"
                      >
                        <CheckIcon class="size-5" aria-hidden="true" />
                      </span>
                    </li>
                  </ListboxOption>
                  <li v-if="!roles.length" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No roles yet.</li>
                </ListboxOptions>
              </transition>
            </div>
          </Listbox>

          <div v-if="isCreating" class="mt-4 space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Create new role</p>
            <input v-model="newRole.name" type="text" placeholder="Role name" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
            <textarea v-model="newRole.description" rows="2" placeholder="Description" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
            <button
              type="button"
              class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              @click="createRoleAction"
            >
              Save role
            </button>
          </div>
        </aside>

        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div v-if="!selectedRoleId" class="text-sm text-gray-500 dark:text-gray-400">Select a role to edit.</div>
          <div v-else class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Role name</label>
                <input v-model="selectedRole.name" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
                <textarea v-model="selectedRole.description" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Permissions</h3>
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
                          <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ permission.display }}</p>
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
