<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchHrUsers, fetchEmployees, attachEmployeeToUser, removeHrUser } from '../api'

const users = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')
const employees = ref([])
const attachSelections = ref({})
const removeBusy = ref({})

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return users.value
  return users.value.filter((u) => {
    return (u.name || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q)
  })
})

async function loadUsers() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchHrUsers()
    users.value = data.data || []
    attachSelections.value = {}
    users.value.forEach((u) => {
      attachSelections.value[u.id] = ''
    })
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load users.'
  } finally {
    loading.value = false
  }
}

async function loadEmployees() {
  try {
    const { data } = await fetchEmployees({ per_page: 200 })
    employees.value = (data.data || data) ?? []
  } catch (err) {
    console.error(err)
  }
}

async function attachEmployee(userId) {
  const employeeId = attachSelections.value[userId]
  if (!employeeId) return
  loading.value = true
  error.value = ''
  try {
    await attachEmployeeToUser(userId, employeeId)
    await loadUsers()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to attach employee.'
  } finally {
    loading.value = false
  }
}

async function removeUser(userId) {
  removeBusy.value = { ...removeBusy.value, [userId]: true }
  error.value = ''
  try {
    await removeHrUser(userId)
    await loadUsers()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to remove user.'
  } finally {
    removeBusy.value = { ...removeBusy.value, [userId]: false }
  }
}

onMounted(loadUsers)
onMounted(loadEmployees)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Users</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Company users</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">All users linked to this company (not employees).</p>
      </header>

      <div class="flex items-center justify-between gap-3">
        <input
          v-model="search"
          type="search"
          placeholder="Search by name or email"
          class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
        />
      </div>

      <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
        Loading users…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-else class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-white/10">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
          <thead class="bg-gray-50 dark:bg-white/5">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Attach employee</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
            <tr v-for="u in filtered" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ u.name || '—' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ u.email || '—' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                <div class="flex items-center gap-2">
                  <select
                    v-model="attachSelections[u.id]"
                    class="w-full rounded-md border border-gray-200 px-2 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                  >
                    <option value="">Select employee</option>
                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                      {{ emp.full_name || emp.name || '' }} ({{ emp.code || emp.employee_id || '' }})
                    </option>
                  </select>
                  <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    :disabled="!attachSelections[u.id]"
                    @click="attachEmployee(u.id)"
                  >
                    Attach
                  </button>
                </div>
              </td>
              <td class="px-4 py-3 text-right text-sm">
                <button
                  type="button"
                  class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-500 disabled:opacity-50"
                  :disabled="removeBusy[u.id]"
                  @click="removeUser(u.id)"
                >
                  {{ removeBusy[u.id] ? 'Removing…' : 'Remove' }}
                </button>
              </td>
            </tr>
            <tr v-if="!filtered.length">
              <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No users found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
