<script setup>
import { ref, onMounted, computed, defineComponent, h } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import {
  fetchHrSettings,
  updateHrSettings,
  fetchDepartmentTree,
  createDepartment,
  updateDepartment,
  deleteDepartment,
  fetchDutyRosters,
  createDutyRoster,
  updateDutyRoster,
  deleteDutyRoster,
} from '../api'

const loading = ref(false)
const error = ref('')
const form = ref({
  work_week_start: 0,
  default_off_days: [],
})
const departments = ref([])
const deptForm = ref({
  name: '',
  parent_id: '',
  id: '',
})
const deptLoading = ref(false)
const rosterForm = ref({
  name: '',
  code: '',
  starts_at: '',
  ends_at: '',
  off_days: [],
  notes: '',
})
const rosters = ref([])
const editingRosterId = ref('')
const activeTab = ref('workweek')
const tabs = [
  { key: 'workweek', label: 'Work week & off days' },
  { key: 'departments', label: 'Departments' },
  { key: 'rosters', label: 'Duty rosters' },
]
const DepartmentNode = defineComponent({
  name: 'DepartmentNode',
  props: {
    node: { type: Object, required: true },
  },
  emits: ['delete', 'edit'],
  setup(props, { emit }) {
    const onDelete = () => emit('delete', props.node.id)
    return () =>
      h('li', { class: 'space-y-2' }, [
        h(
          'div',
          { class: 'flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 dark:border-white/10' },
          [
            h('span', props.node.name),
            h('div', { class: 'flex items-center gap-2' }, [
              h(
                'button',
                {
                  type: 'button',
                  class: 'text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400',
                  onClick: () => emit('edit', props.node),
                },
                'Edit'
              ),
              h(
                'button',
                {
                  type: 'button',
                  class: 'text-xs font-semibold text-red-600 hover:text-red-500 dark:text-red-400',
                  onClick: onDelete,
                },
                'Delete'
              ),
            ]),
          ]
        ),
        props.node.children && props.node.children.length
          ? h(
              'ul',
              { class: 'ml-4 space-y-2 border-l border-gray-200 pl-3 dark:border-white/10' },
              props.node.children.map((child) =>
                h(DepartmentNode, {
                  node: child,
                  onDelete: (id) => emit('delete', id),
                  onEdit: (node) => emit('edit', node),
                })
              )
            )
          : null,
      ])
  },
})

const flatDepartments = computed(() => {
  const out = []
  const walk = (nodes, depth = 0) => {
    nodes.forEach((n) => {
      out.push({ ...n, depth })
      if (n.children?.length) walk(n.children, depth + 1)
    })
  }
  walk(departments.value || [], 0)
  return out
})

const weekDays = [
  { label: 'Sunday', value: 0 },
  { label: 'Monday', value: 1 },
  { label: 'Tuesday', value: 2 },
  { label: 'Wednesday', value: 3 },
  { label: 'Thursday', value: 4 },
  { label: 'Friday', value: 5 },
  { label: 'Saturday', value: 6 },
]

async function loadSettings() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchHrSettings()
    form.value.work_week_start = data.data?.work_week_start ?? 0
    form.value.default_off_days = data.data?.default_off_days ?? []
    await loadDepartments()
    await loadRosters()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load HR settings'
  } finally {
    loading.value = false
  }
}

async function loadDepartments() {
  deptLoading.value = true
  try {
    const { data } = await fetchDepartmentTree()
    departments.value = data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load departments'
  } finally {
    deptLoading.value = false
  }
}

async function saveSettings() {
  loading.value = true
  error.value = ''
  try {
    const payload = {
      work_week_start: Number(form.value.work_week_start),
      default_off_days: (form.value.default_off_days || []).map((v) => Number(v)),
    }
    await updateHrSettings(payload)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save HR settings'
  } finally {
    loading.value = false
  }
}

async function saveDepartment() {
  error.value = ''
  if (!deptForm.value.name) {
    error.value = 'Department name is required.'
    return
  }
  try {
    if (deptForm.value.id) {
      await updateDepartment(deptForm.value.id, {
        name: deptForm.value.name,
        parent_id: deptForm.value.parent_id || null,
      })
    } else {
      await createDepartment({
        name: deptForm.value.name,
        parent_id: deptForm.value.parent_id || null,
      })
    }
    deptForm.value = { id: '', name: '', parent_id: '' }
    await loadDepartments()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save department'
  }
}

