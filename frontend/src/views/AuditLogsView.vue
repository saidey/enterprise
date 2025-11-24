<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="sm:flex sm:items-center sm:justify-between">
        <div class="sm:flex-auto">
          <h1 class="text-base/7 font-semibold text-gray-900 dark:text-white">
            Audit logs
          </h1>
          <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-300">
            A record of changes and important actions across the system.
          </p>
        </div>
      </div>

      <!-- Filters -->
      <div
        class="rounded-lg border border-gray-200 bg-white px-4 py-4 shadow-xs sm:px-6 dark:border-white/10 dark:bg-gray-900"
      >
        <div class="flex flex-wrap items-end gap-3">
          <!-- Action filter (dynamic) -->
          <div class="flex flex-col gap-1">
            <label
              for="action-filter"
              class="text-xs/5 font-medium text-gray-700 dark:text-gray-300"
            >
              Action
            </label>
            <select
              id="action-filter"
              v-model="filters.action"
              class="w-48 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm/6 text-gray-900 outline-hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus-visible:outline-indigo-500"
            >
              <option value="">All</option>
              <option
                v-for="action in actions"
                :key="action"
                :value="action"
              >
                {{ humanizeAction(action) }}
              </option>
            </select>
          </div>

          <!-- User ID filter -->
          <div class="flex flex-col gap-1">
            <label
              for="user-filter"
              class="text-xs/5 font-medium text-gray-700 dark:text-gray-300"
            >
              User ID
            </label>
            <input
              id="user-filter"
              v-model="filters.user_id"
              type="text"
              class="w-56 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm/6 text-gray-900 outline-hidden placeholder:text-gray-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus-visible:outline-indigo-500"
              placeholder="UUID or numeric ID"
            />
          </div>

          <div class="flex flex-1 justify-end gap-3">
            <button
              type="button"
              class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-xs hover:bg-gray-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white/10 dark:text-white dark:hover:bg-white/20 dark:focus-visible:outline-white"
              @click="() => { filters.action = ''; filters.user_id = ''; loadLogs(1) }"
            >
              Clear
            </button>
            <button
              type="button"
              class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
              @click="loadLogs(1)"
            >
              Apply filters
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div
              class="relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xs dark:border-white/10 dark:bg-gray-900 dark:shadow-none"
            >
              <table class="min-w-full divide-y divide-gray-200 dark:divide-white/15">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                  <tr>
                    <!-- WHEN -->
                    <th
                      scope="col"
                      class="py-3.5 pl-4 pr-3 text-left text-xs/5 font-semibold uppercase tracking-wide text-gray-500 sm:pl-6 dark:text-gray-300 cursor-pointer select-none"
                      @click="changeSort('created_at')"
                    >
                      <span class="inline-flex items-center gap-1">
                        When
                        <span v-if="sort.by === 'created_at'">
                          {{ sort.dir === 'asc' ? '↑' : '↓' }}
                        </span>
                      </span>
                    </th>

                    <!-- USER -->
                    <th
                      scope="col"
                      class="px-3 py-3.5 text-left text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300 cursor-pointer select-none"
                      @click="changeSort('user_id')"
                    >
                      <span class="inline-flex items-center gap-1">
                        User
                        <span v-if="sort.by === 'user_id'">
                          {{ sort.dir === 'asc' ? '↑' : '↓' }}
                        </span>
                      </span>
                    </th>

                    <!-- ACTION -->
                    <th
                      scope="col"
                      class="px-3 py-3.5 text-left text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300 cursor-pointer select-none"
                      @click="changeSort('action')"
                    >
                      <span class="inline-flex items-center gap-1">
                        Action
                        <span v-if="sort.by === 'action'">
                          {{ sort.dir === 'asc' ? '↑' : '↓' }}
                        </span>
                      </span>
                    </th>

                    <!-- MODEL -->
                    <th
                      scope="col"
                      class="px-3 py-3.5 text-left text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300 cursor-pointer select-none"
                      @click="changeSort('auditable_type')"
                    >
                      <span class="inline-flex items-center gap-1">
                        Model
                        <span v-if="sort.by === 'auditable_type'">
                          {{ sort.dir === 'asc' ? '↑' : '↓' }}
                        </span>
                      </span>
                    </th>

                    <!-- DETAILS -->
                    <th
                      scope="col"
                      class="px-3 py-3.5 text-right text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300"
                    >
                      Details
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                  <tr v-if="loading">
                    <td
                      colspan="5"
                      class="py-6 text-center text-sm/6 text-gray-500 dark:text-gray-400"
                    >
                      Loading audit logs…
                    </td>
                  </tr>

                  <tr v-else-if="logs.length === 0">
                    <td
                      colspan="5"
                      class="py-6 text-center text-sm/6 text-gray-500 dark:text-gray-400"
                    >
                      No audit logs found.
                    </td>
                  </tr>

                  <template v-else v-for="log in logs" :key="log.id">
                    <!-- Base row -->
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                      <td
                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm/6 text-gray-900 sm:pl-6 dark:text-white"
                      >
                        <div>{{ formatDate(log.created_at) }}</div>
                        <div class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                          {{ formatTime(log.created_at) }}
                        </div>
                      </td>

                      <td class="whitespace-nowrap px-3 py-4 text-sm/6 text-gray-900 dark:text-white">
                        <div class="font-medium">
                          {{ log.user?.name ?? 'System / unknown' }}
                        </div>
                        <div class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
                          {{ log.user?.email ?? log.user_id ?? '—' }}
                        </div>
                      </td>

                      <td class="whitespace-nowrap px-3 py-4 text-sm/6 text-gray-900 dark:text-white">
                        <span
                          class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20 ring-inset dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-500/60"
                        >
                          {{ humanizeAction(log.action) }}
                        </span>
                      </td>

                      <!-- Model column with basic summary -->
                      <td class="whitespace-nowrap px-3 py-4 text-sm/6 text-gray-900 dark:text-white">
                        <div class="font-medium break-all">
                          {{ shortModelName(log.auditable_type) }}
                        </div>
                        <div class="mt-1 text-xs/5 text-gray-500 break-all dark:text-gray-400">
                          ID: {{ log.auditable_id }}
                        </div>
                      </td>

                      <td class="whitespace-nowrap px-3 py-4 text-sm/6 text-right text-gray-900 dark:text-white">
                        <button
                          type="button"
                          class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10 dark:hover:bg-white/5"
                          @click="onToggleDetails(log)"
                        >
                          {{ expandedId === log.id ? 'Close' : 'Open' }}
                        </button>
                      </td>
                    </tr>

                    <!-- Details row -->
                    <tr v-if="expandedId === log.id">
                      <td
                        colspan="5"
                        class="bg-gray-50 px-4 py-4 text-sm/6 text-gray-900 sm:px-6 dark:bg-gray-950 dark:text-gray-100"
                      >
                        <!-- Loading / error states specific to this log -->
                        <div
                          v-if="detail.logId === log.id && detail.loading"
                          class="text-xs/5 text-gray-500 dark:text-gray-400"
                        >
                          Loading details…
                        </div>

                        <div
                          v-else-if="detail.logId === log.id && detail.error"
                          class="text-xs/5 text-red-400"
                        >
                          {{ detail.error }}
                        </div>

                        <div v-else-if="detail.logId === log.id" class="space-y-4">
                          <!-- Change details (old vs new) -->
                          <div>
                            <h3
                              class="text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                              Change details
                            </h3>

                            <div v-if="hasAnyValues(detail.log || log)" class="mt-2 overflow-x-auto">
                              <table
                                class="min-w-full overflow-hidden rounded-md border border-gray-200 text-xs/5 dark:border-white/10"
                              >
                                <thead class="bg-gray-100 dark:bg-gray-900/60">
                                  <tr>
                                    <th
                                      class="px-2 py-1 text-left font-medium text-gray-600 dark:text-gray-300"
                                    >
                                      Field
                                    </th>
                                    <th
                                      class="px-2 py-1 text-left font-medium text-gray-600 dark:text-gray-300"
                                    >
                                      Old
                                    </th>
                                    <th
                                      class="px-2 py-1 text-left font-medium text-gray-600 dark:text-gray-300"
                                    >
                                      New
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr
                                    v-for="row in getDiffRows(detail.log || log)"
                                    :key="row.key"
                                    :class="row.changed ? 'bg-yellow-50 dark:bg-yellow-900/20' : ''"
                                  >
                                    <td
                                      class="px-2 py-1 align-top font-medium text-gray-700 dark:text-gray-200"
                                    >
                                      {{ row.key }}
                                    </td>
                                    <td
                                      class="whitespace-pre-wrap break-words px-2 py-1 align-top text-gray-700 dark:text-gray-200"
                                    >
                                      {{ row.oldValue }}
                                    </td>
                                    <td
                                      class="whitespace-pre-wrap break-words px-2 py-1 align-top text-gray-700 dark:text-gray-200"
                                    >
                                      {{ row.newValue }}
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                Highlighted rows indicate fields that changed.
                              </p>
                            </div>

                            <p v-else class="mt-2 text-xs/5 text-gray-500 dark:text-gray-400">
                              No change payload recorded for this log.
                            </p>
                          </div>

                          <!-- Current model details (from server) -->
                          <div class="border-t border-gray-200 pt-3 dark:border-white/10">
                            <h3
                              class="text-xs/5 font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                              Current model
                            </h3>

                            <div v-if="detail.model" class="mt-2 text-xs/5">
                              <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                                <div
                                  v-for="[key, value] in Object.entries(detail.model)"
                                  :key="key"
                                  v-if="!hiddenModelKeys.includes(key)"
                                  class="rounded-md bg-white px-2 py-1 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10"
                                >
                                  <div
                                    class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                  >
                                    {{ key }}
                                  </div>
                                  <div class="mt-0.5 break-words text-gray-900 dark:text-gray-100">
                                    {{ formatValue(value) }}
                                  </div>
                                </div>
                              </div>
                            </div>
                            <p v-else class="mt-2 text-xs/5 text-gray-500 dark:text-gray-400">
                              Model record is not available (it may have been deleted).
                            </p>
                          </div>

                          <!-- Request meta -->
                          <div
                            class="border-t border-gray-200 pt-3 text-xs/5 text-gray-600 dark:border-white/10 dark:text-gray-300"
                          >
                            <div class="flex flex-wrap gap-4">
                              <div>
                                <span class="font-semibold">IP:</span>
                                <span class="ml-1">{{ (detail.log || log).ip_address ?? '—' }}</span>
                              </div>
                              <div class="min-w-0 flex-1">
                                <span class="font-semibold">User Agent:</span>
                                <span
                                  class="ml-1 block truncate"
                                  :title="(detail.log || log).user_agent"
                                >
                                  {{ (detail.log || log).user_agent ?? '—' }}
                                </span>
                              </div>
                              <div class="w-full">
                                <span class="font-semibold">URL:</span>
                                <span class="ml-1 break-all">
                                  {{ (detail.log || log).url ?? '—' }}
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>

              <!-- Pagination -->
              <div
                v-if="meta"
                class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-3 text-xs/5 text-gray-700 dark:border-white/10 dark:bg-gray-900/60 dark:text-gray-300 sm:px-6"
              >
                <div>
                  Page {{ meta.current_page }} of {{ meta.last_page }}
                  <span class="mx-1 text-gray-400">•</span>
                  <span>
                    Showing
                    {{
                      Math.min(
                        (meta.current_page - 1) * meta.per_page + 1,
                        meta.total || 0
                      )
                    }}
                    –
                    {{
                      Math.min(meta.current_page * meta.per_page, meta.total || 0)
                    }}
                    of {{ meta.total }} results
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10 dark:hover:bg-white/5"
                    :disabled="meta.current_page <= 1 || loading"
                    @click="loadLogs(meta.current_page - 1)"
                  >
                    Previous
                  </button>
                  <button
                    type="button"
                    class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10 dark:hover:bg:white/5"
                    :disabled="meta.current_page >= meta.last_page || loading"
                    @click="loadLogs(meta.current_page + 1)"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>

            <p v-if="error" class="mt-3 text-sm/6 text-red-400">
              {{ error }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchAuditLogs, fetchAuditLogDetails, fetchAuditActions } from '../api'

