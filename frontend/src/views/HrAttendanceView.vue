<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import CalendarMonth from '../components/CalendarMonth.vue'
import { fetchAttendance, fetchAttendanceCalendar, fetchEmployees, createAttendance } from '../api'

const employees = ref([])
const attendanceList = ref([])
const attendanceMeta = ref(null)
const loading = ref(false)
const error = ref('')
const calendarMap = ref({})

const monthStart = ref(startOfMonth(new Date()))
const selectedDate = ref(new Date().toISOString().slice(0, 10))
const selectedEmployeeId = ref('')

const attendanceForm = ref({
  employee_id: '',
  attendance_date: new Date().toISOString().slice(0, 10),
  status: 'present',
  check_in: '',
  check_out: '',
  notes: '',
  late_minutes: 0,
})

const monthLabel = computed(() =>
  monthStart.value.toLocaleString('default', { month: 'long', year: 'numeric' })
)

const monthDateIso = computed(() => monthStart.value.toISOString())

const weekStart = 0 // Sunday

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
}

async function loadAttendance(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchAttendance({
      page,
      employee_id: selectedEmployeeId.value || undefined,
      per_page: 15,
    })
    attendanceList.value = data.data || []
    attendanceMeta.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load attendance'
  } finally {
    loading.value = false
  }
}

async function loadCalendar() {
  const start = new Date(monthStart.value)
  const end = new Date(monthStart.value)
  end.setMonth(end.getMonth() + 1)
  end.setDate(0) // last day of month

  const params = {
    start_date: start.toISOString().slice(0, 10),
    end_date: end.toISOString().slice(0, 10),
  }
  if (selectedEmployeeId.value) params.employee_id = selectedEmployeeId.value

  try {
    const { data } = await fetchAttendanceCalendar(params)
    const map = {}
    ;(data.days || []).forEach((day) => {
      const filtered = (day.events || []).filter((ev) => ev.type === 'attendance')
      map[day.date] = filtered.map((ev) => ({
        ...ev,
        href: ev.href || '#',
      }))
    })
    calendarMap.value = map
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load calendar'
  }
}

async function submitAttendance() {
  error.value = ''
  try {
    await createAttendance({ ...attendanceForm.value })
    await Promise.all([loadAttendance(), loadCalendar()])
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save attendance'
  }
}

function selectDay(date) {
  selectedDate.value = date
  attendanceForm.value.attendance_date = date
}

onMounted(async () => {
  await loadEmployees()
  await Promise.all([loadAttendance(), loadCalendar()])
})

watch([monthStart, selectedEmployeeId], () => {
  loadCalendar()
  loadAttendance()
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Attendance</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance & Duty Roster</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage duty rosters and attendance per employee.</p>
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
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Record attendance</h2>
            <div class="mt-3 space-y-3">
              <select v-model="attendanceForm.employee_id" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                <option value="">Select employee</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
              </select>
              <div class="grid grid-cols-2 gap-2">
                <input v-model="attendanceForm.attendance_date" type="date" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                <select v-model="attendanceForm.status" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                  <option value="present">Present</option>
                  <option value="absent">Absent</option>
                  <option value="late">Late</option>
                  <option value="leave">Leave</option>
                  <option value="wfh">WFH</option>
                  <option value="off">Off</option>
                </select>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <input v-model="attendanceForm.check_in" type="datetime-local" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                <input v-model="attendanceForm.check_out" type="datetime-local" class="rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <input
                v-model.number="attendanceForm.late_minutes"
                type="number"
                min="0"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                placeholder="Late minutes"
              />
              <textarea
                v-model="attendanceForm.notes"
                rows="2"
                placeholder="Notes"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              ></textarea>
              <button
                type="button"
                class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="submitAttendance"
              >
                Save attendance
              </button>
            </div>
          </section>
        </div>
      </div>

      <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
          <thead class="bg-gray-50 dark:bg-white/5">
            <tr>
              <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
              <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
              <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
              <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Check-in</th>
              <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Check-out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
            <tr v-if="loading">
              <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Loading…</td>
            </tr>
            <tr v-else-if="attendanceList.length === 0">
              <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No attendance records found.</td>
            </tr>
            <tr v-else v-for="rec in attendanceList" :key="rec.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
              <td class="px-3 py-3 text-sm text-gray-900 dark:text-white">{{ rec.attendance_date }}</td>
              <td class="px-3 py-3 text-sm text-gray-900 dark:text-white">
                <div class="font-semibold">{{ rec.employee?.name || 'Unknown' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ rec.employee?.employee_id }}</div>
              </td>
              <td class="px-3 py-3 text-sm">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                  {{ rec.status }}
                </span>
              </td>
              <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">{{ rec.check_in || '—' }}</td>
              <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">{{ rec.check_out || '—' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="attendanceMeta" class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:border-white/10 dark:bg-gray-900/60 dark:text-gray-300">
          <div>Page {{ attendanceMeta.current_page }} of {{ attendanceMeta.last_page }}</div>
          <div class="flex gap-2">
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              :disabled="attendanceMeta.current_page <= 1 || loading"
              @click="loadAttendance(attendanceMeta.current_page - 1)"
            >
              Previous
            </button>
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              :disabled="attendanceMeta.current_page >= attendanceMeta.last_page || loading"
              @click="loadAttendance(attendanceMeta.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
        <p v-if="error" class="border-t border-red-100 bg-red-50 px-4 py-2 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
          {{ error }}
        </p>
      </section>
    </div>
  </AppShell>
</template>
