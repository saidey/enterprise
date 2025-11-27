<script setup>
import { onMounted, ref, watch } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchSelfPayslips } from '../api'

const month = ref(currentMonthValue())
const payslips = ref([])
const loading = ref(false)
const error = ref('')

function currentMonthValue() {
  const d = new Date()
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  return `${y}-${m}`
}

async function loadPayslips() {
  if (!month.value) return
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchSelfPayslips({ month: month.value })
    payslips.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Unable to load payslips.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadPayslips()
})

watch(month, loadPayslips)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex flex-col gap-1">
          <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Payslips</p>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My payslips</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Download and review your monthly payslips.</p>
        </div>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            Month
            <input
              v-model="month"
              type="month"
              class="ml-3 rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
            />
          </label>
        </div>

        <div class="mt-4 space-y-3">
          <div v-if="loading" class="text-sm text-gray-600 dark:text-gray-300">Loading payslips…</div>
          <div v-else-if="error" class="text-sm text-red-600 dark:text-red-200">{{ error }}</div>
          <div v-else-if="!payslips.length" class="text-sm text-gray-500 dark:text-gray-400">No payslips for this month yet.</div>
          <ul v-else class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/5 dark:border-white/10">
            <li v-for="slip in payslips" :key="slip.id" class="flex items-center justify-between px-4 py-3">
              <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ slip.title || 'Payslip' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ slip.period || month }}</div>
              </div>
              <a
                :href="slip.url || '#'"
                class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              >
                Download
              </a>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </AppShell>
</template>
