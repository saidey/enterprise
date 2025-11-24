<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginRequest, fetchUser } from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await loginRequest({ email: email.value, password: password.value })

    const { data } = await fetchUser()

  router.push('select-company')
  } catch (e) {
    console.error(e)

    if (e.response?.data?.errors) {
      const first = Object.values(e.response.data.errors)[0][0]
      error.value = first
    } else if (e.response?.data?.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'Login failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <img class="mx-auto h-10 w-auto dark:hidden"
           src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" />
      <img class="mx-auto h-10 w-auto not-dark:hidden"
           src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" />

      <h2 class="mt-10 text-center text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        Sign in to your account
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
            Email address
          </label>
          <input
            v-model="email"
            type="email"
            required
            class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900
                   outline outline-1 outline-gray-300 placeholder-gray-400
                   focus:outline-2 focus:outline-indigo-600
                   dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder-gray-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
            Password
          </label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900
                   outline outline-1 outline-gray-300 placeholder-gray-400
                   focus:outline-2 focus:outline-indigo-600
                   dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder-gray-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5
                 text-sm font-semibold text-white shadow hover:bg-indigo-500
                 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600
                 dark:bg-indigo-500 dark:hover:bg-indigo-400"
        >
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </button>

        <p v-if="error" class="mt-2 text-center text-sm text-red-400">
          {{ error }}
        </p>
      </form>

      <p class="mt-10 text-center text-sm text-gray-500 dark:text-gray-400">
        Not a member?
        <router-link
          to="/register"
          class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
        >
          Create an account
        </router-link>
      </p>
    </div>
  </div>
</template>