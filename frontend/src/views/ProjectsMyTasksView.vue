<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import {
  fetchMyProjectTasks,
  updateProjectTask,
  fetchTaskAttachments,
  uploadTaskAttachment,
  deleteTaskAttachment,
  fetchTaskComments,
  createTaskComment,
} from '../api'

const tasks = ref([])
const loading = ref(false)
const error = ref('')
const success = ref('')
const activeTaskId = ref('')
const attachments = ref({})
const comments = ref({})
const commentDrafts = ref({})
const uploadingId = ref('')
const showModal = ref(false)

const taskStatuses = [
  { value: 'not_started', label: 'Not started' },
  { value: 'in_progress', label: 'In progress' },
  { value: 'pending_review', label: 'Pending review' },
  { value: 'completed', label: 'Completed' },
]

const fmtDate = (value) => {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(new Date(value))
  } catch (e) {
    return value
  }
}

async function loadTasks() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const { data } = await fetchMyProjectTasks()
    tasks.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load tasks.'
  } finally {
    loading.value = false
  }
}

async function setActiveTask(taskId) {
  activeTaskId.value = taskId
  await Promise.all([loadAttachments(taskId), loadComments(taskId)])
  showModal.value = true
}

async function loadAttachments(taskId) {
  attachments.value = { ...attachments.value, [taskId]: { loading: true, items: [] } }
  try {
    const { data } = await fetchTaskAttachments(taskId)
    attachments.value[taskId] = { loading: false, items: data.data || [] }
  } catch (err) {
    attachments.value[taskId] = { loading: false, items: [] }
  }
}

async function loadComments(taskId) {
  comments.value = { ...comments.value, [taskId]: { loading: true, items: [] } }
  try {
    const { data } = await fetchTaskComments(taskId)
    comments.value[taskId] = { loading: false, items: data.data || [] }
  } catch (err) {
    comments.value[taskId] = { loading: false, items: [] }
  }
}

async function submitForReview(task) {
  success.value = ''
  error.value = ''
  try {
    await updateProjectTask(task.id, {
      status: 'pending_review',
    })
    task.status = 'pending_review'
    success.value = 'Task submitted for review.'
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to update task.'
  }
}

async function updateStatus(task, status) {
  success.value = ''
  error.value = ''
  try {
    await updateProjectTask(task.id, { status })
    task.status = status
    success.value = 'Task updated.'
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to update task.'
  }
}

async function handleUpload(task, event) {
  const file = event.target.files?.[0]
  if (!file) return
  uploadingId.value = task.id
  error.value = ''
  success.value = ''
  try {
    await uploadTaskAttachment(task.id, file)
    await loadAttachments(task.id)
    success.value = 'Attachment uploaded.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to upload attachment.'
  } finally {
    uploadingId.value = ''
    event.target.value = ''
  }
}

async function removeAttachment(taskId, attachmentId) {
  success.value = ''
  error.value = ''
  try {
    await deleteTaskAttachment(attachmentId)
    await loadAttachments(taskId)
    success.value = 'Attachment removed.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete attachment.'
  }
}

async function addComment(taskId) {
  const body = commentDrafts.value[taskId]
  if (!body || !body.trim()) return
  success.value = ''
  error.value = ''
  try {
    await createTaskComment(taskId, { body })
    commentDrafts.value[taskId] = ''
    await loadComments(taskId)
    success.value = 'Comment added.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to add comment.'
  }
}

