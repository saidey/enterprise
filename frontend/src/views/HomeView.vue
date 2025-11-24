<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { useSession } from '../composables/useSession'

const router = useRouter()
const { setCurrentApp, currentCompany, currentOperation, modules } = useSession()

const contextLabel = computed(() => {
  if (!currentCompany.value || !currentOperation.value) return 'Select a company and operation to continue.'
  return `${currentCompany.value.name} / ${currentOperation.value.name}`
})

const availableApps = computed(() => {
  const enabled = new Set((modules.value || []).map((m) => m.code))

  const base = []

  if (enabled.has('hr')) {
    base.push({
      key: 'hr',
      name: 'HR',
      description: 'Manage employees, leave, and people operations.',
      to: '/apps/hr',
      badge: 'People',
    })
  }

  if (enabled.has('accounting')) {
    base.push({
      key: 'accounting',
      name: 'Accounting',
      description: 'Journals, ledgers, and finance reporting.',
      to: '/apps/accounting',
      badge: 'Finance',
    })
  }

  base.push({
    key: 'admin',
    name: 'Admin',
    description: 'Permissions, audit logs, and organizational settings.',
    to: '/admin',
    badge: 'Admin',
  })

  return base
})

const handleSelectApp = (app) => {
  setCurrentApp(app.key)
  router.push(app.to)
}
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-base font-semibold text-gray-900 dark:text-white">
            Apps dashboard
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Choose an app to work in for {{ contextLabel }}.
          </p>
        </div>
        <div class="rounded-md bg-gray-50 px-3 py-2 text-xs font-medium text-gray-700 dark:bg-white/5 dark:text-gray-200">
          Current context: {{ contextLabel }}
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <button
          v-for="app in availableApps"
          :key="app.key"
          type="button"
          class="flex flex-col items-start rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-px hover:border-indigo-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900"
          @click="handleSelectApp(app)"
        >
          <div class="flex w-full items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
              {{ app.badge }}
            </span>
            <span class="text-[11px] text-gray-400 dark:text-gray-500">Open</span>
          </div>
          <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
            {{ app.name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ app.description }}
          </p>
        </button>
      </div>
    </div>
  </AppShell>
</template>
