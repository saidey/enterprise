<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { createCompany } from '../api'

const router = useRouter()
const name = ref('')
const loading = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await createCompany({ name: name.value })
    router.push('/') // new company becomes current on backend
  } catch (e) {
    console.error(e)
    error.value = 'Failed to create company.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="max-w-md">
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
        Create a company
      </h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        This will be your business workspace. You can invite others later.
      </p>

      <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            Company name
          </label>
          <input
            v-model="name"
            type="text"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-white"
          />
        </div>

        <button
          type="submit"
          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
          :disabled="loading"
        >
          {{ loading ? 'Creating…' : 'Create company' }}
        </button>

        <p v-if="error" class="mt-2 text-sm text-red-400">
          {{ error }}
        </p>
      </form>
    </div>
  </AppShell>
</template>