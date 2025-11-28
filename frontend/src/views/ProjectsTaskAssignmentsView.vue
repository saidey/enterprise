<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import {
  fetchProjects,
  fetchProjectTasks,
  fetchWbs,
  fetchProjectUsers,
  createProjectTask,
  updateProjectTask,
} from '../api'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { ChevronUpDownIcon } from '@heroicons/vue/16/solid'
import { CheckIcon } from '@heroicons/vue/20/solid'

const projects = ref([])
const selectedProjectId = ref('')
const tasks = ref([])
const wbs = ref([])
const users = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')
const creating = ref(false)

const statusOptions = [
  { value: 'not_started', label: 'Not started' },
  { value: 'in_progress', label: 'In progress' },
  { value: 'completed', label: 'Completed' },
]

const newTask = ref({
  name: '',
  due_date: '',
  status: 'not_started',
  wbs_item_id: '',
  assigned_to: '',
})

const selectedProject = computed(() => projects.value.find((p) => p.id === selectedProjectId.value) || null)
const wbsOptions = computed(() => {
  const flat = []
  const walk = (nodes) => {
    nodes.forEach((n) => {
      flat.push({ id: n.id, label: `${n.code} — ${n.title}` })
      if (n.children?.length) walk(n.children)
    })
  }
  walk(wbs.value || [])
  return flat
})

const displayUser = (id) => {
  const u = users.value.find((x) => x.id === id)
  if (!u) return ''
  return u.name ? `${u.name} ${u.email ? `(${u.email})` : ''}` : u.email || ''
}

const getWbsLabel = (id) => {
  const w = wbsOptions.value.find((x) => x.id === id)
  return w ? w.label : 'Unlinked'
}

async function loadProjects() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchProjects({ per_page: 100 })
    const payload = data.data || data || []
    projects.value = Array.isArray(payload.data) ? payload.data : payload
    if (projects.value.length && !selectedProjectId.value) {
      selectedProjectId.value = projects.value[0].id
      await loadProjectData(selectedProjectId.value)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load projects.'
  } finally {
    loading.value = false
  }
}

async function loadProjectData(projectId) {
  if (!projectId) return
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const [taskRes, wbsRes] = await Promise.all([
      fetchProjectTasks(projectId),
      fetchWbs(projectId),
    ])
    tasks.value = taskRes.data?.data || taskRes.data || []
    wbs.value = wbsRes.data?.data || wbsRes.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load project tasks.'
  } finally {
    loading.value = false
  }
}

