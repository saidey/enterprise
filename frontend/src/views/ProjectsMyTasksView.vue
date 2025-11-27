<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchMyWbs, updateWbs } from '../api'

const tasks = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')

const taskStatuses = ['not_started', 'in_progress', 'completed']

async function loadTasks() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const { data } = await fetchMyWbs()
    tasks.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load tasks.'
  } finally {
    loading.value = false
  }
}

async function saveTask(task) {
  success.value = ''
  error.value = ''
  try {
    await updateWbs(task.id, {
      status: task.status,
      quantity_total: task.quantity_total,
      quantity_completed: task.quantity_completed,
    })
    success.value = 'Task updated.'
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to update task.'
  }
}

onMounted(loadTasks)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / My tasks</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My project tasks</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Update your assigned tasks across all projects.</p>
      </header>

      <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
        Loading tasks…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-else class="space-y-3">
        <div v-if="success" class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
          {{ success }}
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">WBS item</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty (done/total)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ task.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ task.phase?.name || '' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.project?.name || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.end_date || task.due_date || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <select v-model="task.status" class="rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-white/10 dark:bg-gray-900 dark:text-white" @change="saveTask(task)">
                    <option v-for="s in taskStatuses" :key="s" :value="s">{{ s }}</option>
                  </select>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="task.quantity_completed"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-20 rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-white/10 dark:bg-gray-900 dark:text-white"
                      @change="saveTask(task)"
                    />
                    <span class="text-xs text-gray-500 dark:text-gray-400">/</span>
                    <input
                      v-model="task.quantity_total"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-20 rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-white/10 dark:bg-gray-900 dark:text-white"
                      @change="saveTask(task)"
                    />
                  </div>
                </td>
              </tr>
              <tr v-if="!tasks.length">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No tasks assigned to you yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
