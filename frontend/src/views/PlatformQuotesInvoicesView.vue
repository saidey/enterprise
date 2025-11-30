<script setup>
import { onMounted, ref, computed } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { apiInstance as api } from '../api'

const loading = ref(true)
const error = ref('')
const success = ref('')
const invoices = ref([])
const busyId = ref(null)
const tab = ref('quotes') // quotes | invoices
const apiBase = api?.defaults?.baseURL || ''
const renewals = ref([])

const quotes = computed(() => invoices.value.filter((i) => i.status === 'quote'))
const paidInvoices = computed(() => invoices.value.filter((i) => i.status !== 'quote'))

const getSubmissions = (inv) => {
  // Prefer attached relation, fallback to renewals loaded separately
  const rel = inv.renewalSubmissions || inv.renewal_submissions || []
  if (rel.length) return rel

  // Fallback: match by quote_id or, as a last resort, by company_id (latest submission)
  const matches = renewals.value.filter((r) => r.quote_id === inv.id)
  if (matches.length) return matches

  return renewals.value
    .filter((r) => r.company_id === inv.company_id)
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
}

const loadInvoices = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/api/admin/invoices')
    invoices.value = data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoices.'
  } finally {
    loading.value = false
  }
}

const loadRenewals = async () => {
  try {
    const { data } = await api.get('/api/admin/renewals')
    renewals.value = data.data || []
  } catch (err) {
    // ignore
  }
}

const approveQuote = async (invoice) => {
  busyId.value = invoice.id
  error.value = ''
  success.value = ''
  try {
    await api.post(`/api/admin/invoices/${invoice.id}/approve`)
    success.value = 'Quote verified.'
    await loadInvoices()
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to verify quote.'
  } finally {
    busyId.value = null
  }
}

onMounted(loadInvoices)
onMounted(loadRenewals)
</script>

<template>
  <AppShell>
    <div class="space-y-4">
      <header class="border-b border-gray-200 pb-3 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Quotes & invoices</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage tenant billing documents.</p>
      </header>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-semibold"
          :class="tab === 'quotes' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10'"
          @click="tab = 'quotes'"
        >
          Quotes
        </button>
        <button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-semibold"
          :class="tab === 'invoices' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10'"
          @click="tab = 'invoices'"
        >
          Invoices
        </button>
        <span v-if="loading" class="text-xs text-gray-500 dark:text-gray-400">Loading…</span>
      </div>

      <p v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</p>
      <p v-if="success" class="text-xs text-emerald-600 dark:text-emerald-300">{{ success }}</p>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-300">
              <tr>
                <th class="px-3 py-2 text-left font-semibold">Number</th>
                <th class="px-3 py-2 text-left font-semibold">Company</th>
                <th class="px-3 py-2 text-left font-semibold">Plan</th>
                <th class="px-3 py-2 text-right font-semibold">Total</th>
                <th class="px-3 py-2 text-left font-semibold">Status</th>
                <th class="px-3 py-2 text-left font-semibold">Period end</th>
                <th class="px-3 py-2 text-right font-semibold">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
              <tr
                v-for="inv in (tab === 'quotes' ? quotes : paidInvoices)"
                :key="inv.id"
                class="text-gray-900 dark:text-gray-100"
              >
                <td class="px-3 py-2 font-medium">{{ inv.number || '—' }}</td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ inv.company?.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ inv.company_id }}</div>
                </td>
                <td class="px-3 py-2">{{ inv.plan?.name }}</td>
                <td class="px-3 py-2 text-right">{{ Number(inv.total_amount).toFixed(2) }} {{ inv.currency || 'MVR' }}</td>
                <td class="px-3 py-2">
                  <span
                    v-if="inv.status === 'quote'"
                    class="rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20 ring-inset dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30"
                  >Quote</span>
                  <span
                    v-else-if="inv.status === 'paid'"
                    class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 ring-inset dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30"
                  >Paid</span>
                  <span
                    v-else
                    class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-300/80 ring-inset dark:bg-white/5 dark:text-gray-300 dark:ring-white/10"
                  >{{ inv.status }}</span>
                </td>
                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                  {{ inv.period_end ? new Date(inv.period_end).toLocaleDateString() : '—' }}
                </td>
                <td class="px-3 py-2 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a
                      v-if="getSubmissions(inv).length"
                      :href="`${apiBase}/api/admin/renewals/${getSubmissions(inv)[0].id}/file`"
                      target="_blank"
                      rel="noopener"
                      class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/5"
                    >
                      View slip
                    </a>
                    <span
                      v-else
                      class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-600 dark:ring-white/10"
                    >
                      No slip
                    </span>
                    <button
                      v-if="tab === 'quotes'"
                      type="button"
                      class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                      :disabled="busyId === inv.id"
                      @click="approveQuote(inv)"
                    >
                      {{ busyId === inv.id ? 'Verifying…' : 'Verify' }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="(tab === 'quotes' ? quotes.length : paidInvoices.length) === 0 && !loading">
                <td colspan="7" class="px-3 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                  No records.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