onMounted(loadTasks)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / My tasks</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My assigned tasks</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Update tasks assigned to you across all projects.</p>
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
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Task</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">WBS</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="flex items-center justify-between gap-2">
                    <div>
                      <div class="font-semibold">{{ task.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ task.phase?.name || '' }}</div>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ task.phase?.name || '' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.project?.name || '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  {{ task.due_date_human || fmtDate(task.due_date) }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="text-xs text-gray-600 dark:text-gray-300">
                    {{ task.wbs_item?.code ? `${task.wbs_item.code} — ${task.wbs_item.title}` : 'Unlinked' }}
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <span
                    :class="[
                      'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold',
                      task.status === 'completed'
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
                        : task.status === 'pending_review'
                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200'
                        : task.status === 'in_progress'
                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200'
                        : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-200',
                    ]"
                  >
                    {{ taskStatuses.find((s) => s.value === task.status)?.label || task.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                      <label class="text-xs text-gray-500 dark:text-gray-400">Status:</label>
                      <select
                        v-model="task.status"
                        class="rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-white/10 dark:bg-gray-900 dark:text-white"
                        :disabled="task.status === 'completed'"
                        @change="updateStatus(task, task.status)"
                      >
                        <option value="not_started">Not started</option>
                        <option value="in_progress">In progress</option>
                      </select>
                    </div>
                    <button
                      v-if="task.status !== 'pending_review' && task.status !== 'completed'"
                      type="button"
                      class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                      @click="submitForReview(task)"
                    >
                      Complete
                    </button>
                    <button
                      v-if="task.status === 'pending_review'"
                      type="button"
                      class="rounded-md bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-800 hover:bg-gray-300 dark:bg-white/10 dark:text-white"
                      disabled
                    >
                      Awaiting approval
                    </button>
                    <button
                      v-if="task.status === 'completed'"
                      type="button"
                      class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                      disabled
                    >
                      Completed
                    </button>
                    <button
                      type="button"
                      class="rounded-md bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-white"
                      @click="setActiveTask(task.id)"
                    >
                      Open Task
                    </button>
                    <button
                      v-if="task.status === 'pending_review'"
                      type="button"
                      class="rounded-md bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 hover:bg-slate-300 dark:bg-white/10 dark:text-white"
                      @click="updateStatus(task, 'in_progress')"
                    >
                      Undo submit
                    </button>
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

        <!-- Detail modal -->
        <div
          v-if="showModal && activeTaskId"
          class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4"
          @click.self="showModal = false"
        >
          <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Task details</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ tasks.find((t) => t.id === activeTaskId)?.name }}
                </p>
              </div>
              <button
                type="button"
                class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white"
                @click="showModal = false"
              >
                Close
              </button>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
              <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Attachments</h4>
                <div class="mt-2 space-y-2 rounded-lg border border-gray-100 p-3 dark:border-white/10 dark:bg-white/5">
                  <div v-if="attachments[activeTaskId]?.loading" class="text-xs text-gray-500 dark:text-gray-400">Loading attachments…</div>
                  <div v-else-if="!attachments[activeTaskId]?.items?.length" class="text-xs text-gray-500 dark:text-gray-400">No attachments yet.</div>
                  <ul v-else class="space-y-2">
                    <li
                      v-for="att in attachments[activeTaskId].items"
                      :key="att.id"
                      class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-white/5"
                    >
                      <div class="min-w-0">
                        <p class="truncate font-medium text-gray-900 dark:text-white">{{ att.original_name || att.path }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ att.mime_type || 'file' }}</p>
                      </div>
                      <div class="flex items-center gap-2">
                        <a
                          :href="att.url || `/storage/${att.path}`"
                          target="_blank"
                          rel="noreferrer"
                          class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                        >
                          Open
                        </a>
                        <button
                          type="button"
                          class="text-xs font-semibold text-red-600 hover:text-red-500 dark:text-red-400"
                          @click="removeAttachment(activeTaskId, att.id)"
                        >
                          Delete
                        </button>
                      </div>
                    </li>
                  </ul>
                  <label
                    class="mt-2 inline-flex cursor-pointer items-center gap-2 rounded-md border border-dashed border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:border-indigo-300 hover:text-indigo-600 dark:border-white/20 dark:text-gray-200 dark:hover:border-indigo-400"
                  >
                    <input type="file" class="hidden" :disabled="uploadingId === activeTaskId" @change="(e) => handleUpload(tasks.find((t) => t.id === activeTaskId), e)" />
                    <span>{{ uploadingId === activeTaskId ? 'Uploading…' : 'Upload file' }}</span>
                  </label>
                </div>
              </div>
              <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Comments</h4>
                <div class="mt-2 space-y-2 rounded-lg border border-gray-100 p-3 dark:border-white/10 dark:bg-white/5">
                  <div v-if="comments[activeTaskId]?.loading" class="text-xs text-gray-500 dark:text-gray-400">Loading comments…</div>
                  <div v-else-if="!comments[activeTaskId]?.items?.length" class="text-xs text-gray-500 dark:text-gray-400">No comments yet.</div>
                  <ul v-else class="space-y-2">
                    <li
                      v-for="c in comments[activeTaskId].items"
                      :key="c.id"
                      class="rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-white/5"
                    >
                      <div class="flex items-center justify-between">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ c.user?.name || c.user?.email || 'User' }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ c.created_at_human || fmtDate(c.created_at) }}</span>
                      </div>
                      <p class="text-sm text-gray-800 dark:text-gray-200">{{ c.body }}</p>
                    </li>
                  </ul>
                  <div class="mt-2 space-y-2">
                    <textarea
                      v-model="commentDrafts[activeTaskId]"
                      rows="2"
                      class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                      placeholder="Add a comment"
                    ></textarea>
                    <button
                      type="button"
                      class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                      @click="addComment(activeTaskId)"
                    >
                      Send
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
