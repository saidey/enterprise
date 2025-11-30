<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchAccountingSettings, updateAccountingSettings } from '../api'

const settings = ref({
  currency: 'USD',
  fiscal_year_start: '01-01',
  decimal_places: 2,
})
const loading = ref(false)
const error = ref('')
const success = ref('')

async function loadSettings() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchAccountingSettings()
    settings.value = data.data || data || settings.value
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load settings.'
  } finally {
    loading.value = false
  }
}

async function save() {
  error.value = ''
  success.value = ''
  try {
    await updateAccountingSettings(settings.value)
    success.value = 'Saved.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save settings.'
  }
}

onMounted(loadSettings)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Accounting</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Settings</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Configure currency and fiscal settings for this company.</p>
      </header>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Currency</label>
            <input v-model="settings.currency" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Fiscal year start (MM-DD)</label>
            <input v-model="settings.fiscal_year_start" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Decimal places</label>
            <input v-model.number="settings.decimal_places" type="number" min="0" max="4" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-3">
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            @click="save"
            :disabled="loading"
          >
            Save
          </button>
          <span v-if="success" class="text-xs text-emerald-600 dark:text-emerald-300">{{ success }}</span>
          <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
        </div>
      </div>
    </div>
  </AppShell>
</template>
