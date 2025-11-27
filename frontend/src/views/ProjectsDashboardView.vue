<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchProjects, fetchIslands, fetchProcurementItems, fetchCostEntries, fetchProjectTasks } from '../api'

const projects = ref([])
const islands = ref([])
const loading = ref(false)
const error = ref('')
const selectedProjectId = ref('')
const selectedProject = computed(() => projects.value.find((p) => p.id === selectedProjectId.value) || null)
const projectTasks = ref([])
const projectProcurement = ref([])
const projectCosts = ref([])

const statuses = [
  { value: 'design', label: 'Design' },
  { value: 'planned', label: 'Planned' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
]

const metrics = computed(() => {
  const total = projects.value.length
  const budgetTotal = projects.value.reduce((acc, p) => acc + (Number(p.budget_amount) || 0), 0)
  const byStatus = statuses.map((s) => {
    const count = projects.value.filter((p) => p.status === s.value).length
    return { ...s, count, percent: total ? Math.round((count / total) * 100) : 0 }
  })
  const byIsland = islands.value.map((i) => {
    const count = projects.value.filter((p) => p.island_id === i.id).length
    return { id: i.id, name: i.name, count }
  })
  const active = projects.value.filter((p) => ['design', 'planned', 'in_progress'].includes(p.status)).length
  return { total, active, budgetTotal, byStatus, byIsland }
})

const projectMetrics = computed(() => {
  if (!selectedProject.value) return null
  return {
    tasksTotal: projectTasks.value.length,
    tasksDone: projectTasks.value.filter((t) => t.status === 'completed').length,
    procurementTotal: projectProcurement.value.length,
    procurementDelivered: projectProcurement.value.filter((i) => i.status === 'delivered').length,
    costsTotal: projectCosts.value.reduce((acc, c) => acc + (Number(c.amount) || 0), 0),
  }
})

async function loadData() {
  loading.value = true
  error.value = ''
  try {
    const [{ data: proj }, { data: isl }] = await Promise.all([fetchProjects({ per_page: 100 }), fetchIslands()])
    projects.value = proj.data?.data || proj.data || []
    islands.value = isl.data || []
    if (!selectedProjectId.value && projects.value.length) {
      selectedProjectId.value = projects.value[0].id
    }
    if (selectedProjectId.value) {
      await loadProjectDrill(selectedProjectId.value)
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load dashboard.'
  } finally {
    loading.value = false
  }
}

async function loadProjectDrill(projectId) {
  if (!projectId) return
  try {
    const [{ data: tasks }, { data: proc }, { data: costs }] = await Promise.all([
      fetchProjectTasks(projectId),
      fetchProcurementItems(projectId),
      fetchCostEntries(projectId),
    ])
    projectTasks.value = tasks.data || []
    projectProcurement.value = proc.data || []
    projectCosts.value = costs.data || []
  } catch (err) {
    console.error(err)
  }
}

function handleProjectSelect() {
  loadProjectDrill(selectedProjectId.value)
}

onMounted(loadData)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Projects dashboard</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Overview of projects by status and island for management.</p>
      </header>

      <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
        Loading dashboard…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-else class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total projects</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ metrics.total }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Active: {{ metrics.active }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Budget (sum)</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ metrics.budgetTotal.toLocaleString() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">All projects</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Islands covered</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ metrics.byIsland.length }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Projects distributed across islands</p>
          </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Project drill-down</h2>
              <select v-model="selectedProjectId" class="rounded-md border border-gray-200 px-3 py-1.5 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" @change="handleProjectSelect">
                <option value="">Select project</option>
                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
            <div v-if="selectedProject" class="mt-3 space-y-2">
              <div class="text-sm text-gray-900 dark:text-white">
                <span class="font-semibold">{{ selectedProject.name }}</span>
                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ selectedProject.code }}</span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-sm text-gray-800 dark:text-gray-200">
                <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                  <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tasks</p>
                  <p class="text-lg font-semibold">{{ projectMetrics?.tasksDone || 0 }} / {{ projectMetrics?.tasksTotal || 0 }} done</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                  <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Procurement</p>
                  <p class="text-lg font-semibold">{{ projectMetrics?.procurementDelivered || 0 }} / {{ projectMetrics?.procurementTotal || 0 }} delivered</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                  <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Costs total</p>
                  <p class="text-lg font-semibold">{{ (projectMetrics?.costsTotal || 0).toLocaleString() }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                  <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                  <p class="text-lg font-semibold">{{ selectedProject.status }}</p>
                </div>
              </div>
            </div>
            <div v-else class="mt-3 text-sm text-gray-500 dark:text-gray-400">Select a project to see its metrics.</div>
          </div>

          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Status distribution</h2>
              <span class="text-xs text-gray-500 dark:text-gray-400">% of projects</span>
            </div>
            <div class="mt-3 space-y-2">
              <div v-for="s in metrics.byStatus" :key="s.value" class="space-y-1">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                  <span>{{ s.label }}</span>
                  <span>{{ s.count }} ({{ s.percent }}%)</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                  <div
                    class="h-2 rounded-full bg-indigo-500"
                    :style="{ width: `${s.percent}%` }"
                  ></div>
                </div>
              </div>
              <div v-if="!metrics.total" class="text-sm text-gray-500 dark:text-gray-400">No projects yet.</div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Projects by island</h2>
              <span class="text-xs text-gray-500 dark:text-gray-400">Count</span>
            </div>
            <div class="mt-3 space-y-2">
              <div v-for="i in metrics.byIsland" :key="i.id" class="space-y-1">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                  <span class="truncate">{{ i.name }}</span>
                  <span>{{ i.count }}</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                  <div
                    class="h-2 rounded-full bg-emerald-500"
                    :style="{ width: metrics.total ? `${Math.min(100, (i.count / metrics.total) * 100)}%` : '0%' }"
                  ></div>
                </div>
              </div>
              <div v-if="!metrics.byIsland.length" class="text-sm text-gray-500 dark:text-gray-400">No islands yet.</div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>
