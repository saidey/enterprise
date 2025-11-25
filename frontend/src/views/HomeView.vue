<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { useSession } from '../composables/useSession'
import { moduleRegistry } from '../constants/modules'

const router = useRouter()
const { setCurrentApp, currentCompany, currentOperation, modules } = useSession()

const contextLabel = computed(() => {
  if (!currentCompany.value || !currentOperation.value) return 'Select a company and operation to continue.'
  return `${currentCompany.value.name} / ${currentOperation.value.name}`
})

const canUseModule = (mod) => {
  const enabled = new Set((modules.value || []).map((m) => m.code))
  if (mod.requiredModuleCode && !enabled.has(mod.requiredModuleCode)) return false
  // If you wire permissions into session, check here as well
  return true
}

const availableApps = computed(() => {
  return moduleRegistry
    .filter((mod) => mod.key === 'admin' || canUseModule(mod))
    .map((mod) => ({
      key: mod.key,
      name: mod.name,
      description: mod.description,
      to: mod.route,
      badge: mod.badge,
    }))
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
