<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchSelfLeaveBalance } from '../api'

const balances = ref(null)
const loading = ref(false)
const error = ref('')

async function loadBalance() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchSelfLeaveBalance()
    balances.value = data.data || {}
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Unable to load leave balances.'
  } finally {
    loading.value = false
  }
}

onMounted(loadBalance)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex flex-col gap-1">
          <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / My leave</p>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Leave balances</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Track your remaining leave by type.</p>
        </div>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="text-sm text-gray-600 dark:text-gray-300">Loading balances…</div>
        <div v-else-if="error" class="text-sm text-red-600 dark:text-red-200">{{ error }}</div>
        <div v-else class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Annual</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ balances?.annual ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">days remaining</p>
          </div>
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sick</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ balances?.sick ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">days remaining</p>
          </div>
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unpaid</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ balances?.unpaid ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">days available</p>
          </div>
        </div>
      </section>
    </div>
  </AppShell>
</template>
