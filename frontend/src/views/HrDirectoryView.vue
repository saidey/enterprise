<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchSelfDirectory } from '../api'

const employees = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return employees.value
  return employees.value.filter((emp) => {
    return (
      emp.name?.toLowerCase().includes(q) ||
      emp.title?.toLowerCase().includes(q) ||
      emp.department?.name?.toLowerCase().includes(q) ||
      emp.employee_id?.toLowerCase().includes(q) ||
      emp.email?.toLowerCase().includes(q)
    )
  })
})

async function loadDirectory() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchSelfDirectory()
    employees.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Unable to load directory.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDirectory)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Directory</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Company directory</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Search coworkers and view their contact details.</p>
          </div>
          <input
            v-model="search"
            type="search"
            placeholder="Search by name, title, or department"
            class="w-72 rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
          />
        </div>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="divide-y divide-gray-200 dark:divide-white/5">
          <div v-if="loading" class="p-4 text-sm text-gray-600 dark:text-gray-300">Loading directory…</div>
          <div v-else-if="error" class="p-4 text-sm text-red-600 dark:text-red-200">{{ error }}</div>
          <div v-else-if="!filtered.length" class="p-4 text-sm text-gray-500 dark:text-gray-400">No employees found.</div>
          <ul v-else class="divide-y divide-gray-200 dark:divide-white/5">
            <li v-for="emp in filtered" :key="emp.id" class="p-4 hover:bg-gray-50 dark:hover:bg-white/5">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ emp.name }}
                    <span class="ml-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ emp.employee_id || '' }}</span>
                  </div>
                  <div class="text-sm text-gray-700 dark:text-gray-200">{{ emp.title || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ emp.department?.name || 'No department' }}</div>
                </div>
                <div class="text-right text-sm text-gray-700 dark:text-gray-200">
                  <div class="font-medium">{{ emp.email || 'No email' }}</div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </AppShell>
</template>
