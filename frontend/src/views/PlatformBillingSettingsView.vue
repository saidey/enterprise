<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchBillingSettings, updateBillingSettings, fetchAllCompanies, fetchAdminOperationsByCompany } from '../api'

const settings = ref({ gst_percent: 0, invoice_prefix: 'INV', currency: 'MVR' })
const loading = ref(false)
const error = ref('')
const success = ref('')
const companies = ref([])
const operations = ref([])

async function loadSettings() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchBillingSettings()
    settings.value = data.data || data || { gst_percent: 0, invoice_prefix: 'INV' }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load settings.'
  } finally {
    loading.value = false
  }
}

async function loadCompanies() {
  try {
    const { data } = await fetchAllCompanies()
    companies.value = data.data || data || []
  } catch (err) {
    // ignore
  }
}

async function loadOperations() {
  if (!settings.value.seller_company_id) {
    operations.value = []
    return
  }
  try {
    const { data } = await fetchAdminOperationsByCompany(settings.value.seller_company_id)
    operations.value = data.data || data || []
  } catch (err) {
    operations.value = []
  }
}

async function save() {
  error.value = ''
  success.value = ''
  try {
    await updateBillingSettings(settings.value)
    success.value = 'Saved.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save settings.'
  }
}

onMounted(async () => {
  await Promise.all([loadSettings(), loadCompanies()])
  await loadOperations()
})
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Billing settings</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">GST and invoice settings for tenant billing.</p>
      </header>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">GST %</label>
            <input v-model.number="settings.gst_percent" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Currency</label>
            <input v-model="settings.currency" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm uppercase dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Invoice prefix</label>
            <input v-model="settings.invoice_prefix" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Seller company</label>
            <select
              v-model="settings.seller_company_id"
              class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              @change="loadOperations"
            >
              <option value="">Select company</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Seller operation</label>
            <select v-model="settings.seller_operation_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
              <option value="">Select operation</option>
              <option v-for="op in operations" :key="op.id" :value="op.id">{{ op.name }}</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-3">
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
            @click="save"
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
