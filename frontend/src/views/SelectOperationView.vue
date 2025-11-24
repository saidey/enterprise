<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { fetchMyOperations, setCurrentOperation } from '../api'
import { resetCachedUser } from '../router'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref(null)
const operations = ref([])

const loadOperations = async () => {
  loading.value = true
  error.value = null

  try {
    const { data } = await fetchMyOperations()
    operations.value = data.data || data || []
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load your operations.'
  } finally {
    loading.value = false
  }
}

const handleSelect = async (operation) => {
  try {
    await setCurrentOperation(operation.id)

    // Make router re-fetch fresh user context (with operation)
    resetCachedUser()

    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (e) {
    console.error(e)
    error.value = 'Failed to select operation.'
  }
}

const goToCreate = () => {
  router.push({ name: 'operations-create' })
}

onMounted(loadOperations)
</script>

<template>
  <AppShell>
    <div class="mx-auto max-w-5xl">
      <!-- Page header -->
      <header class="border-b border-gray-200 pb-6 dark:border-white/10">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
          Select an operation
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Choose which operation of this company you want to work in (for example, head office,
          resort, branch, or store).
        </p>
      </header>

      <!-- Content -->
      <section class="mt-8">
        <!-- Loading -->
        <div v-if="loading" class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-600 dark:border-white/10 dark:bg-gray-900/50 dark:text-gray-300">
          Loading operations…
        </div>

        <!-- Loaded -->
        <div v-else class="space-y-6">
          <!-- If no operations -->
          <div
            v-if="operations.length === 0"
            class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm dark:border-white/10 dark:bg-gray-900"
          >
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
              No operations found
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              You don’t have any operations set up for this company yet.
              Create your first operation to continue.
            </p>
            <button
              type="button"
              class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
              @click="goToCreate"
            >
              Create operation
            </button>
          </div>

          <!-- Operations grid + create card -->
          <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Operation cards -->
            <button
              v-for="operation in operations"
              :key="operation.id"
              type="button"
              class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-px hover:border-indigo-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900"
              @click="handleSelect(operation)"
            >
              <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ operation.name }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  {{ operation.code || 'No code' }}
                </p>
                <p
                  v-if="operation.description"
                  class="mt-3 line-clamp-3 text-xs text-gray-600 dark:text-gray-400"
                >
                  {{ operation.description }}
                </p>
              </div>

              <div class="mt-4 flex items-center justify-between">
                <span
                  v-if="operation.type"
                  class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                >
                  {{ operation.type }}
                </span>
                <span class="ml-auto inline-flex items-center text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                  Use this operation
                  <span aria-hidden="true" class="ml-1">→</span>
                </span>
              </div>
            </button>

            <!-- Create new operation card (always visible) -->
            <div
              class="flex flex-col justify-between rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm dark:border-white/15 dark:bg-gray-900/40"
            >
              <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                  Create a new operation
                </h2>
                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                  For example “Head Office”, “Resort A”, “Retail Store #1”, or
                  “Workshop”.
                </p>
              </div>
              <button
                type="button"
                class="mt-4 inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-900 shadow-xs inset-ring-1 inset-ring-gray-300 hover:bg-gray-100 dark:bg-white/10 dark:text-white dark:shadow-none dark:inset-ring-white/10 dark:hover:bg-white/20"
                @click="goToCreate"
              >
                + New operation
              </button>
            </div>
          </div>
        </div>

        <!-- Error -->
        <p v-if="error" class="mt-4 text-sm text-red-500">
          {{ error }}
        </p>
      </section>
    </div>
  </AppShell>
</template>