const logs = ref([])
const meta = ref(null)
const loading = ref(false)
const error = ref(null)

// dynamic actions
const actions = ref([])

const expandedId = ref(null)
const detail = ref({
  logId: null,
  loading: false,
  error: null,
  log: null,
  model: null,
})

const filters = ref({
  action: '',
  user_id: '',
})

// sort state
const sort = ref({
  by: 'created_at',
  dir: 'desc',
})

const hiddenModelKeys = [
  'password',
  'remember_token',
  'two_factor_secret',
  'two_factor_recovery_codes',
  'created_at',
  'updated_at',
  'deleted_at',
]

// Load distinct actions from backend
const loadActions = async () => {
  try {
    const { data } = await fetchAuditActions()
    actions.value = data?.data || data || []
  } catch (e) {
    console.error('Failed to load actions', e)
    actions.value = []
  }
}

// Humanize action labels (e.g. "logged_in" -> "logged in")
const humanizeAction = (val) => {
  if (!val) return ''
  return String(val).replace(/_/g, ' ')
}

const changeSort = (column) => {
  if (sort.value.by === column) {
    // toggle direction
    sort.value.dir = sort.value.dir === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value.by = column
    sort.value.dir = 'asc'
  }
  loadLogs(1)
}

const onToggleDetails = async (log) => {
  // Close if currently open
  if (expandedId.value === log.id) {
    expandedId.value = null
    detail.value = {
      logId: null,
      loading: false,
      error: null,
      log: null,
      model: null,
    }
    return
  }

  expandedId.value = log.id
  detail.value = {
    logId: log.id,
    loading: true,
    error: null,
    log: null,
    model: null,
  }

  try {
    const response = await fetchAuditLogDetails(log.id)
    const payload = response.data?.data ?? response.data

    detail.value.logId = log.id
    detail.value.log = payload.log ?? log
    detail.value.model = payload.model ?? null
    detail.value.loading = false
  } catch (e) {
    console.error(e)
    detail.value.error = 'Failed to load details.'
    detail.value.loading = false
  }
}

