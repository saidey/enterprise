<script setup>
import { onMounted, ref, computed } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchAdminInvoices, generateAdminInvoices } from '../api'

const invoices = ref([])
const loading = ref(false)
const error = ref('')
const generating = ref(false)
const search = ref('')

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return invoices.value
  return invoices.value.filter((inv) => {
    return (
      (inv.company?.name || '').toLowerCase().includes(q) ||
      (inv.number || '').toLowerCase().includes(q) ||
      (inv.status || '').toLowerCase().includes(q) ||
      (inv.plan?.name || '').toLowerCase().includes(q)
    )
  })
})

async function loadInvoices() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchAdminInvoices()
    invoices.value = data.data || data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoices.'
  } finally {
    loading.value = false
  }
}

async function generateUpcoming() {
  generating.value = true
  error.value = ''
  try {
    await generateAdminInvoices()
    await loadInvoices()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to generate invoices.'
  } finally {
    generating.value = false
  }
}

onMounted(loadInvoices)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Invoices</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Platform billing for tenants.</p>
      </header>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
          v-model="search"
          type="search"
          placeholder="Search by company, plan, status"
          class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white sm:w-96"
        />
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
            @click="loadInvoices"
            :disabled="loading"
          >
            Refresh
          </button>
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            :disabled="generating"
            @click="generateUpcoming"
          >
            {{ generating ? 'Generating…' : 'Generate upcoming' }}
          </button>
          <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">Loading invoices…</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Period</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Totals</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="inv in filtered" :key="inv.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ inv.company?.name || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">#{{ inv.number || inv.id }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div>{{ inv.plan?.name || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ inv.period_start }} → {{ inv.period_end }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div>Ex GST: {{ inv.amount_ex_gst }}</div>
                  <div>GST ({{ inv.gst_percent }}%): {{ inv.gst_amount }}</div>
                  <div class="font-semibold text-gray-900 dark:text-white">Total: {{ inv.total_amount }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <span
                    :class="[
                      inv.status === 'paid'
                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                        : 'bg-amber-50 text-amber-700 ring-amber-200',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset dark:bg-white/10 dark:text-white dark:ring-white/10'
                    ]"
                  >
                    {{ inv.status || 'pending' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!filtered.length && !loading">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No invoices found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
