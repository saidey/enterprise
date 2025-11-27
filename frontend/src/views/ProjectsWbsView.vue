<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { fetchProjects, fetchWbs, createWbs, updateWbs, deleteWbs } from '../api'

const route = useRoute()
const router = useRouter()
const projects = ref([])
const projectId = ref(route.params.id || '')
const tree = ref([])
const loading = ref(false)
const error = ref('')
const info = ref('')

const rootForm = reactive({
  title: '',
  description: '',
  quantity_total: '',
  quantity_completed: '',
})

const childDrafts = reactive({})
const statusOptions = ['not_started', 'in_progress', 'completed']
const selectedItem = ref(null)
const childCardForm = reactive({
  title: '',
  description: '',
  quantity_total: '',
  quantity_completed: '',
})

const selectedProject = computed(() => projects.value.find((p) => p.id === projectId.value) || null)

async function loadProjects() {
  try {
    const { data } = await fetchProjects({ per_page: 100 })
    projects.value = data.data?.data || data.data || []
    if (!projectId.value && projects.value.length) {
      projectId.value = projects.value[0].id
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load projects.'
  }
}

async function loadTree() {
  if (!projectId.value) return
  loading.value = true
  error.value = ''
  info.value = ''
  try {
    const { data } = await fetchWbs(projectId.value)
    tree.value = data.data || []
    // refresh selected item reference
    if (selectedItem.value) {
      const updated = flattened.value.find((n) => n.id === selectedItem.value.id)
      selectedItem.value = updated || null
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load WBS.'
  } finally {
    loading.value = false
  }
}

async function addRoot() {
  if (!projectId.value) return
  error.value = ''
  info.value = ''
  try {
    await createWbs(projectId.value, {
      title: rootForm.title,
      description: rootForm.description,
      quantity_total: rootForm.quantity_total,
      quantity_completed: rootForm.quantity_completed,
    })
    rootForm.title = ''
    rootForm.description = ''
    rootForm.quantity_total = ''
    rootForm.quantity_completed = ''
    info.value = 'WBS item created.'
    await loadTree()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create WBS item.'
  }
}

function ensureDraft(id) {
  if (!childDrafts[id]) {
    childDrafts[id] = { title: '', description: '' }
  }
}

async function addChild(parentId) {
  if (!projectId.value) return
  ensureDraft(parentId)
  const draft = childDrafts[parentId]
  if (!draft.title) {
    error.value = 'Enter a title for the child item.'
    return
  }
  error.value = ''
  info.value = ''
  try {
    await createWbs(projectId.value, {
      parent_id: parentId,
      title: draft.title,
      description: draft.description,
      quantity_total: draft.quantity_total,
      quantity_completed: draft.quantity_completed,
    })
    childDrafts[parentId] = { title: '', description: '' }
    info.value = 'Child WBS item created.'
    await loadTree()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create child.'
  }
}

async function saveItem(item) {
  error.value = ''
  info.value = ''
  try {
    await updateWbs(item.id, {
      title: item.title,
      description: item.description,
      notes: item.notes,
      status: item.status,
      estimated_cost: item.estimated_cost,
      actual_cost: item.actual_cost,
      quantity_total: item.quantity_total,
      quantity_completed: item.quantity_completed,
    })
    info.value = 'WBS item updated.'
    await loadTree()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update WBS item.'
  }
}

async function removeItem(id) {
  if (!confirm('Delete this WBS item and its children?')) return
  error.value = ''
  info.value = ''
  try {
    await deleteWbs(id)
    info.value = 'WBS item deleted.'
    await loadTree()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete WBS item.'
  }
}

const flattened = computed(() => {
  const out = []
  const walk = (nodes, depth = 0) => {
    nodes.forEach((n) => {
      out.push({ ...n, depth })
      if (n.children?.length) walk(n.children, depth + 1)
    })
  }
  walk(tree.value, 0)
  return out
})

function goProjects() {
  router.push({ name: 'app-projects' })
}

function selectItem(item) {
  selectedItem.value = { ...item }
  // clear child card form
  childCardForm.title = ''
  childCardForm.description = ''
  childCardForm.quantity_total = ''
  childCardForm.quantity_completed = ''
}

async function addChildFromCard() {
  if (!selectedItem.value) return
  if (!childCardForm.title) {
    error.value = 'Enter a title for the child item.'
    return
  }
  error.value = ''
  info.value = ''
  try {
    await createWbs(projectId.value, {
      parent_id: selectedItem.value.id,
      title: childCardForm.title,
      description: childCardForm.description,
      quantity_total: childCardForm.quantity_total,
      quantity_completed: childCardForm.quantity_completed,
    })
    childCardForm.title = ''
    childCardForm.description = ''
    childCardForm.quantity_total = ''
    childCardForm.quantity_completed = ''
    info.value = 'Child WBS item created.'
    await loadTree()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create child.'
  }
}

watch(projectId, async () => {
  await loadTree()
})

onMounted(async () => {
  await loadProjects()
  if (projectId.value) {
    await loadTree()
  }
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / WBS</p>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Work Breakdown Structure</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Hierarchical packages with roll-ups.</p>
          </div>
          <div class="flex gap-2">
            <select v-model="projectId" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
              <option value="">Select project</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              @click="goProjects"
            >
              Back to projects
            </button>
          </div>
        </div>
      </header>

      <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-if="info" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
        {{ info }}
      </div>
      <div v-if="selectedProject" class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-100">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide">Selected project</span>
            <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold shadow-sm ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/20 dark:ring-indigo-300/40">
              {{ selectedProject.name }}
            </span>
          </div>
          <div class="flex items-center gap-2 text-xs sm:text-sm">
            <span class="rounded-full bg-white px-3 py-1 font-semibold shadow-sm ring-1 ring-inset ring-indigo-200 dark:bg-indigo-500/20 dark:ring-indigo-300/40">
              Expected completion: {{ selectedProject.expected_end_date || '—' }}
            </span>
          </div>
        </div>
      </div>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Add root WBS item</h2>
        <form class="mt-3 grid gap-3 md:grid-cols-2" @submit.prevent="addRoot">
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Title</label>
            <input v-model="rootForm.title" required type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
            <textarea v-model="rootForm.description" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Quantity total</label>
            <input v-model="rootForm.quantity_total" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Quantity completed</label>
            <input v-model="rootForm.quantity_completed" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" :disabled="!projectId">
              Save
            </button>
            <span v-if="!projectId" class="ml-2 text-xs text-gray-500 dark:text-gray-400">Select a project first.</span>
          </div>
        </form>
      </section>

      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">WBS tree</h2>
          <button
            type="button"
            class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
            @click="loadTree"
          >
            Refresh
          </button>
        </div>
        <div v-if="loading" class="border-t border-gray-200 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">Loading WBS…</div>
        <div v-else class="divide-y divide-gray-200 dark:divide-white/10">
          <div v-for="item in flattened" :key="item.id" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-400 dark:text-gray-500">{{ item.code }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ '— '.repeat(item.depth) }}</span>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.title }}</span>
              <span
                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset"
                :class="item.status === 'completed'
                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/30'
                  : item.status === 'in_progress'
                  ? 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30'
                  : 'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-white/10'"
              >
                {{ item.status.replace('_', ' ') }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400">Progress: {{ item.progress }}%</span>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/30"
                @click="selectItem(item)"
              >
                Open
              </button>
              <button
                type="button"
                class="rounded-md bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-200"
                @click="removeItem(item.id)"
              >
                Delete
              </button>
            </div>
          </div>
          <div v-if="!flattened.length" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No WBS items yet.</div>
        </div>
      </section>

      <section v-if="selectedItem" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">WBS details</h2>
          <span class="text-xs text-gray-500 dark:text-gray-400">Code: {{ selectedItem.code }}</span>
        </div>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Title</label>
            <input v-model="selectedItem.title" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Status</label>
            <select v-model="selectedItem.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
              <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Progress</label>
            <input disabled :value="`${selectedItem.progress}% (auto)`" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Qty total</label>
            <input v-model="selectedItem.quantity_total" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Qty completed</label>
            <input v-model="selectedItem.quantity_completed" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Estimated cost</label>
            <input v-model="selectedItem.estimated_cost" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Actual cost</label>
            <input v-model="selectedItem.actual_cost" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Description</label>
            <textarea v-model="selectedItem.description" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notes</label>
            <textarea v-model="selectedItem.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
        </div>
        <div class="mt-3 flex items-center gap-3">
          <button type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" @click="saveItem(selectedItem)">
            Save changes
          </button>
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Add child to {{ selectedItem.title }}</h3>
          <div class="mt-2 grid gap-2 md:grid-cols-2">
            <input v-model="childCardForm.title" type="text" placeholder="Child title" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
            <input v-model="childCardForm.quantity_total" type="number" min="0" step="0.01" placeholder="Qty total" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
            <input v-model="childCardForm.quantity_completed" type="number" min="0" step="0.01" placeholder="Qty done" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
            <textarea v-model="childCardForm.description" rows="2" placeholder="Child description" class="md:col-span-2 rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
          <div class="mt-3 flex gap-2">
            <button type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400" @click="addChildFromCard">
              Save child
            </button>
            <button type="button" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-100" @click="childCardForm.title=''; childCardForm.description=''; childCardForm.quantity_total=''; childCardForm.quantity_completed=''">
              Clear
            </button>
          </div>
        </div>
      </section>
    </div>
  </AppShell>
</template>