async function loadUsers() {
  try {
    const { data } = await fetchProjectUsers()
    users.value = data.data || data || []
    if (!users.value.length) {
      error.value = 'No company users found. Add members to assign tasks.'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load users.'
  }
}

async function handleProjectChange() {
  await loadProjectData(selectedProjectId.value)
}

async function createTask() {
  if (!selectedProjectId.value) {
    error.value = 'Select a project first.'
    return
  }
  creating.value = true
  error.value = ''
  success.value = ''
  try {
    await createProjectTask(selectedProjectId.value, {
      name: newTask.value.name,
      due_date: newTask.value.due_date || null,
      status: newTask.value.status,
      wbs_item_id: newTask.value.wbs_item_id || null,
      assigned_to: newTask.value.assigned_to || null,
    })
    newTask.value = { name: '', due_date: '', status: 'not_started', wbs_item_id: '', assigned_to: '' }
    await loadProjectData(selectedProjectId.value)
    success.value = 'Task created.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create task.'
  } finally {
    creating.value = false
  }
}

async function saveTask(task) {
  success.value = ''
  error.value = ''
  try {
    await updateProjectTask(task.id, {
      status: task.status,
      wbs_item_id: task.wbs_item_id || null,
      assigned_to: task.assigned_to || null,
    })
    success.value = 'Task updated.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update task.'
  }
}

onMounted(async () => {
  await Promise.allSettled([loadProjects(), loadUsers()])
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / Task assignments</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Task assignments</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Assign tasks to users and link them to WBS items.</p>
      </header>

      <div class="grid gap-3 md:grid-cols-[260px,1fr]">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <Listbox v-model="selectedProjectId" @update:modelValue="handleProjectChange">
            <ListboxLabel class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</ListboxLabel>
            <div class="relative mt-2">
              <ListboxButton
                class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-2 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-gray-800/50 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
              >
                <span class="col-start-1 row-start-1 flex items-center gap-3 pr-6">
                  <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                    {{ selectedProject?.name?.[0] || '?' }}
                  </span>
                  <span class="block truncate">
                    {{ selectedProject ? `${selectedProject.name} (${selectedProject.code || '—'})` : 'Select a project' }}
                  </span>
                </span>
                <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
              </ListboxButton>
              <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                <ListboxOptions
                  class="absolute z-10 mt-1 max-h-64 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline-1 outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                >
                  <ListboxOption v-for="p in projects" :key="p.id" :value="p.id" v-slot="{ active, selected }">
                    <li
                      :class="[
                        active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                        'relative cursor-default py-2 pr-9 pl-3 select-none',
                      ]"
                    >
                      <div class="flex items-center gap-3">
                        <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                          {{ p.name?.[0] || '?' }}
                        </span>
                        <div class="min-w-0">
                          <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ p.name }}</span>
                          <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ p.code || 'No code' }}</span>
                        </div>
                      </div>
                      <span
                        v-if="selected"
                        :class="[
                          active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                          'absolute inset-y-0 right-0 flex items-center pr-4',
                        ]"
                      >
                        <CheckIcon class="size-5" aria-hidden="true" />
                      </span>
                    </li>
                  </ListboxOption>
                  <li v-if="!projects.length" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No projects.</li>
                </ListboxOptions>
              </transition>
            </div>
          </Listbox>
          <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            Choose a project to view its tasks and assign ownership + WBS linkage.
          </p>
        </div>

        <div class="space-y-3">
          <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
            Loading…
          </div>
          <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
            {{ error }}
          </div>
          <div v-else class="space-y-3">
            <div v-if="success" class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
              {{ success }}
            </div>

            <!-- Create task -->
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Create task</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-400">Add a task and link it to WBS + assignee.</p>
                </div>
                <button
                  type="button"
                  class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                  :disabled="creating"
                  @click="createTask"
                >
                  {{ creating ? 'Saving…' : 'Add task' }}
                </button>
              </div>
              <div class="mt-3 grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                  <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Name</label>
                  <input
                    v-model="newTask.name"
                    type="text"
                    class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm bg-white outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                    placeholder="Task title"
                  />
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Due date</label>
                  <input
                    v-model="newTask.due_date"
                    type="date"
                    class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm bg-white outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                  />
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Status</label>
                  <Listbox v-model="newTask.status">
                    <div class="relative mt-1">
                      <ListboxButton
                        class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-2 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                      >
                        <span class="col-start-1 row-start-1 flex items-center gap-2 pr-6">
                          <span class="block truncate">{{ statusOptions.find((s) => s.value === newTask.status)?.label || 'Select' }}</span>
                        </span>
                        <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
                      </ListboxButton>
                      <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                        <ListboxOptions
                          class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                        >
                          <ListboxOption v-for="opt in statusOptions" :key="opt.value" :value="opt.value" v-slot="{ active, selected }">
                            <li
                              :class="[
                                active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                'relative cursor-default py-2 pr-9 pl-3 select-none',
                              ]"
                            >
                              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ opt.label }}</span>
                              <span
                                v-if="selected"
                                :class="[
                                  active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                  'absolute inset-y-0 right-0 flex items-center pr-4',
                                ]"
                              >
                                <CheckIcon class="size-5" aria-hidden="true" />
                              </span>
                            </li>
                          </ListboxOption>
                        </ListboxOptions>
                      </transition>
                    </div>
                  </Listbox>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">WBS</label>
                  <Listbox v-model="newTask.wbs_item_id">
                    <div class="relative mt-1">
                      <ListboxButton
                        class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-2 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                      >
                        <span class="col-start-1 row-start-1 flex items-center gap-2 pr-6">
                          <span class="block truncate">
                            {{ wbsOptions.find((w) => w.id === newTask.wbs_item_id)?.label || 'Unlinked' }}
                          </span>
                        </span>
                        <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
                      </ListboxButton>
                      <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                        <ListboxOptions
                          class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                        >
                          <ListboxOption :value="''" v-slot="{ active, selected }">
                            <li
                              :class="[
                                active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                'relative cursor-default py-2 pr-9 pl-3 select-none',
                              ]"
                            >
                              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">Unlinked</span>
                              <span
                                v-if="selected"
                                :class="[
                                  active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                  'absolute inset-y-0 right-0 flex items-center pr-4',
                                ]"
                              >
                                <CheckIcon class="size-5" aria-hidden="true" />
                              </span>
                            </li>
                          </ListboxOption>
                          <ListboxOption v-for="opt in wbsOptions" :key="opt.id" :value="opt.id" v-slot="{ active, selected }">
                            <li
                              :class="[
                                active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                'relative cursor-default py-2 pr-9 pl-3 select-none',
                              ]"
                            >
                              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ opt.label }}</span>
                              <span
                                v-if="selected"
                                :class="[
                                  active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                  'absolute inset-y-0 right-0 flex items-center pr-4',
                                ]"
                              >
                                <CheckIcon class="size-5" aria-hidden="true" />
                              </span>
                            </li>
                          </ListboxOption>
                        </ListboxOptions>
                      </transition>
                    </div>
                  </Listbox>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Assignee</label>
                  <Listbox v-model="newTask.assigned_to">
                    <div class="relative mt-1">
                      <ListboxButton
                        class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-2 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                      >
                        <span class="col-start-1 row-start-1 flex items-center gap-2 pr-6">
                          <span class="block truncate">
                            {{ displayUser(newTask.assigned_to) || 'Unassigned' }}
                          </span>
                        </span>
                        <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
                      </ListboxButton>
                      <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                        <ListboxOptions
                          class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                        >
                          <ListboxOption :value="''" v-slot="{ active, selected }">
                            <li
                              :class="[
                                active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                'relative cursor-default py-2 pr-9 pl-3 select-none',
                              ]"
                            >
                              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">Unassigned</span>
                              <span
                                v-if="selected"
                                :class="[
                                  active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                  'absolute inset-y-0 right-0 flex items-center pr-4',
                                ]"
                              >
                                <CheckIcon class="size-5" aria-hidden="true" />
                              </span>
                            </li>
                          </ListboxOption>
                          <ListboxOption v-for="u in users" :key="u.id" :value="u.id" v-slot="{ active, selected }">
                            <li
                              :class="[
                                active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                'relative cursor-default py-2 pr-9 pl-3 select-none',
                              ]"
                            >
                              <div class="flex items-center gap-3">
                                <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                                  {{ (u.name || u.email || '?').charAt(0).toUpperCase() }}
                                </span>
                                <div class="min-w-0">
                                  <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ u.name || u.email }}</span>
                                  <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ u.email }}</span>
                                </div>
                              </div>
                              <span
                                v-if="selected"
                                :class="[
                                  active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                  'absolute inset-y-0 right-0 flex items-center pr-4',
                                ]"
                              >
                                <CheckIcon class="size-5" aria-hidden="true" />
                              </span>
                            </li>
                          </ListboxOption>
                        </ListboxOptions>
                      </transition>
                    </div>
                  </Listbox>
                </div>
              </div>
            </div>

            <!-- Tasks table -->
            <div class="relative overflow-visible rounded-xl border border-gray-200 shadow-sm dark:border-white/10">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Task</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">WBS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assignee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                  <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                      <div class="font-semibold">{{ task.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ task.phase?.name || '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                      {{ task.due_date || '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                      <div class="relative">
                        <Listbox v-model="task.wbs_item_id" @update:modelValue="saveTask(task)">
                          <div class="relative">
                            <ListboxButton
                              class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-xs dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                            >
                              <span class="col-start-1 row-start-1 flex items-center gap-2 pr-6">
                                <span class="block truncate">{{ getWbsLabel(task.wbs_item_id) }}</span>
                              </span>
                            <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
                          </ListboxButton>
                          <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                            <ListboxOptions
                              class="absolute left-0 top-full z-50 mt-1 max-h-60 w-72 overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                            >
                                <ListboxOption :value="''" v-slot="{ active, selected }">
                                  <li
                                    :class="[
                                      active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                      'relative cursor-default py-2 pr-9 pl-3 select-none',
                                    ]"
                                  >
                                    <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">Unlinked</span>
                                    <span
                                      v-if="selected"
                                      :class="[
                                        active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                        'absolute inset-y-0 right-0 flex items-center pr-4',
                                      ]"
                                    >
                                      <CheckIcon class="size-5" aria-hidden="true" />
                                    </span>
                                  </li>
                                </ListboxOption>
                                <ListboxOption v-for="opt in wbsOptions" :key="opt.id" :value="opt.id" v-slot="{ active, selected }">
                                  <li
                                    :class="[
                                      active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                      'relative cursor-default py-2 pr-9 pl-3 select-none',
                                    ]"
                                  >
                                    <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ opt.label }}</span>
                                    <span
                                      v-if="selected"
                                      :class="[
                                        active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                        'absolute inset-y-0 right-0 flex items-center pr-4',
                                      ]"
                                    >
                                      <CheckIcon class="size-5" aria-hidden="true" />
                                    </span>
                                  </li>
                                </ListboxOption>
                              </ListboxOptions>
                            </transition>
                          </div>
                        </Listbox>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                      <div class="relative">
                        <Listbox v-model="task.assigned_to" @update:modelValue="saveTask(task)">
                          <div class="relative">
                            <ListboxButton
                              class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-xs dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                            >
                              <span class="col-start-1 row-start-1 flex items-center gap-2 pr-6">
                                <span class="block truncate">{{ displayUser(task.assigned_to) || 'Unassigned' }}</span>
                              </span>
                            <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4 dark:text-gray-400" aria-hidden="true" />
                          </ListboxButton>
                          <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                            <ListboxOptions
                              class="absolute left-0 top-full z-50 mt-1 max-h-60 w-72 overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                            >
                                <ListboxOption :value="''" v-slot="{ active, selected }">
                                  <li
                                    :class="[
                                      active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                      'relative cursor-default py-2 pr-9 pl-3 select-none',
                                    ]"
                                  >
                                    <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">Unassigned</span>
                                    <span
                                      v-if="selected"
                                      :class="[
                                        active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                        'absolute inset-y-0 right-0 flex items-center pr-4',
                                      ]"
                                    >
                                      <CheckIcon class="size-5" aria-hidden="true" />
                                    </span>
                                  </li>
                                </ListboxOption>
                                <ListboxOption v-for="u in users" :key="u.id" :value="u.id" v-slot="{ active, selected }">
                                  <li
                                    :class="[
                                      active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                                      'relative cursor-default py-2 pr-9 pl-3 select-none',
                                    ]"
                                  >
                                    <div class="flex items-center gap-3">
                                      <span class="flex size-7 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                                        {{ (u.name || u.email || '?').charAt(0).toUpperCase() }}
                                      </span>
                                      <div class="min-w-0">
                                        <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ u.name || u.email }}</span>
                                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ u.email }}</span>
                                      </div>
                                    </div>
                                    <span
                                      v-if="selected"
                                      :class="[
                                        active ? 'text-white' : 'text-indigo-600 dark:text-indigo-400',
                                        'absolute inset-y-0 right-0 flex items-center pr-4',
                                      ]"
                                    >
                                      <CheckIcon class="size-5" aria-hidden="true" />
                                    </span>
                                  </li>
                                </ListboxOption>
                              </ListboxOptions>
                            </transition>
                          </div>
                        </Listbox>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                      <select
                        v-model="task.status"
                        class="w-full rounded-md border border-gray-200 px-2 py-1 text-xs outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:outline-white/10 dark:focus-visible:outline-indigo-500"
                        @change="saveTask(task)"
                      >
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                      </select>
                    </td>
                  </tr>
                  <tr v-if="selectedProjectId && !tasks.length">
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No tasks for this project.</td>
                  </tr>
                  <tr v-if="!selectedProjectId">
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Select a project to manage task assignments.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
