<script setup>
import { ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { selfAttendanceCheck } from '../api'

const message = ref('')
const error = ref('')
const lastRecord = ref(null)
const loading = ref(false)

const checkNow = async () => {
  message.value = ''
  error.value = ''
  loading.value = true
  try {
    const { data } = await selfAttendanceCheck()
    lastRecord.value = data.data
    const hasCheckOut = !!lastRecord.value?.check_out
    message.value = hasCheckOut ? 'Checked out successfully.' : 'Checked in successfully.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to record attendance.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">HR / My attendance</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Check in / Check out</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Record your attendance instantly.</p>
          </div>
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
            :disabled="loading"
            @click="checkNow"
          >
            {{ loading ? 'Recording...' : 'Check now' }}
          </button>
        </div>
      </header>

      <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
          <div class="flex items-center justify-between">
            <span class="font-semibold">Last action</span>
            <span v-if="lastRecord" class="text-xs text-gray-500">{{ lastRecord.attendance_date }}</span>
          </div>
          <div v-if="lastRecord">
            <div>Check-in: {{ lastRecord.check_in || '—' }}</div>
            <div>Check-out: {{ lastRecord.check_out || '—' }}</div>
            <div>Status: {{ lastRecord.status || '—' }}</div>
          </div>
          <div v-else class="text-gray-500">No attendance recorded yet.</div>
        </div>
        <p v-if="message" class="mt-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200">
          {{ message }}
        </p>
        <p v-if="error" class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-200">
          {{ error }}
        </p>
      </section>
    </div>
  </AppShell>
</template>
