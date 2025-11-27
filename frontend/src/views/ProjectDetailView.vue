<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import {
  fetchProject,
  fetchProjectPhases,
  createProjectPhase,
  updateProjectPhase,
  fetchProcurementItems,
  createProcurementItem,
  updateProcurementItem,
  fetchCostEntries,
  createCostEntry,
  fetchWbs,
} from '../api'

const route = useRoute()
const projectId = route.params.id
const project = ref(null)
const loading = ref(false)
const error = ref('')

const phases = ref([])
const wbsTree = ref([])
const procurement = ref([])
const costs = ref([])

const phaseForm = ref({ name: '', sort_order: 0, status: 'planned' })
const procurementForm = ref({ name: '', category: '', quantity: '', status: 'requested', estimated_cost: '' })
const costForm = ref({ category: '', description: '', amount: '', entry_date: '' })

const statuses = ['design', 'planned', 'in_progress', 'on_hold', 'completed', 'cancelled']
const procurementStatuses = ['requested', 'quotation_received', 'approved', 'ordered', 'in_transit', 'delivered', 'installed']

async function loadAll() {
  loading.value = true
  error.value = ''
  try {
    const [{ data: proj }, { data: phs }, { data: wbs }, { data: pcs }, { data: cst }] = await Promise.all([
      fetchProject(projectId),
      fetchProjectPhases(projectId),
      fetchWbs(projectId),
      fetchProcurementItems(projectId),
      fetchCostEntries(projectId),
    ])
    project.value = proj.data || proj
    phases.value = phs.data || []
    wbsTree.value = wbs.data || []
    procurement.value = pcs.data || []
    costs.value = cst.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load project.'
  } finally {
    loading.value = false
  }
}

async function addPhase() {
  try {
    await createProjectPhase(projectId, phaseForm.value)
    phaseForm.value = { name: '', sort_order: 0, status: 'planned' }
    await loadAll()
  } catch (err) {
    console.error(err)
  }
}

async function addProcurement() {
  try {
    await createProcurementItem(projectId, procurementForm.value)
    procurementForm.value = { name: '', category: '', quantity: '', status: 'requested', estimated_cost: '' }
    await loadAll()
  } catch (err) {
    console.error(err)
  }
}

async function updateProcurement(item) {
  try {
    await updateProcurementItem(item.id, { status: item.status })
    await loadAll()
  } catch (err) {
    console.error(err)
  }
}

async function addCost() {
  try {
    await createCostEntry(projectId, costForm.value)
    costForm.value = { category: '', description: '', amount: '', entry_date: '' }
    await loadAll()
  } catch (err) {
    console.error(err)
  }
}

onMounted(loadAll)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects</p>
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ project?.name || 'Project' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ project?.code }}</p>
          </div>
          <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/40">
            {{ project?.status }}
          </span>
        </div>
      </header>

      <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
        Loading project…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-else class="space-y-6">
        <!-- Summary -->
        <section class="grid gap-4 md:grid-cols-3">
          <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ project?.client_name || '—' }}</p>
          </div>
          <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Budget</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ project?.budget_amount ?? '—' }}</p>
          </div>
          <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dates</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">Start: {{ project?.start_date || '—' }}</p>
            <p class="text-sm text-gray-900 dark:text-white">Expected: {{ project?.expected_end_date || '—' }}</p>
          </div>
        </section>

        <!-- Phases -->
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Phases</h2>
            <div class="flex gap-2">
              <input v-model="phaseForm.name" type="text" placeholder="Phase name" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <button type="button" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" @click="addPhase">
                Add
              </button>
            </div>
          </div>
          <div class="mt-3 grid gap-3 md:grid-cols-2">
            <div v-for="phase in phases" :key="phase.id" class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ phase.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Status: {{ phase.status }}</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Sort {{ phase.sort_order }}</span>
              </div>
            </div>
            <div v-if="!phases.length" class="text-sm text-gray-500 dark:text-gray-400">No phases yet.</div>
          </div>
        </section>

        <!-- WBS snapshot replaces tasks -->
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">WBS snapshot</h2>
            <router-link
              :to="{ name: 'app-projects-wbs', params: { id: projectId } }"
              class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
              Open full WBS
            </router-link>
          </div>
          <div class="mt-3 space-y-2 max-h-64 overflow-auto">
            <div v-for="node in wbsTree" :key="node.id" class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
              <div class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ node.code }}</span>
                <span class="font-semibold">{{ node.title }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">({{ node.status }})</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Progress: {{ node.progress }}%</span>
              </div>
              <p class="text-xs text-gray-600 dark:text-gray-300">{{ node.description }}</p>
              <div v-if="node.children?.length" class="ml-3 mt-2 space-y-1">
                <div v-for="child in node.children" :key="child.id" class="text-xs text-gray-700 dark:text-gray-200">
                  {{ child.code }} - {{ child.title }} ({{ child.status }}) {{ child.progress }}%
                </div>
              </div>
            </div>
            <div v-if="!wbsTree.length" class="text-sm text-gray-500 dark:text-gray-400">No WBS items yet.</div>
          </div>
        </section>

        <!-- Procurement -->
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Procurement</h2>
            <div class="flex gap-2">
              <input v-model="procurementForm.name" type="text" placeholder="Item name" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <select v-model="procurementForm.status" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                <option v-for="s in procurementStatuses" :key="s" :value="s">{{ s }}</option>
              </select>
              <button type="button" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" @click="addProcurement">
                Add
              </button>
            </div>
          </div>
          <div class="mt-3 space-y-2">
            <div v-for="item in procurement" :key="item.id" class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Status: {{ item.status }}</p>
                </div>
                <select v-model="item.status" class="rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-white/10 dark:bg-gray-900 dark:text-white" @change="updateProcurement(item)">
                  <option v-for="s in procurementStatuses" :key="s" :value="s">{{ s }}</option>
                </select>
              </div>
            </div>
            <div v-if="!procurement.length" class="text-sm text-gray-500 dark:text-gray-400">No procurement items yet.</div>
          </div>
        </section>

        <!-- Costs -->
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Costs</h2>
            <div class="flex gap-2">
              <input v-model="costForm.category" type="text" placeholder="Category" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <input v-model="costForm.amount" type="number" step="0.01" placeholder="Amount" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <button type="button" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" @click="addCost">
                Add
              </button>
            </div>
          </div>
          <div class="mt-3 space-y-2">
            <div v-for="c in costs" :key="c.id" class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ c.category }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ c.description || '—' }}</p>
                </div>
                <div class="text-sm text-gray-900 dark:text-white"> {{ c.amount }}</div>
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Date: {{ c.entry_date || '—' }}</div>
            </div>
            <div v-if="!costs.length" class="text-sm text-gray-500 dark:text-gray-400">No costs yet.</div>
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>
