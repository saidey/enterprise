<script setup>
import AppShell from '../layouts/AppShell.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const cards = [
  {
    title: 'Permission assignment',
    description: 'Assign roles and direct permissions to users.',
    action: () => router.push({ name: 'permissions' }),
    cta: 'Open permissions',
  },
  {
    title: 'Audit logs',
    description: 'Review recent activity across the organization.',
    action: () => router.push({ name: 'audit-logs' }),
    cta: 'View audit logs',
  },
  {
    title: 'Company settings',
    description: 'Update registration, contact, and address details.',
    action: () => router.push({ name: 'settings-company' }),
    cta: 'Edit company',
  },
]
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header>
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Admin</p>
        <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">Admin workspace</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage permissions, audit logs, and organizational settings.
        </p>
      </header>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <button
          v-for="card in cards"
          :key="card.title"
          type="button"
          class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-px hover:border-indigo-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900"
          @click="card.action()"
        >
          <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
            <span>Admin</span>
            <span class="text-[11px] text-gray-400 dark:text-gray-500">Go</span>
          </div>
          <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
            {{ card.title }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ card.description }}
          </p>
          <span class="mt-4 inline-flex items-center text-xs font-semibold text-indigo-600 dark:text-indigo-400">
            {{ card.cta }} →
          </span>
        </button>
      </div>
    </div>
  </AppShell>
</template>
