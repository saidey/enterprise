<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { createOperation } from '../api'
import { resetCachedUser } from '../router'

const router = useRouter()
const route = useRoute()

const form = ref({
  name: '',
  code: '',
  type: '',
  description: '',
})

const saving = ref(false)
const error = ref('')
const message = ref('')

const onSubmit = async () => {
  saving.value = true
  error.value = ''
  message.value = ''

  try {
    const { data } = await createOperation(form.value)

    message.value = data.message || 'Operation created successfully.'

    // Clear any cached user so router pulls fresh context next request
    resetCachedUser()

    // After creating, send user back to select-operation to manually choose
    const redirect = route.query.redirect || { name: 'select-operation' }
    setTimeout(() => {
      router.push(redirect)
    }, 500)
  } catch (e) {
    console.error(e)
    if (e.response?.data?.message) {
      error.value = e.response.data.message
    } else if (e.response?.data?.errors) {
      const first = Object.values(e.response.data.errors)[0][0]
      error.value = first
    } else {
      error.value = 'Failed to create operation.'
    }
  } finally {
    saving.value = false
  }
}

const cancel = () => {
  router.push({ name: 'select-operation' })
}
</script>

<template>
  <AppShell>
    <div class="mx-auto max-w-3xl">
      <!-- Header -->
      <header class="border-b border-gray-200 pb-6 dark:border-white/10">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
          Create operation
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Operations represent business units within the selected company, such as head office,
          branches, resorts, or departments.
        </p>
      </header>

      <!-- Form -->
      <section
        class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900"
      >
        <form class="space-y-6" @submit.prevent="onSubmit">
          <div class="grid gap-4 sm:grid-cols-2">
            <!-- Name -->
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                Operation name
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="e.g. Head Office, Resort A, Retail Store #1"
                class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
              />
            </div>

            <!-- Code -->
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                Code (optional)
              </label>
              <input
                v-model="form.code"
                type="text"
                placeholder="e.g. HO, RS1"
                class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
              />
            </div>

            <!-- Type -->
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                Type
              </label>
              <select
                v-model="form.type"
                class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500"
              >
                <option value="">Select type</option>
                <option value="head_office">Head office</option>
                <option value="branch">Branch</option>
                <option value="resort">Resort</option>
                <option value="store">Store</option>
                <option value="department">Department</option>
              </select>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
              Description (optional)
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Short description of this operation, for your team."
              class="mt-2 block w-full rounded-md bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-x-3 pt-2">
            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-60 disabled:cursor-not-allowed dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500"
            >
              {{ saving ? 'Creating…' : 'Create operation' }}
            </button>

            <button
              type="button"
              class="text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
              @click="cancel"
            >
              Cancel
            </button>
          </div>

          <!-- Messages -->
          <p v-if="message" class="mt-2 text-xs text-emerald-400">
            {{ message }}
          </p>
          <p v-if="error" class="mt-2 text-xs text-red-400">
            {{ error }}
          </p>
        </form>
      </section>
    </div>
  </AppShell>
</template>