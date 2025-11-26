<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import CalendarMonth from '../components/CalendarMonth.vue'
import { fetchAttendanceCalendar, fetchDutyRosters, createDutyRoster, assignDutyRoster, fetchEmployees } from '../api'

const employees = ref([])
const rosters = ref([])
const calendarMap = ref({})
const weekStart = 0 // Sunday start
const monthStart = ref(startOfMonth(new Date()))
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const selectedEmployeeId = ref('')
const search = ref('')
const error = ref('')
const rowAssignments = reactive({})

const rosterForm = ref({
  name: '',
  code: '',
  starts_at: '',
  ends_at: '',
  off_days: [],
  notes: '',
})

const rosterAssignForm = ref({
  roster_id: '',
  employee_ids: [],
  start_date: '',
  end_date: '',
})

const monthLabel = computed(() => monthStart.value.toLocaleString('default', { month: 'long', year: 'numeric' }))
const monthDateIso = computed(() => monthStart.value.toISOString())

const days = computed(() => buildMonthDays(monthStart.value, calendarMap.value, weekStart))

const flatEvents = computed(() =>
  days.value.flatMap((day) => day.events.map((event) => ({ ...event, date: day.date })))
)

function buildMonthDays(startDate, map, startOfWeek = 0) {
  const start = new Date(startDate)
  const startDay = (start.getDay() - startOfWeek + 7) % 7
  const rows = []
  for (let i = 0; i < startDay; i++) {
    const d = new Date(start)
    d.setDate(d.getDate() - (startDay - i))
    rows.push(buildDay(d, false, map))
  }

  const cursor = new Date(start)
  while (cursor.getMonth() === start.getMonth()) {
    rows.push(buildDay(cursor, true, map))
    cursor.setDate(cursor.getDate() + 1)
  }

  while (rows.length % 7 !== 0 || rows.length < 42) {
    rows.push(buildDay(cursor, false, map))
    cursor.setDate(cursor.getDate() + 1)
  }
  return rows
}

function buildDay(dateObj, isCurrentMonth, map) {
  const dateStr = dateObj.toISOString().slice(0, 10)
  return {
    date: dateStr,
    isCurrentMonth,
    isToday: dateStr === new Date().toISOString().slice(0, 10),
    isSelected: dateStr === selectedDate.value,
    events: map[dateStr] || [],
  }
}

const nextMonth = () => {
  const d = new Date(monthStart.value)
  d.setMonth(d.getMonth() + 1, 1)
  monthStart.value = startOfMonth(d)
}

const prevMonth = () => {
  const d = new Date(monthStart.value)
  d.setMonth(d.getMonth() - 1, 1)
  monthStart.value = startOfMonth(d)
}

function startOfMonth(date) {
  const d = new Date(date)
  d.setDate(1)
  d.setHours(0, 0, 0, 0)
  return d
}

async function loadEmployees() {
  const { data } = await fetchEmployees({ per_page: 200 })
  employees.value = data.data || []
  // hydrate rowAssignments defaults
  employees.value.forEach((emp) => {
    if (!rowAssignments[emp.id]) {
      rowAssignments[emp.id] = {
        roster_id: '',
        start_date: '',
        end_date: '',
      }
    }
  })
}

async function loadRosters() {
  const { data } = await fetchDutyRosters()
  rosters.value = data.data || []
}

async function loadCalendar() {
  const start = new Date(monthStart.value)
  const end = new Date(monthStart.value)
  end.setMonth(end.getMonth() + 1)
  end.setDate(0)

  const params = {
    start_date: start.toISOString().slice(0, 10),
    end_date: end.toISOString().slice(0, 10),
  }
  if (selectedEmployeeId.value) params.employee_id = selectedEmployeeId.value

  try {
    const { data } = await fetchAttendanceCalendar(params)
    const map = {}
    ;(data.days || []).forEach((day) => {
      const rosterEvents = (day.events || []).filter((ev) => ev.type === 'roster')
      map[day.date] = rosterEvents.map((ev) => ({ ...ev, href: ev.href || '#' }))
    })
    calendarMap.value = map
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load roster calendar'
  }
}

async function submitRoster() {
  error.value = ''
  try {
    rosterForm.value.off_days = (rosterForm.value.off_days || []).map((v) => Number(v))
    await createDutyRoster({ ...rosterForm.value })
    rosterForm.value = { name: '', code: '', starts_at: '', ends_at: '', off_days: [], notes: '' }
    await loadRosters()
    await loadCalendar()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create roster'
  }
}

