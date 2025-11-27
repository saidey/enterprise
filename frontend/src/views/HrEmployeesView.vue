<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchDepartmentTree, fetchEmployees, createEmployee, assignEmployeeToUser, createEmployeeInvite } from '../api'
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxLabel,
  ComboboxOption,
  ComboboxOptions,
} from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

const employees = ref([])
const loading = ref(false)
const error = ref('')

const search = ref('')
const statusFilter = ref('all')
const selectedDeptId = ref(null)

const assignEmail = ref('')
const assignEmployeeId = ref(null)
const assignMessage = ref('')
const assignError = ref('')

const inviteLinks = ref({})
const inviteLoadingId = ref(null)
const inviteError = ref('')

const showCreate = ref(false)
const createForm = ref({
  employee_id: '',
  name: '',
  title: '',
  status: 'active',
  start_date: '',
  email: '',
  department_id: '',
  user_email: '',
})
const createMessage = ref('')
const createError = ref('')

const departmentTree = ref([])
const deptError = ref('')
const deptLoading = ref(false)
const deptQuery = ref('')
const createDeptQuery = ref('')

const statuses = ['all', 'active', 'probation', 'on_leave', 'exited']

const flatDepartments = computed(() => {
  const list = []
  const walk = (nodes, depth = 0) => {
    nodes.forEach((n) => {
      list.push({ ...n, depth })
      if (n.children?.length) {
        walk(n.children, depth + 1)
      }
    })
  }
  walk(departmentTree.value || [], 0)
  return list
})

const selectedDeptName = computed(() => {
  const hit = flatDepartments.value.find((d) => d.id === selectedDeptId.value)
  return hit?.name || null
})

const selectedDept = computed({
  get: () => flatDepartments.value.find((d) => d.id === selectedDeptId.value) || null,
  set: (val) => {
    selectedDeptId.value = val?.id || null
    deptQuery.value = ''
  },
})

const filteredDeptOptions = computed(() => {
  const q = deptQuery.value.toLowerCase()
  if (!q) return flatDepartments.value
  return flatDepartments.value.filter((d) => d.name.toLowerCase().includes(q))
})
const queryDeptOption = computed(() => (deptQuery.value ? { id: null, name: deptQuery.value, depth: 0 } : null))

const selectedCreateDept = computed({
  get: () => flatDepartments.value.find((d) => d.id === createForm.value.department_id) || null,
  set: (val) => {
    createForm.value.department_id = val?.id || ''
    createDeptQuery.value = ''
  },
})

const filteredCreateDeptOptions = computed(() => {
  const q = createDeptQuery.value.toLowerCase()
  if (!q) return flatDepartments.value
  return flatDepartments.value.filter((d) => d.name.toLowerCase().includes(q))
})
const queryCreateDeptOption = computed(() => (createDeptQuery.value ? { id: null, name: createDeptQuery.value, depth: 0 } : null))

const filtered = computed(() => {
  return employees.value.filter((emp) => {
    const matchesSearch =
      emp.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      emp.title?.toLowerCase().includes(search.value.toLowerCase()) ||
      emp.employee_id?.toLowerCase().includes(search.value.toLowerCase())

    const matchesDept = !selectedDeptId.value || emp.department_id === selectedDeptId.value
    const matchesStatus = statusFilter.value === 'all' || emp.status === statusFilter.value

    return matchesSearch && matchesDept && matchesStatus
  })
})

const activeCount = computed(() => employees.value.filter((e) => e.status === 'active').length)
const probationCount = computed(() => employees.value.filter((e) => e.status === 'probation').length)

const loadDepartmentTree = async () => {
  deptLoading.value = true
  deptError.value = ''
  try {
    const { data } = await fetchDepartmentTree()
    departmentTree.value = data.data || []
  } catch (e) {
    console.error(e)
    deptError.value = 'Failed to load department tree.'
  } finally {
    deptLoading.value = false
  }
}

const loadEmployees = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchEmployees({
      search: search.value || undefined,
      department_id: selectedDeptId.value || undefined,
      status: statusFilter.value === 'all' ? undefined : statusFilter.value,
      per_page: 100,
    })
    employees.value = data.data || []
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load employees.'
  } finally {
    loading.value = false
  }
}

const clearDept = () => {
  selectedDeptId.value = null
  deptQuery.value = ''
}

onMounted(() => {
  loadDepartmentTree()
  loadEmployees()
})