async function removeDepartment(id) {
  try {
    await deleteDepartment(id)
    await loadDepartments()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete department'
  }
}

function startEditDepartment(node) {
  deptForm.value = {
    id: node.id,
    name: node.name,
    parent_id: node.parent_id || '',
  }
}

async function loadRosters() {
  try {
    const { data } = await fetchDutyRosters()
    rosters.value = data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load rosters'
  }
}

async function saveRoster() {
  error.value = ''
  if (!rosterForm.value.name) {
    error.value = 'Roster name is required.'
    return
  }
  const hasStart = rosterForm.value.starts_at !== null && rosterForm.value.starts_at !== undefined && String(rosterForm.value.starts_at).trim() !== ''
  const hasEnd = rosterForm.value.ends_at !== null && rosterForm.value.ends_at !== undefined && String(rosterForm.value.ends_at).trim() !== ''
  if (!hasStart || !hasEnd) {
    error.value = 'Start and end times are required.'
    return
  }
  try {
    const normalizeTime = (t) => {
      if (!t) return null
      const trimmed = t.trim()
      if (/^\d{2}:\d{2}:\d{2}$/.test(trimmed)) return trimmed
      if (/^\d{2}:\d{2}$/.test(trimmed)) return `${trimmed}:00`
      return trimmed
    }
    const payload = {
      ...rosterForm.value,
      off_days: (rosterForm.value.off_days || []).map((v) => Number(v)),
      starts_at: normalizeTime(rosterForm.value.starts_at),
      ends_at: normalizeTime(rosterForm.value.ends_at),
    }
    if (editingRosterId.value) {
      await updateDutyRoster(editingRosterId.value, payload)
    } else {
      await createDutyRoster(payload)
    }
    rosterForm.value = { name: '', code: '', starts_at: '', ends_at: '', off_days: [], notes: '' }
    editingRosterId.value = ''
    await loadRosters()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save roster'
  }
}

function startEditRoster(roster) {
  editingRosterId.value = roster.id
  rosterForm.value = {
    name: roster.name || '',
    code: roster.code || '',
    starts_at: roster.starts_at ? roster.starts_at.slice(0, 5) : '',
    ends_at: roster.ends_at ? roster.ends_at.slice(0, 5) : '',
    off_days: roster.off_days || [],
    notes: roster.notes || '',
  }
}