async function submitAssignment() {
  error.value = ''
  if (!rosterAssignForm.value.roster_id) {
    error.value = 'Select a roster before assigning.'
    return
  }
  if (!rosterAssignForm.value.employee_ids.length) {
    error.value = 'Select at least one employee to assign.'
    return
  }
  if (!rosterAssignForm.value.start_date || !rosterAssignForm.value.end_date) {
    error.value = 'Select a start and end date.'
    return
  }
  try {
    await assignDutyRoster(rosterAssignForm.value.roster_id, {
      employee_ids: rosterAssignForm.value.employee_ids,
      start_date: rosterAssignForm.value.start_date,
      end_date: rosterAssignForm.value.end_date,
    })
    await loadCalendar()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to assign roster'
  }
}

async function assignSingle(employeeId) {
  error.value = ''
  const payload = rowAssignments[employeeId] || {}
  if (!payload.roster_id) {
    error.value = 'Select a roster before assigning.'
    return
  }
  if (!payload.start_date || !payload.end_date) {
    error.value = 'Select start and end dates.'
    return
  }
  try {
    await assignDutyRoster(payload.roster_id, {
      employee_ids: [employeeId],
      start_date: payload.start_date,
      end_date: payload.end_date,
    })
    await loadCalendar()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to assign roster'
  }
}

const filteredEmployees = computed(() => {
  const q = (search.value || '').toLowerCase()
  if (!q) return employees.value
  return employees.value.filter((e) => e.name?.toLowerCase().includes(q) || e.employee_id?.toLowerCase().includes(q))
})

function selectDay(date) {
  selectedDate.value = date
}

onMounted(async () => {
  await Promise.all([loadEmployees(), loadRosters()])
  await loadCalendar()
})

watch([monthStart, selectedEmployeeId], () => {
  loadCalendar()
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Duty rosters</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Duty rosters</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create rosters and assign them to employees across date ranges.</p>
          </div>
          <div class="flex gap-3">
            <select
              v-model="selectedEmployeeId"
              class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white"
            >
              <option value="">All employees</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              @click="loadCalendar"
            >
              Refresh
            </button>
          </div>
      </div>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900 lg:col-span-2">
          <CalendarMonth
            :month-label="monthLabel"
            :month-date="monthDateIso"
            :days="days"
            :events="flatEvents"
            :show-add-button="false"
            :week-start="weekStart"
            @prev="prevMonth"
            @next="nextMonth"
            @today="monthStart = startOfMonth(new Date())"
            @select="selectDay"
          />
      </section>

      <div class="space-y-4">
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Create duty roster</h2>
            <div class="mt-3 space-y-3">
              <input v-model="rosterForm.name" type="text" placeholder="Name" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              <div class="grid grid-cols-2 gap-2">
                <input v-model="rosterForm.starts_at" type="time" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                <input v-model="rosterForm.ends_at" type="time" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
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
                @click="submitRoster"
              >
                Save roster
              </button>
            </div>
          </section>

          <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Assign roster (bulk)</h2>
            <div class="mt-3 space-y-3">
              <select v-model="rosterAssignForm.roster_id" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                <option value="">Select roster</option>
                <option v-for="r in rosters" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
              <select
                v-model="rosterAssignForm.employee_ids"
                multiple
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              >
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
              </select>
              <div class="grid grid-cols-2 gap-2">
                <input v-model="rosterAssignForm.start_date" type="date" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                <input v-model="rosterAssignForm.end_date" type="date" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <button
                type="button"
                class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="submitAssignment"
              >
                Apply roster
              </button>
            </div>
          </section>
        </div>
      </div>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Assign to specific employee</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Search employees and apply a roster to a date range.</p>
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Search employees"
            class="w-full max-w-xs rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
          />
        </div>
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Roster</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</th>
                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-if="filteredEmployees.length === 0">
                <td colspan="5" class="px-3 py-3 text-center text-sm text-gray-500 dark:text-gray-400">No employees found.</td>
              </tr>
              <tr v-for="emp in filteredEmployees" :key="emp.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-3 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ emp.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ emp.employee_id }}</div>
                </td>
                <td class="px-3 py-3 text-sm">
                  <select
                    v-model="rowAssignments[emp.id].roster_id"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                  >
                    <option value="">Select roster</option>
                    <option v-for="r in rosters" :key="r.id" :value="r.id">{{ r.name }}</option>
                  </select>
                </td>
                <td class="px-3 py-3 text-sm">
                  <input
                    v-model="rowAssignments[emp.id].start_date"
                    type="date"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                  />
                </td>
                <td class="px-3 py-3 text-sm">
                  <input
                    v-model="rowAssignments[emp.id].end_date"
                    type="date"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                  />
                </td>
                <td class="px-3 py-3 text-right">
                  <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    @click="assignSingle(emp.id)"
                  >
                    Assign
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <p v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </p>
    </div>
  </AppShell>
</template>
