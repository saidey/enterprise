<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchIslands, createIsland, updateIsland, deleteIsland } from '../api'

const islands = ref([])
const loading = ref(false)
const error = ref('')
const form = ref({
  name: '',
  atoll: '',
  notes: '',
})
const message = ref('')

async function loadIslands() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await fetchIslands()
    islands.value = data.data || []
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to load islands.'
  } finally {
    loading.value = false
  }
}

async function submit() {
  message.value = ''
  error.value = ''
  try {
    await createIsland(form.value)
    message.value = 'Island saved.'
    Object.assign(form.value, { name: '', atoll: '', notes: '' })
    await loadIslands()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Failed to save island.'
  }
}

async function saveInline(island) {
  try {
    await updateIsland(island.id, {
      name: island.name,
      atoll: island.atoll,
      notes: island.notes,
    })
  } catch (err) {
    console.error(err)
  }
}

async function removeIsland(id) {
  if (!confirm('Delete this island?')) return
  try {
    await deleteIsland(id)
    await loadIslands()
  } catch (err) {
    console.error(err)
  }
}

onMounted(loadIslands)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Projects / Islands</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Islands</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage islands for your projects.</p>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Add island</h2>
        <form class="mt-3 grid gap-3 md:grid-cols-2" @submit.prevent="submit">
          <div class="md:col-span-1">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Name</label>
            <input v-model="form.name" required type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-1">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Atoll / City</label>
            <input v-model="form.atoll" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notes</label>
            <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
          </div>
          <div class="md:col-span-2 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400">
              Save
            </button>
            <span v-if="message" class="text-xs text-emerald-600 dark:text-emerald-300">{{ message }}</span>
            <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
          </div>
        </form>
      </section>

      <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Islands list</h2>
            <button
              type="button"
              class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
              @click="loadIslands"
          >
            Refresh
          </button>
        </div>
        <div v-if="loading" class="border-t border-gray-200 px-4 py-3 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">
          Loading islands…
        </div>
        <div v-else class="overflow-hidden border-t border-gray-200 dark:border-white/10">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atoll / City</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Notes</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="island in islands" :key="island.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <input
                    v-model="island.name"
                    type="text"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    @change="saveInline(island)"
                  />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <input
                    v-model="island.atoll"
                    type="text"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    @change="saveInline(island)"
                  />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <textarea
                    v-model="island.notes"
                    rows="2"
                    class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    @change="saveInline(island)"
                  ></textarea>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    type="button"
                    class="rounded-md bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm hover:bg-red-200 dark:bg-red-900/30 dark:text-red-200"
                    @click="removeIsland(island.id)"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!islands.length">
                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  No islands yet. Add one above.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppShell>
</template>