const openAssign = (empId) => {
  assignEmployeeId.value = empId
  assignEmail.value = ''
  assignMessage.value = ''
  assignError.value = ''
}

const submitAssign = async () => {
  if (!assignEmployeeId.value || !assignEmail.value) return
  assignError.value = ''
  assignMessage.value = ''
  try {
    await assignEmployeeToUser(assignEmployeeId.value, assignEmail.value)
    assignMessage.value = 'User linked.'
    await loadEmployees()
  } catch (e) {
    console.error(e)
    assignError.value = e.response?.data?.message || 'Failed to link user.'
  }
}

const sendInvite = async (empId) => {
  inviteError.value = ''
  inviteLoadingId.value = empId
  try {
    const { data } = await createEmployeeInvite({ employee_id: empId })
    const token = data.data?.token
    if (token) {
      inviteLinks.value[empId] = `${window.location.origin}/apps/hr/claim?token=${token}`
    }
  } catch (e) {
    console.error(e)
    inviteError.value = e.response?.data?.message || 'Failed to generate invite.'
  } finally {
    inviteLoadingId.value = null
  }
}

const submitCreate = async () => {
  createError.value = ''
  createMessage.value = ''
  try {
    await createEmployee(createForm.value)
    createMessage.value = 'Employee created.'
    showCreate.value = false
    Object.assign(createForm.value, {
      employee_id: '',
      name: '',
      title: '',
      status: 'active',
      start_date: '',
      email: '',
      department_id: '',
      user_email: '',
    })
    await loadEmployees()
  } catch (e) {
    console.error(e)
    createError.value = e.response?.data?.message || 'Failed to create employee.'
  }
}
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
          <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
            HR / Employees
          </p>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Employee directory
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Search, filter, and manage employees for the current company and operation.
          </p>
        </div>
        <div class="flex gap-3">
          <button
            type="button"
            @click="showCreate = true"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
          >
            Add employee
          </button>
          <button
            type="button"
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-indigo-400 hover:text-indigo-600 dark:border-white/10 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-white"
          >
            Import
          </button>
        </div>
      </div>

      <!-- Summary -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Active</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ activeCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Currently on payroll</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Probation</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ probationCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">New hires under review</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ employees.length }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">All employees in this operation</p>
        </div>
      </div>



          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Department
              </p>
              <button
                v-if="selectedDeptId"
                type="button"
                @click="clearDept"
                class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
              >
                Clear
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Cluster → division → department → section → unit
            </p>

            <div class="mt-3">
              <Combobox as="div" v-model="selectedDept" @update:modelValue="deptQuery = ''">
                <ComboboxLabel class="sr-only">Department</ComboboxLabel>
                <div class="relative">
                  <ComboboxInput
                    class="block w-full rounded-md bg-white py-1.5 pr-12 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
                    :display-value="(dept) => dept?.name"
                    @change="deptQuery = $event.target.value"
                    @blur="deptQuery = ''"
                    placeholder="Search department"
                  />
                  <ComboboxButton class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-hidden">
                    <ChevronDownIcon class="size-5 text-gray-400" aria-hidden="true" />
                  </ComboboxButton>

                  <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                    <ComboboxOptions
                      v-if="filteredDeptOptions.length > 0 || deptQuery.length > 0"
                      class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                    >
                      <ComboboxOption v-if="queryDeptOption" :value="queryDeptOption" as="template" v-slot="{ active }">
                        <li
                          :class="[
                            'relative cursor-default select-none px-3 py-2',
                            active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                          ]"
                        >
                          <span class="block truncate">{{ deptQuery }}</span>
                        </li>
                      </ComboboxOption>
                      <ComboboxOption
                        v-for="dept in filteredDeptOptions"
                        :key="dept.id"
                        :value="dept"
                        as="template"
                        v-slot="{ active }"
                      >
                        <li
                          :class="[
                            'relative cursor-default select-none px-3 py-2',
                            active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                          ]"
                        >
                          <div class="flex">
                            <span class="truncate">{{ dept.name }}</span>
                            <span
                              :class="[
                                'ml-2 truncate',
                                active ? 'text-white' : 'text-gray-500 dark:text-gray-400',
                              ]"
                            >
                              {{ dept.parent_id ? 'Child' : 'Root' }}
                            </span>
                          </div>
                        </li>
                      </ComboboxOption>
                    </ComboboxOptions>
                  </transition>
                </div>
              </Combobox>
            </div>
          </div>

                <div class="grid gap-6 lg:grid-cols-[280px,1fr]">
        <!-- Left rail: filters + department tree -->
        <div class="space-y-4">
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="relative">
              <input
                v-model="search"
                type="search"
                placeholder="Search by name or title"
                class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 pr-10 text-sm text-gray-900 shadow-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 5.65 5.65a7.5 7.5 0 0 0 10.6 10.6Z" />
                </svg>
              </span>
            </div>

            <div class="mt-3">
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Status
              </label>
              <select
                v-model="statusFilter"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              >
                <option
                  v-for="status in statuses"
                  :key="status"
                  :value="status"
                >
                  {{ status === 'all' ? 'All statuses' : status }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Main table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/5">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Name
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Title
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Department
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Status
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Manager
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Start date
                </th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
              <tr v-for="emp in filtered" :key="emp.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                  {{ emp.name }}
                  <div class="text-xs font-normal text-gray-500 dark:text-gray-400">
                    {{ emp.employee_id || '—' }}
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ emp.title }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ emp.department?.name || '—' }}
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                    :class="emp.status === 'Active'
                      ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                      : emp.status === 'Probation'
                      ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-300'"
                  >
                    {{ emp.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ emp.user?.name || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ emp.start_date || '—' }}
                </td>
                <td class="px-4 py-3 text-right text-sm">
                  <div class="flex flex-col items-end gap-2">
                    <button
                      type="button"
                      @click="openAssign(emp.id)"
                      class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:border-indigo-400 hover:text-indigo-600 dark:border-white/10 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-white"
                    >
                      Link user
                    </button>
                    <button
                      type="button"
                      class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    >
                      Manage
                    </button>
                    <button
                      type="button"
                      class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:border-indigo-400 hover:text-indigo-600 disabled:opacity-50 dark:border-white/10 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-white"
                      :disabled="inviteLoadingId === emp.id"
                      @click="sendInvite(emp.id)"
                    >
                      {{ inviteLoadingId === emp.id ? 'Generating…' : 'Invite link' }}
                    </button>
                    <div v-if="inviteLinks[emp.id]" class="w-full rounded-md border border-gray-200 bg-gray-50 p-2 text-left text-xs dark:border-white/10 dark:bg-white/5">
                      <p class="font-semibold text-gray-700 dark:text-gray-200">Share link</p>
                      <input
                        :value="inviteLinks[emp.id]"
                        readonly
                        class="mt-1 w-full truncate rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] dark:border-white/10 dark:bg-gray-900 dark:text-white"
                      />
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No employees match your filters yet. Try clearing filters or add a new employee.
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="loading" class="border-t border-gray-200 px-4 py-3 text-sm text-gray-500 dark:border-white/5 dark:text-gray-400">
            Loading employees…
          </div>
          <div v-if="error" class="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
            {{ error }}
          </div>
          <div v-if="inviteError" class="border-t border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/30 dark:bg-amber-950/30 dark:text-amber-200">
            {{ inviteError }}
          </div>
        </div>
      </div>
    </div>

    <!-- Create employee modal -->
    <div
      v-if="showCreate"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
      <div class="w-full max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Add employee</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Capture key details and optional login link.</p>
          </div>
          <button
            type="button"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
            @click="showCreate = false"
          >
            ✕
          </button>
        </div>

        <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Employee ID</label>
              <input
                v-model="createForm.employee_id"
                type="text"
                placeholder="e.g. EMP-001"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Title</label>
              <input
                v-model="createForm.title"
                type="text"
                placeholder="e.g. People Partner"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Full name</label>
            <input
              v-model="createForm.name"
              type="text"
              required
              placeholder="Employee full name"
              class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
            />
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Status</label>
              <select
                v-model="createForm.status"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              >
                <option value="active">Active</option>
                <option value="probation">Probation</option>
                <option value="on_leave">On leave</option>
                <option value="exited">Exited</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Start date</label>
              <input
                v-model="createForm.start_date"
                type="date"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Work email</label>
              <input
                v-model="createForm.email"
                type="email"
                placeholder="name@company.com"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Link to user by email (optional)</label>
              <input
                v-model="createForm.user_email"
                type="email"
                placeholder="existing user email"
                class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
              />
            </div>
          </div>

          <div>
            <Combobox as="div" v-model="selectedCreateDept" @update:modelValue="createDeptQuery = ''">
              <ComboboxLabel class="block text-sm/6 font-medium text-gray-900 dark:text-white">Department</ComboboxLabel>
              <div class="relative mt-2">
                <ComboboxInput
                  class="block w-full rounded-md bg-white py-1.5 pr-12 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
                  :display-value="(dept) => dept?.name"
                  @change="createDeptQuery = $event.target.value"
                  @blur="createDeptQuery = ''"
                  placeholder="Select department"
                />
                <ComboboxButton class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-hidden">
                  <ChevronDownIcon class="size-5 text-gray-400" aria-hidden="true" />
                </ComboboxButton>

                <transition leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
                  <ComboboxOptions
                    v-if="filteredCreateDeptOptions.length > 0 || createDeptQuery.length > 0"
                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-black/5 sm:text-sm dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10"
                  >
                    <ComboboxOption v-if="queryCreateDeptOption" :value="queryCreateDeptOption" as="template" v-slot="{ active }">
                      <li
                        :class="[
                          'relative cursor-default select-none px-3 py-2',
                          active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                        ]"
                      >
                        <span class="block truncate">{{ createDeptQuery }}</span>
                      </li>
                    </ComboboxOption>
                    <ComboboxOption
                      v-for="dept in filteredCreateDeptOptions"
                      :key="dept.id"
                      :value="dept"
                      as="template"
                      v-slot="{ active }"
                    >
                      <li
                        :class="[
                          'relative cursor-default select-none px-3 py-2',
                          active ? 'bg-indigo-600 text-white outline-hidden dark:bg-indigo-500' : 'text-gray-900 dark:text-white',
                        ]"
                      >
                        <div class="flex">
                          <span class="truncate">{{ dept.name }}</span>
                          <span
                            :class="[
                              'ml-2 truncate',
                              active ? 'text-white' : 'text-gray-500 dark:text-gray-400',
                            ]"
                          >
                            {{ dept.parent_id ? 'Child' : 'Root' }}
                          </span>
                        </div>
                      </li>
                    </ComboboxOption>
                  </ComboboxOptions>
                </transition>
              </div>
            </Combobox>
          </div>

          <div class="flex items-center justify-between pt-2">
            <div class="text-xs text-red-500" v-if="createError">{{ createError }}</div>
            <div class="text-xs text-emerald-500" v-if="createMessage">{{ createMessage }}</div>
            <div class="flex gap-2">
              <button
                type="button"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-gray-300 dark:border-white/10 dark:text-gray-200"
                @click="showCreate = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              >
                Save
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Assign user sheet -->
    <div
      v-if="assignEmployeeId"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
      <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Link user</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Search by email to connect an employee to an existing user.</p>
          </div>
          <button
            type="button"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white"
            @click="assignEmployeeId = null"
          >
            ✕
          </button>
        </div>

        <div class="mt-3 space-y-2">
          <input
            v-model="assignEmail"
            type="email"
            placeholder="user@example.com"
            class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
          />
          <div class="text-xs text-red-500" v-if="assignError">{{ assignError }}</div>
          <div class="text-xs text-emerald-500" v-if="assignMessage">{{ assignMessage }}</div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button
            type="button"
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-gray-300 dark:border-white/10 dark:text-gray-200"
            @click="assignEmployeeId = null"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            @click="submitAssign"
          >
            Link
          </button>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script>
// Lightweight inline recursive component for the department tree
export default {
  components: {
    DepartmentNode: {
      props: {
        node: { type: Object, required: true },
        selected: { type: [String, null], default: null },
      },
      emits: ['select'],
      setup(props, { emit }) {
        const toggle = () => emit('select', props.node.id)
        return { toggle }
      },
      template: `
        <div class="space-y-1">
          <button
            type="button"
            @click="toggle"
            class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-sm"
            :class="selected === node.id
              ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200'
              : 'text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5'"
          >
            <span class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ node.type || 'unit' }}</span>
            <span class="truncate">{{ node.name }}</span>
          </button>
          <div v-if="node.children && node.children.length" class="border-l border-gray-200 pl-3 dark:border-white/10">
            <DepartmentNode
              v-for="child in node.children"
              :key="child.id"
              :node="child"
              :selected="selected"
              @select="$emit('select', $event)"
            />
          </div>
        </div>
      `,
    },
  },
}
</script>