async function removeRoster(id) {
  try {
    await deleteDutyRoster(id)
    if (editingRosterId.value === id) {
      editingRosterId.value = ''
      rosterForm.value = { name: '', code: '', starts_at: '', ends_at: '', off_days: [], notes: '' }
    }
    await loadRosters()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete roster'
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Settings</p>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">HR Settings</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Control tenant-specific HR defaults like work week start and off days.</p>
        </div>
      </header>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-white/10">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            :class="[
              activeTab === tab.key
                ? 'bg-indigo-50 text-indigo-700 dark:bg-white/10 dark:text-white'
                : 'text-gray-700 hover:text-indigo-700 dark:text-gray-300 dark:hover:text-white',
              'rounded-md px-3 py-1.5 text-sm font-semibold'
            ]"
            @click="activeTab = tab.key"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-if="activeTab === 'workweek'" class="space-y-4 pt-4">
          <div>
            <label class="block text-sm font-semibold text-gray-900 dark:text-white">Work week start day</label>
            <p class="text-xs text-gray-500 dark:text-gray-400">Choose the first day of the work week (e.g., Sunday for Maldives).</p>
            <select
              v-model="form.work_week_start"
              class="mt-2 w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white"
            >
              <option v-for="day in weekDays" :key="day.value" :value="day.value">{{ day.label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-900 dark:text-white">Default off days</label>
            <p class="text-xs text-gray-500 dark:text-gray-400">Select weekly off days that apply to most rosters.</p>
            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
              <label
                v-for="day in weekDays"
                :key="day.value"
                class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900"
              >
                <input
                  type="checkbox"
                  :value="day.value"
                  v-model="form.default_off_days"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-gray-900 dark:text-white">{{ day.label }}</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="button"
              class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              :disabled="loading"
              @click="saveSettings"
            >
              Save settings
            </button>
          </div>
        </div>

        <div v-else-if="activeTab === 'departments'" class="space-y-4 pt-4">
          <div class="mb-1 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Departments</h2>
              <p class="text-xs text-gray-500 dark:text-gray-400">Manage department tree for this tenant.</p>
            </div>
            <span v-if="deptLoading" class="text-xs text-gray-500 dark:text-gray-400">Loading…</span>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Name</label>
              <input
                v-model="deptForm.name"
                type="text"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                placeholder="Department name"
              />
              <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Parent (optional)</label>
              <select
                v-model="deptForm.parent_id"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white"
              >
                <option value="">No parent</option>
                <option
                  v-for="dept in flatDepartments"
                  :key="dept.id"
                  :value="dept.id"
                >
                  {{ '—'.repeat(dept.depth) }} {{ dept.name }}
                </option>
              </select>
              <button
                type="button"
                class="mt-2 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="saveDepartment"
                :disabled="deptLoading"
              >
                {{ deptForm.id ? 'Update department' : 'Add department' }}
              </button>
              <button
                v-if="deptForm.id"
                type="button"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                @click="deptForm = { id: '', name: '', parent_id: '' }"
              >
                Cancel edit
              </button>
            </div>
            <div>
              <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department tree</h3>
              <ul class="mt-2 space-y-2 text-sm text-gray-900 dark:text-white">
                <DepartmentNode
                  v-for="dept in departments"
                  :key="dept.id"
                  :node="dept"
                  @delete="removeDepartment"
                  @edit="startEditDepartment"
                />
              </ul>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'rosters'" class="space-y-4 pt-4">
          <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Duty roster templates</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Create roster templates here (assignment stays on roster page).</p>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div class="space-y-3">
              <input v-model="rosterForm.name" type="text" placeholder="Name" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="rosterForm.starts_at"
                  type="text"
                  inputmode="numeric"
                  autocomplete="off"
                  placeholder="HH:MM"
                  pattern="^([01]\\d|2[0-3]):[0-5]\\d$"
                  class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                />
                <input
                  v-model="rosterForm.ends_at"
                  type="text"
                  inputmode="numeric"
                  autocomplete="off"
                  placeholder="HH:MM"
                  pattern="^([01]\\d|2[0-3]):[0-5]\\d$"
                  class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Off days</label>
                <div class="grid grid-cols-3 gap-2 text-sm">
                  <label
                    v-for="(day, idx) in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']"
                    :key="day"
                    class="flex items-center gap-2 rounded-md border border-gray-200 px-2 py-1.5 dark:border-white/10 dark:bg-gray-900"
                  >
                    <input
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      :value="idx"
                      v-model="rosterForm.off_days"
                    />
                    <span class="text-gray-800 dark:text-gray-200">{{ day }}</span>
                  </label>
                </div>
              </div>
              <textarea
                v-model="rosterForm.notes"
                rows="2"
                placeholder="Notes"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              ></textarea>
              <button
                type="button"
                class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="saveRoster"
              >
                {{ editingRosterId ? 'Update roster' : 'Save roster' }}
              </button>
              <button
                v-if="editingRosterId"
                type="button"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                @click="() => { editingRosterId = ''; rosterForm = { name: '', code: '', starts_at: '', ends_at: '', off_days: [], notes: '' } }"
              >
                Cancel edit
              </button>
            </div>
            <div>
              <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Existing rosters</h3>
              <ul class="mt-2 space-y-2 text-sm text-gray-900 dark:text-white">
                <li
                  v-for="r in rosters"
                  :key="r.id"
                  class="rounded-md border border-gray-200 px-3 py-2 dark:border-white/10"
                >
                  <div class="font-semibold">{{ r.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ r.starts_at || '—' }} - {{ r.ends_at || '—' }} | Off: {{ (r.off_days || []).join(', ') || 'None' }}
                  </div>
                  <div class="mt-2 flex gap-2">
                    <button
                      type="button"
                      class="rounded-md border border-gray-200 px-2 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
                      @click="startEditRoster(r)"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                  class="rounded-md border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 shadow-sm hover:bg-red-50 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200"
                  @click="removeRoster(r.id)"
                >
                  Delete
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>
        </div>

        <p v-if="error" class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
          {{ error }}
        </p>
      </div>
    </div>
  </AppShell>
</template>
