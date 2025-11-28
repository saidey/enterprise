<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchPendingProjectTasks, updateProjectTask } from '../api'

const loading = ref(false)
const error = ref('')
const success = ref('')
const tasks = ref([])

async function loadTasks() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const { data } = await fetchPendingProjectTasks()
    tasks.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load approvals.'
  } finally {
    loading.value = false
  }
}

async function approve(task) {
  success.value = ''
  error.value = ''
  try {
    await updateProjectTask(task.id, { status: 'completed' })
    success.value = 'Task approved.'
    tasks.value = tasks.value.filter((t) => t.id !== task.id)
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to approve task.'
  }
}

async function sendBack(task) {
  success.value = ''
  error.value = ''
  try {
    await updateProjectTask(task.id, { status: 'in_progress' })
    success.value = 'Task sent back to assignee.'
    tasks.value = tasks.value.filter((t) => t.id !== task.id)
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to send back task.'
  }
}

onMounted(loadTasks)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / Approvals</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Task approvals</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Review and approve tasks submitted by team members.</p>
      </header>

      <div v-if="loading" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
        Loading approvals…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-950/40 dark:text-red-200">
        {{ error }}
      </div>
      <div v-else class="space-y-3">
        <div v-if="success" class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-200">
          {{ success }}
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-white/10">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Task</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">WBS</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ task.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Submitted by: {{ task.assigned_to_name || '—' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.project?.name || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.due_date || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="text-xs text-gray-600 dark:text-gray-300">
                    {{ task.wbs_item?.code ? `${task.wbs_item.code} — ${task.wbs_item.title}` : 'Unlinked' }}
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                      @click="approve(task)"
                    >
                      Approve
                    </button>
                    <button
                      type="button"
                      class="rounded-md bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 hover:bg-slate-300 dark:bg-white/10 dark:text-white"
                      @click="sendBack(task)"
                    >
                      Send back
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!tasks.length">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No tasks awaiting approval.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
