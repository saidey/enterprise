<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchProjects, createProject, fetchIslands } from '../api'
import { useRouter } from 'vue-router'

const router = useRouter()
const projects = ref([])
const islands = ref([])
const loading = ref(false)
const error = ref('')
const filterStatus = ref('')
const filterIsland = ref('')

const form = ref({
  name: '',
  code: '',
  island_id: '',
  client_name: '',
  site_location: '',
  latitude: '',
  longitude: '',
  start_date: '',
  expected_end_date: '',
  status: 'design',
  budget_amount: '',
  description: '',
})

const formError = ref('')
const formSuccess = ref('')

const statuses = [
  { value: 'design', label: 'Design' },
  { value: 'planned', label: 'Planned' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
]

const filteredProjects = computed(() => {
  return (projects.value || []).filter((p) => {
    const statusOk = filterStatus.value ? p.status === filterStatus.value : true
    const islandOk = filterIsland.value ? p.island_id === filterIsland.value : true
    return statusOk && islandOk
  })
})

const stats = computed(() => {
  const total = projects.value.length
  const byStatus = statuses.map((s) => ({
    value: s.value,
    label: s.label,
    count: projects.value.filter((p) => p.status === s.value).length,
  }))
  const byIsland = islands.value.map((i) => ({
    id: i.id,
    name: i.name,
    count: projects.value.filter((p) => p.island_id === i.id).length,
  }))
  return { total, byStatus, byIsland }
})

async function loadIslands() {
  try {
    const { data } = await fetchIslands()
    islands.value = data.data || []
  } catch (err) {
    console.error(err)
  }
}

async function loadProjects() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchProjects({
      status: filterStatus.value || undefined,
      island_id: filterIsland.value || undefined,
      per_page: 50,
    })
    projects.value = data.data?.data || data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load projects.'
  } finally {
    loading.value = false
  }
}

async function submitProject() {
  formError.value = ''
  formSuccess.value = ''
  try {
    const payload = { ...form.value }
    await createProject(payload)
    formSuccess.value = 'Project created.'
    await loadProjects()
    Object.assign(form.value, {
      name: '',
      code: '',
      island_id: '',
      client_name: '',
      site_location: '',
      latitude: '',
      longitude: '',
      start_date: '',
      expected_end_date: '',
      status: 'design',
      budget_amount: '',
      description: '',
    })
  } catch (err) {
    console.error(err)
    formError.value = err.response?.data?.message || 'Failed to create project.'
  }
}

const openProject = (project) => {
  router.push({ name: 'app-projects-detail', params: { id: project.id } })
}

onMounted(async () => {
  await Promise.all([loadIslands(), loadProjects()])
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="flex flex-col gap-2 border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Projects workspace</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Track projects, phases, tasks, procurement, and costs.</p>
          </div>
          <div class="flex gap-3">
            <select
              v-model="filterStatus"
              class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              @change="loadProjects"
            >
              <option value="">All statuses</option>
              <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <select
              v-model="filterIsland"
              class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              @change="loadProjects"
            >
              <option value="">All locations</option>
              <option v-for="i in islands" :key="i.id" :value="i.id">
                {{ i.name }} <span v-if="i.country">({{ i.country }})</span>
              </option>
            </select>
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              @click="loadProjects"
            >
              Refresh
            </button>
          </div>
        </div>
      </header>

      <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total projects</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.total }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Across all islands</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">By status</p>
          <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-800 dark:text-gray-200">
            <div v-for="s in stats.byStatus" :key="s.value" class="flex items-center justify-between">
              <span>{{ s.label }}</span>
              <span class="font-semibold">{{ s.count }}</span>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">By island</p>
          <div class="mt-2 space-y-1 text-sm text-gray-800 dark:text-gray-200 max-h-32 overflow-auto">
            <div v-for="i in stats.byIsland" :key="i.id" class="flex items-center justify-between">
              <span class="truncate">{{ i.name }}</span>
              <span class="font-semibold">{{ i.count }}</span>
            </div>
            <div v-if="!stats.byIsland.length" class="text-xs text-gray-500 dark:text-gray-400">No islands yet.</div>
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Create project</h2>
          <div class="text-xs text-gray-500 dark:text-gray-400">Fields can be edited later.</div>
        </div>
        <form class="mt-3 grid gap-4 md:grid-cols-2" @submit.prevent="submitProject">
          <div class="md:col-span-1">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Name</label>
            <input v-model="form.name" required type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-1">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Code</label>
            <input v-model="form.code" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Location</label>
            <select v-model="form.island_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
              <option value="">Select location</option>
              <option v-for="i in islands" :key="i.id" :value="i.id">
                {{ i.name }}
                <span v-if="i.country">({{ i.country }})</span>
                <span v-if="i.region" class="text-gray-500 dark:text-gray-400"> — {{ i.region }}</span>
              </option>
            </select>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Client</label>
            <input v-model="form.client_name" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Site location</label>
            <input v-model="form.site_location" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Status</label>
            <select v-model="form.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
              <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Budget (optional)</label>
            <input v-model="form.budget_amount" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Start date</label>
            <input v-model="form.start_date" type="date" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Expected end</label>
            <input v-model="form.expected_end_date" type="date" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
            <textarea v-model="form.description" rows="3" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
          <div class="md:col-span-2 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400">
              Save project
            </button>
            <span v-if="formSuccess" class="text-xs text-emerald-600 dark:text-emerald-300">{{ formSuccess }}</span>
            <span v-if="formError" class="text-xs text-red-600 dark:text-red-300">{{ formError }}</span>
          </div>
        </form>
      </section>

      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Projects</h2>
          <span class="text-xs text-gray-500 dark:text-gray-400">{{ filteredProjects.length }} items</span>
        </div>
        <div v-if="loading" class="border-t border-gray-200 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">
          Loading projects…
        </div>
        <div v-else-if="error" class="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
          {{ error }}
        </div>
        <div v-else class="overflow-hidden border-t border-gray-200 dark:border-white/10">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Island</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Budget</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dates</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="proj in filteredProjects" :key="proj.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ proj.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ proj.code || '—' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ islands.find((i) => i.id === proj.island_id)?.name || '—' }}
                </td>
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset ring-gray-200 dark:ring-white/10">
                    {{ proj.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ proj.budget_amount ?? '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="text-xs">Start: {{ proj.start_date || '—' }}</div>
                  <div class="text-xs">End: {{ proj.expected_end_date || '—' }}</div>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    @click="openProject(proj)"
                  >
                    Open
                  </button>
                </td>
              </tr>
              <tr v-if="!filteredProjects.length">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No projects match your filters.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppShell>
</template>
