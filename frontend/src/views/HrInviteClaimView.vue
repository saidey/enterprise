<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AppShell from '../layouts/AppShell.vue'
import { claimEmployeeInvite } from '../api'

const route = useRoute()
const token = ref(route.query.token || '')
const loading = ref(false)
const message = ref('')
const error = ref('')

async function submit() {
  if (!token.value) {
    error.value = 'Enter an invite token.'
    return
  }
  loading.value = true
  message.value = ''
  error.value = ''
  try {
    const { data } = await claimEmployeeInvite(token.value)
    message.value = data.message || 'Successfully linked to your employee record.'
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Unable to claim invite.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (token.value) {
    submit()
  }
})
</script>

<template>
  <AppShell>
    <div class="mx-auto max-w-2xl space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / Claim invite</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Link your employee profile</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Paste the invite token you received to connect your user account to the employee record.
        </p>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Invite token</label>
        <input
          v-model="token"
          type="text"
          placeholder="Paste invite token"
          class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:focus:border-indigo-400"
        />
        <button
          type="button"
          class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
          :disabled="loading"
          @click="submit"
        >
          {{ loading ? 'Linking…' : 'Claim invite' }}
        </button>

        <p v-if="message" class="mt-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200">
          {{ message }}
        </p>
        <p v-if="error" class="mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-200">
          {{ error }}
        </p>
      </section>
    </div>
  </AppShell>
</template>
