<script setup>
import { computed, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'

// Placeholder data until backend API is wired in
const employees = ref([
  {
    id: 'emp-1',
    name: 'Aishath Latheefa',
    title: 'People Partner',
    department: 'HR',
    status: 'Active',
    location: 'Malé',
    manager: 'Ahmed Rasheed',
    startDate: '2023-02-10',
  },
  {
    id: 'emp-2',
    name: 'Hassan Zahir',
    title: 'Front Office Supervisor',
    department: 'Operations',
    status: 'Probation',
    location: 'Addu',
    manager: 'Aishath Latheefa',
    startDate: '2024-09-01',
  },
  {
    id: 'emp-3',
    name: 'Mariyam Shifana',
    title: 'Payroll Specialist',
    department: 'Finance',
    status: 'Active',
    location: 'Malé',
    manager: 'Ahmed Rasheed',
    startDate: '2022-07-18',
  },
])

const search = ref('')
const departmentFilter = ref('all')
const statusFilter = ref('all')

const departments = computed(() => {
  const opts = new Set(employees.value.map((e) => e.department))
  return ['all', ...Array.from(opts)]
})

const statuses = ['all', 'Active', 'Probation', 'On Leave', 'Exited']

const filtered = computed(() => {
  return employees.value.filter((emp) => {
    const matchesSearch =
      emp.name.toLowerCase().includes(search.value.toLowerCase()) ||
      emp.title.toLowerCase().includes(search.value.toLowerCase())

    const matchesDept = departmentFilter.value === 'all' || emp.department === departmentFilter.value
    const matchesStatus = statusFilter.value === 'all' || emp.status === statusFilter.value

    return matchesSearch && matchesDept && matchesStatus
  })
})

const activeCount = computed(() => employees.value.filter((e) => e.status === 'Active').length)
const probationCount = computed(() => employees.value.filter((e) => e.status === 'Probation').length)
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

      <!-- Filters -->
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div class="relative w-full lg:max-w-sm">
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
          <div class="flex flex-wrap gap-3">
            <select
              v-model="departmentFilter"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
            >
              <option
                v-for="dept in departments"
                :key="dept"
                :value="dept"
              >
                {{ dept === 'all' ? 'All departments' : dept }}
              </option>
            </select>

            <select
              v-model="statusFilter"
              class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
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

      <!-- Table -->
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
                <div class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ emp.location }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                {{ emp.title }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                {{ emp.department }}
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
                {{ emp.manager }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                {{ emp.startDate }}
              </td>
              <td class="px-4 py-3 text-right text-sm">
                <div class="flex justify-end gap-2">
                  <button
                    type="button"
                    class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:border-indigo-400 hover:text-indigo-600 dark:border-white/10 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-white"
                  >
                    View
                  </button>
                  <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                  >
                    Manage
                  </button>
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
      </div>
    </div>
  </AppShell>
</template>
