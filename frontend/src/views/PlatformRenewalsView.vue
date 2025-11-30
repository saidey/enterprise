<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { api } from '../api'

const loading = ref(true)
const error = ref('')
const success = ref('')
const submissions = ref([])
const busyId = ref(null)

const loadRenewals = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/api/admin/renewals')
    submissions.value = data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load renewals.'
  } finally {
    loading.value = false
  }
}

const approve = async (sub) => {
  busyId.value = sub.id
  error.value = ''
  success.value = ''
  try {
    await api.post(`/api/admin/renewals/${sub.id}/approve`)
    success.value = 'Renewal approved and subscription activated.'
    await loadRenewals()
  } catch (err) {
    error.value = err.response?.data?.message || 'Approval failed.'
  } finally {
    busyId.value = null
  }
}

onMounted(loadRenewals)
</script>

<template>
  <AppShell>
    <div class="space-y-4">
      <header class="border-b border-gray-200 pb-3 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Renewal submissions</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Review payment slips and activate subscriptions.</p>
      </header>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Submissions</h2>
          <span v-if="loading" class="text-xs text-gray-500 dark:text-gray-400">Loading…</span>
        </div>
        <p v-if="error" class="mt-2 text-xs text-red-600 dark:text-red-300">{{ error }}</p>
        <p v-if="success" class="mt-2 text-xs text-emerald-600 dark:text-emerald-300">{{ success }}</p>
        <div class="mt-3 overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-300">
              <tr>
                <th class="px-3 py-2 text-left font-semibold">Company</th>
                <th class="px-3 py-2 text-left font-semibold">Quote</th>
                <th class="px-3 py-2 text-left font-semibold">User</th>
                <th class="px-3 py-2 text-left font-semibold">Status</th>
                <th class="px-3 py-2 text-left font-semibold">Submitted</th>
                <th class="px-3 py-2 text-right font-semibold">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
              <tr v-for="sub in submissions" :key="sub.id" class="text-gray-900 dark:text-gray-100">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ sub.company?.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ sub.company_id }}</div>
                </td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ sub.quote?.number || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ sub.quote ? (sub.quote.total_amount + ' ' + (sub.quote.currency || 'MVR')) : '' }}
                  </div>
                </td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ sub.user?.name || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ sub.user?.email }}</div>
                </td>
                <td class="px-3 py-2">
                  <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 ring-inset dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">
                    {{ sub.status }}
                  </span>
                </td>
                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                  {{ new Date(sub.created_at).toLocaleString() }}
                </td>
                <td class="px-3 py-2 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a
                      v-if="sub.file_path"
                      :href="`/storage/${sub.file_path}`"
                      target="_blank"
                      class="text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white"
                    >
                      View slip
                    </a>
                    <button
                      type="button"
                      class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                      :disabled="busyId === sub.id"
                      @click="approve(sub)"
                    >
                      {{ busyId === sub.id ? 'Approving…' : 'Verify' }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!submissions.length && !loading">
                <td colspan="6" class="px-3 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                  No submissions yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
