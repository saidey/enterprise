<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { fetchAdminSubscriptions, updateAdminSubscription, fetchAdminPlans } from '../api'

const companies = ref([])
const plans = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')
const saving = ref({})

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return companies.value
  return companies.value.filter((c) => {
    return (
      (c.name || '').toLowerCase().includes(q) ||
      (c.slug || '').toLowerCase().includes(q) ||
      (c.subscription_status || '').toLowerCase().includes(q) ||
      (c.subscription?.plan_name || '').toLowerCase().includes(q)
    )
  })
})

async function loadData() {
  loading.value = true
  error.value = ''
  try {
    const [{ data: subs }, { data: planData }] = await Promise.all([
      fetchAdminSubscriptions(),
      fetchAdminPlans(),
    ])
    companies.value = subs.data || subs || []
    plans.value = planData.data || planData || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load subscriptions.'
  } finally {
    loading.value = false
  }
}

async function save(company) {
  saving.value = { ...saving.value, [company.id]: true }
  error.value = ''
  try {
    await updateAdminSubscription(company.id, {
      plan_id: company.subscription?.plan_id || null,
      status: company.subscription?.status || 'active',
      billing_cycle: company.subscription?.billing_cycle || 'monthly',
      trial_ends_at: company.subscription?.trial_ends_at || null,
      current_period_start: company.subscription?.current_period_start || null,
      current_period_end: company.subscription?.current_period_end || null,
      next_billing_at: company.subscription?.next_billing_at || null,
    })
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update subscription.'
  } finally {
    saving.value = { ...saving.value, [company.id]: false }
  }
}

onMounted(loadData)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Subscriptions</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage tenant subscriptions and plans.</p>
      </header>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
          v-model="search"
          type="search"
          placeholder="Search by company, plan, status"
          class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white sm:w-96"
        />
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200"
            @click="loadData"
            :disabled="loading"
          >
            Refresh
          </button>
          <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">Loading subscriptions…</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Billing</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Trial ends</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Next billing</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="c in filtered" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ c.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ c.slug }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <select v-model="(c.subscription ||= {}).plan_id" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                    <option :value="null">None</option>
                    <option v-for="p in plans" :key="p.id" :value="p.id">
                      {{ p.name }} ({{ p.code }})
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <select v-model="(c.subscription ||= {}).billing_cycle" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <select v-model="(c.subscription ||= {}).status" class="rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white">
                    <option value="active">Active</option>
                    <option value="trialing">Trialing</option>
                    <option value="past_due">Past due</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <input v-model="(c.subscription ||= {}).trial_ends_at" type="date" class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <input v-model="(c.subscription ||= {}).next_billing_at" type="date" class="w-full rounded-md border border-gray-200 px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
                </td>
                <td class="px-4 py-3 text-right text-sm">
                  <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    :disabled="saving[c.id]"
                    @click="save(c)"
                  >
                    {{ saving[c.id] ? 'Saving…' : 'Save' }}
                  </button>
                </td>
              </tr>
              <tr v-if="!filtered.length && !loading">
                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No companies found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>
