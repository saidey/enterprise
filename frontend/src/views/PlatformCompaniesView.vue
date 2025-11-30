<script setup>
import { onMounted, ref, computed } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchAllCompanies } from '../api'

const companies = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return companies.value
  return companies.value.filter((c) => {
    return (
      (c.name || '').toLowerCase().includes(q) ||
      (c.slug || '').toLowerCase().includes(q) ||
      (c.status || '').toLowerCase().includes(q) ||
      (c.subscription_status || '').toLowerCase().includes(q)
    )
  })
})

async function loadCompanies() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchAllCompanies()
    companies.value = data.data || data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load companies.'
  } finally {
    loading.value = false
  }
}

onMounted(loadCompanies)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Companies</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Platform-wide list with subscription status.</p>
      </header>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
          v-model="search"
          type="search"
          placeholder="Search by name, slug, status"
          class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white sm:w-80"
        />
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
            @click="loadCompanies"
            :disabled="loading"
          >
            Refresh
          </button>
          <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">Loading companies…</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscription</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="c in filtered" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ c.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ c.slug }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ c.status || '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <span
                    :class="[
                      c.subscription_status === 'active'
                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                        : 'bg-amber-50 text-amber-700 ring-amber-200',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset dark:bg-white/10 dark:text-white dark:ring-white/10'
                    ]"
                  >
                    {{ c.subscription_status || '—' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!filtered.length && !loading">
                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No companies found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
