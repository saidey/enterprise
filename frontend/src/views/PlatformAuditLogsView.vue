<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchPlatformAuditLogs, fetchPlatformAuditActions, fetchPlatformAuditLog, fetchAllCompanies } from '../api'

const logs = ref([])
const actions = ref([])
const companies = ref([])
const loading = ref(false)
const error = ref('')
const pagination = ref({ total: 0, current_page: 1, last_page: 1 })
const detailLog = ref(null)
const detailModel = ref(null)
const detailLoading = ref(false)

const filters = ref({
  company_id: '',
  action: '',
  user_id: '',
  auditable_type: '',
})

const loadLogs = async (page = 1) => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchPlatformAuditLogs({
      page,
      company_id: filters.value.company_id || undefined,
      action: filters.value.action || undefined,
      user_id: filters.value.user_id || undefined,
      auditable_type: filters.value.auditable_type || undefined,
      sort_by: 'created_at',
      sort_dir: 'desc',
      per_page: 100,
    })
    const payload = data.data || data
    logs.value = payload.data || []
    pagination.value = {
      total: payload.total || 0,
      current_page: payload.current_page || 1,
      last_page: payload.last_page || 1,
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load audit logs.'
  } finally {
    loading.value = false
  }
}

const loadActions = async () => {
  try {
    const { data } = await fetchPlatformAuditActions()
    actions.value = data.data || data || []
  } catch (err) {
    console.error(err)
  }
}

const loadCompanies = async () => {
  try {
    const { data } = await fetchAllCompanies()
    companies.value = data.data || data || []
  } catch (err) {
    console.error(err)
  }
}

const humanizeAction = (action) => action?.replace(/_/g, ' ') || ''

const clearFilters = () => {
  filters.value = { company_id: '', action: '', user_id: '', auditable_type: '' }
  loadLogs(1)
}

const openLog = async (log) => {
  detailLoading.value = true
  detailLog.value = log
  detailModel.value = null
  try {
    const { data } = await fetchPlatformAuditLog(log.id)
    detailLog.value = data.data?.log || data.log || null
    detailModel.value = data.data?.model || data.model || null
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load audit log details.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  detailLog.value = null
  detailModel.value = null
}

onMounted(async () => {
  await Promise.all([loadActions(), loadCompanies(), loadLogs(1)])
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Audit logs (all tenants)</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Platform-wide audit trail. Filter by tenant/company when needed.
        </p>
      </header>

      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Company</label>
            <select
              v-model="filters.company_id"
              class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 outline-hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus-visible:outline-indigo-500"
            >
              <option value="">All companies</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">
                {{ c.name }} ({{ c.slug }})
              </option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Action</label>
            <select
              v-model="filters.action"
              class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 outline-hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus-visible:outline-indigo-500"
            >
              <option value="">All</option>
              <option v-for="a in actions" :key="a" :value="a">{{ humanizeAction(a) }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">User ID</label>
            <input
              v-model="filters.user_id"
              type="text"
              class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 outline-hidden placeholder:text-gray-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus-visible:outline-indigo-500"
              placeholder="UUID or numeric"
            />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Model</label>
            <input
              v-model="filters.auditable_type"
              type="text"
              class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 outline-hidden placeholder:text-gray-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus-visible:outline-indigo-500"
              placeholder="e.g. App\\Models\\User"
            />
          </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-3">
          <button
            type="button"
            class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-xs hover:bg-gray-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white/10 dark:text-white dark:hover:bg-white/20 dark:focus-visible:outline-white"
            @click="clearFilters"
          >
            Clear
          </button>
          <button
            type="button"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
            @click="loadLogs(1)"
          >
            Apply
          </button>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Model</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"> </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  {{ log.created_at_human || log.created_at }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="font-semibold text-gray-900 dark:text-white">
                    {{ log.company?.name || '—' }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ log.company_id || '' }}
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ humanizeAction(log.action) }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="font-semibold text-gray-900 dark:text-white">{{ log.user?.name || '—' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ log.user?.email || log.user_id || '' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="truncate text-xs text-gray-600 dark:text-gray-300">{{ log.auditable_type }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ log.auditable_id }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-100 dark:ring-indigo-500/40 dark:hover:bg-indigo-500/30"
                    @click="openLog(log)"
                  >
                    View
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && !logs.length">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No audit logs found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">
          <div>Page {{ pagination.current_page }} of {{ pagination.last_page }}</div>
          <div class="flex gap-2">
            <button
              class="rounded-md border border-gray-300 px-3 py-1 text-xs disabled:opacity-50 dark:border-white/10"
              :disabled="pagination.current_page <= 1 || loading"
              @click="loadLogs(pagination.current_page - 1)"
            >
              Prev
            </button>
            <button
              class="rounded-md border border-gray-300 px-3 py-1 text-xs disabled:opacity-50 dark:border-white/10"
              :disabled="pagination.current_page >= pagination.last_page || loading"
              @click="loadLogs(pagination.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Detail modal -->
      <div
        v-if="detailLog"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        @click.self="closeDetail"
      >
        <div class="w-full max-w-3xl rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Audit log</p>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ humanizeAction(detailLog.action) }}</h2>
            </div>
            <button
              type="button"
              class="rounded-md bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-900 hover:bg-gray-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white/10 dark:text-white dark:hover:bg-white/20"
              @click="closeDetail"
            >
              Close
            </button>
          </div>
          <div class="max-h-[70vh] space-y-4 overflow-y-auto p-4">
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Date</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ detailLog.created_at_human || detailLog.created_at }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Company</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ detailLog.company_id || '—' }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">User</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ detailLog.user?.name || '—' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ detailLog.user?.email || detailLog.user_id }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">IP / User agent</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ detailLog.ip_address || '—' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ detailLog.user_agent || '—' }}</p>
              </div>
              <div class="md:col-span-2">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">URL</p>
                <p class="truncate text-sm text-indigo-600 dark:text-indigo-300">{{ detailLog.url || '—' }}</p>
              </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Old values</p>
                <pre class="whitespace-pre-wrap break-words text-xs text-gray-800 dark:text-gray-200">{{ JSON.stringify(detailLog.old_values, null, 2) || '—' }}</pre>
              </div>
              <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">New values</p>
                <pre class="whitespace-pre-wrap break-words text-xs text-gray-800 dark:text-gray-200">{{ JSON.stringify(detailLog.new_values, null, 2) || '—' }}</pre>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Model payload</p>
              <pre class="whitespace-pre-wrap break-words text-xs text-gray-800 dark:text-gray-200">{{ JSON.stringify(detailModel, null, 2) || '—' }}</pre>
            </div>

            <div v-if="detailLoading" class="text-sm text-gray-500 dark:text-gray-400">Loading…</div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