const loadLogs = async (page = 1) => {
  loading.value = true
  error.value = null

  try {
    const response = await fetchAuditLogs({
      page,
      action: filters.value.action || undefined,
      user_id: filters.value.user_id || undefined,
      sort_by: sort.value.by,
      sort_dir: sort.value.dir,
    })

    const payload = response.data
    const paginated = payload.data

    logs.value = paginated.data || []
    meta.value = {
      current_page: paginated.current_page,
      last_page: paginated.last_page,
      per_page: paginated.per_page,
      total: paginated.total,
    }

    // If expanded row disappeared from this page, reset details
    if (!logs.value.some((l) => l.id === expandedId.value)) {
      expandedId.value = null
      detail.value = {
        logId: null,
        loading: false,
        error: null,
        log: null,
        model: null,
      }
    }
  } catch (e) {
    console.error(e)
    if (e.response?.status === 403) {
      error.value = 'You do not have permission to view audit logs.'
      logs.value = []
      meta.value = null
    } else {
      error.value = 'Failed to load audit logs.'
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (value) => {
  if (!value) return '—'
  return new Date(value).toLocaleDateString()
}

const formatTime = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleTimeString()
}

const shortModelName = (fqcn) => {
  if (!fqcn) return '—'
  return fqcn.split('\\').pop()
}

// Safely parse stored values
const getValuesObject = (val) => {
  if (!val) return {}
  if (typeof val === 'string') {
    try {
      return JSON.parse(val)
    } catch {
      return {}
    }
  }
  return val
}

const hasAnyValues = (log) => {
  return !!(log?.old_values || log?.new_values)
}

const formatValue = (value) => {
  if (value === null || value === undefined) return '—'
  if (typeof value === 'object') {
    try {
      return JSON.stringify(value)
    } catch {
      return String(value)
    }
  }
  return String(value)
}

// Diff rows from audit payload
const getDiffRows = (log) => {
  const oldVals = getValuesObject(log.old_values)
  const newVals = getValuesObject(log.new_values)

  const keys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)])).sort()

  return keys.map((key) => {
    const oldVal = oldVals[key]
    const newVal = newVals[key]

    const oldFormatted = formatValue(oldVal)
    const newFormatted = formatValue(newVal)
    const changed = JSON.stringify(oldVal) !== JSON.stringify(newVal)

    return {
      key,
      oldValue: oldFormatted,
      newValue: newFormatted,
      changed,
    }
  })
}

onMounted(async () => {
  await loadActions()
  await loadLogs(1)
})
</script>