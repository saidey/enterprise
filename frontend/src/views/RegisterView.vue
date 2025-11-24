<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { registerRequest, fetchUser } from '../api'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

const loading = ref(false)
const error = ref(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await registerRequest({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    const { data } = await fetchUser()
    console.log('Registered user:', data)

    router.push('/') // redirect wherever makes sense
  } catch (e) {
    console.error(e)
    if (e.response?.data?.errors) {
      const first = Object.values(e.response.data.errors)[0][0]
      error.value = first
    } else if (e.response?.data?.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 dark:text-white">
        Create your account
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
            Name
          </label>
          <div class="mt-2">
            <input
              v-model="name"
              type="text"
              required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1
                     -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2
                     focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6
                     dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500
                     dark:focus:outline-indigo-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
            Email address
          </label>
          <div class="mt-2">
            <input
              v-model="email"
              type="email"
              autocomplete="email"
              required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1
                     -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2
                     focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6
                     dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500
                     dark:focus:outline-indigo-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
            Password
          </label>
          <div class="mt-2">
            <input
              v-model="password"
              type="password"
              autocomplete="new-password"
              required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1
                     -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2
                     focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6
                     dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500
                     dark:focus:outline-indigo-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
            Confirm password
          </label>
          <div class="mt-2">
            <input
              v-model="passwordConfirmation"
              type="password"
              autocomplete="new-password"
              required
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1
                     -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2
                     focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6
                     dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500
                     dark:focus:outline-indigo-500"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6
                   font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2
                   focus-visible:outline-offset-2 focus-visible:outline-indigo-600
                   dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400
                   dark:focus-visible:outline-indigo-500"
            :disabled="loading"
          >
            {{ loading ? 'Creating account…' : 'Sign up' }}
          </button>
        </div>

        <p v-if="error" class="mt-2 text-center text-sm text-red-400">
          {{ error }}
        </p>
      </form>

      <p class="mt-10 text-center text-sm/6 text-gray-500 dark:text-gray-400">
        Already have an account?
        <router-link
          to="/login"
          class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
        >
          Sign in
        </router-link>
      </p>
    </div>
  </div>
</template>