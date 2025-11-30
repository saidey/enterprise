<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { apiInstance } from '../api'
import { fetchBillingSettings } from '../api'

const apiBase = computed(() => {
  const base = apiInstance?.defaults?.baseURL || ''
  if (base) {
    return base.replace(/\/+$/, '')
  }
  return window.location.origin
})

const billingSettings = ref(null)
const needsSellerSetup = computed(() => {
  return !billingSettings.value || !billingSettings.value.seller_company_id
})

onMounted(async () => {
  try {
    const { data } = await fetchBillingSettings()
    billingSettings.value = data.data || data || null
  } catch (err) {
    billingSettings.value = null
  }
})
</script>

<template>
  <AppShell>
    <div class="space-y-4">
      <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-900/30 dark:bg-indigo-950/20">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Platform admin</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">System Administrator</h1>
        <p class="text-sm text-gray-700 dark:text-gray-300">Superadmin / platform admin controls (Horizon, Pulse).</p>
      </div>

      <div
        v-if="needsSellerSetup"
        class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/30 dark:text-amber-100"
      >
        <div class="font-semibold">Seller company not configured</div>
        <p class="mt-1">Set the platform seller company/operation in Billing settings before generating invoices.</p>
        <router-link
          to="/administrator/billing-settings"
          class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
        >
          Open Billing settings
        </router-link>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Queues (Horizon)</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Monitor jobs and workers.</p>
          <a :href="`${apiBase}/horizon`" class="mt-2 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            Open Horizon
          </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pulse</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">View application telemetry.</p>
          <a :href="`${apiBase}/pulse`" class="mt-2 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            Open Pulse
          </a>
        </div>
      </div>
    </div>
  </AppShell>
</template>
