<script setup>
import AppShell from '../layouts/AppShell.vue'
</script>

<template>
  <AppShell>
    <div class="space-y-4">
      <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-900/30 dark:bg-indigo-950/20">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Platform admin</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">System Administrator</h1>
        <p class="text-sm text-gray-700 dark:text-gray-300">Superadmin / platform admin controls (Horizon, Pulse).</p>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Queues (Horizon)</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">Monitor jobs and workers.</p>
          <a href="/horizon" class="mt-2 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            Open Horizon
          </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pulse</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400">View application telemetry.</p>
          <a href="/pulse" class="mt-2 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            Open Pulse
          </a>
        </div>
      </div>
    </div>
  </AppShell>
</template>
