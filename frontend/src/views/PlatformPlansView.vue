<script setup>
import { onMounted, ref } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import api from '../api'

const plans = ref([])
const loading = ref(false)
const error = ref('')
const editing = ref(null)
const modalOpen = ref(false)

const emptyPlan = () => ({
  id: null,
  name: '',
  code: '',
  price_monthly: '',
  price_yearly: '',
  max_users: '',
  max_operations: '',
  included_modules: [],
  description: '',
  is_active: true,
  trial_days: 0,
})

const form = ref(emptyPlan())
const moduleOptions = [
  { code: 'hr', label: 'HR' },
  { code: 'accounting', label: 'Accounting' },
  { code: 'projects', label: 'Projects' },
  { code: 'admin', label: 'Admin' },
  { code: 'platform', label: 'Platform' },
]

const normalizeModules = (val) => {
  if (Array.isArray(val)) {
    return val.filter(Boolean)
  }
  if (typeof val === 'string') {
    return val
      .split(',')
      .map((s) => s.trim())
      .filter(Boolean)
  }
  return []
}

async function loadPlans() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/api/admin/plans')
    plans.value = data.data || data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load plans.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = emptyPlan()
  modalOpen.value = true
}

function openEdit(plan) {
  editing.value = plan.id
  form.value = { ...plan, included_modules: normalizeModules(plan.included_modules) }
  modalOpen.value = true
}

async function savePlan() {
  error.value = ''
  const payload = { ...form.value, included_modules: normalizeModules(form.value.included_modules) }
  try {
    if (editing.value) {
      await api.put(`/api/admin/plans/${editing.value}`, payload)
    } else {
      await api.post('/api/admin/plans', payload)
    }
    modalOpen.value = false
    await loadPlans()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save plan.'
  }
}

async function toggleActive(plan) {
  try {
    await api.put(`/api/admin/plans/${plan.id}`, { is_active: !plan.is_active })
    await loadPlans()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update plan.'
  }
}

onMounted(loadPlans)
</script>

<template>
  <AppShell>
    <div class="space-y-6">
      <header class="border-b border-gray-200 pb-4 dark:border-white/10">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">System administrator</p>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Plans</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage subscription plans.</p>
      </header>

      <div class="flex items-center justify-between gap-3">
        <div class="text-xs text-red-600 dark:text-red-300" v-if="error">{{ error }}</div>
        <button
          type="button"
          class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
          @click="openCreate"
        >
          New plan
        </button>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div v-if="loading" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">Loading plans…</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Code</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pricing</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Limits</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
              <tr v-for="p in plans" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                  <div class="font-semibold">{{ p.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.description || '—' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ p.code }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div>Monthly: {{ p.price_monthly ?? '—' }}</div>
                  <div>Yearly: {{ p.price_yearly ?? '—' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div>Users: {{ p.max_users ?? '∞' }}</div>
                  <div>Operations: {{ p.max_operations ?? '∞' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <span
                    :class="[
                      p.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-700 ring-gray-200',
                      'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset dark:bg-white/10 dark:text-white dark:ring-white/10'
                    ]"
                  >
                    {{ p.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="rounded-md bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 hover:bg-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-100 dark:ring-indigo-500/30"
                      @click="openEdit(p)"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                      class="rounded-md bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-200 dark:bg-white/10 dark:text-white dark:ring-white/10"
                      @click="toggleActive(p)"
                    >
                      {{ p.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!plans.length && !loading">
                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No plans found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="modalOpen = false">
        <div class="w-full max-w-2xl rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Plan</p>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ editing ? 'Edit plan' : 'New plan' }}</h2>
            </div>
            <button
              type="button"
              class="rounded-md bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-900 hover:bg-gray-200 dark:bg-white/10 dark:text-white dark:hover:bg-white/20"
              @click="modalOpen = false"
            >
              Close
            </button>
          </div>
          <div class="max-h-[70vh] space-y-3 overflow-y-auto p-4">
            <div class="grid gap-3 md:grid-cols-2">
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Name</label>
                <input v-model="form.name" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Code</label>
                <input v-model="form.code" type="text" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Price monthly</label>
                <input v-model.number="form.price_monthly" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Price yearly</label>
                <input v-model.number="form.price_yearly" type="number" step="0.01" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Max users</label>
                <input v-model.number="form.max_users" type="number" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Max operations</label>
                <input v-model.number="form.max_operations" type="number" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Trial days (initial only)</label>
                <input v-model.number="form.trial_days" type="number" min="0" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" />
              </div>
              <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Description</label>
                <textarea v-model="form.description" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Included modules</label>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                  <label
                    v-for="mod in moduleOptions"
                    :key="mod.code"
                    class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-800/40"
                  >
                    <input
                      v-model="form.included_modules"
                      :value="mod.code"
                      type="checkbox"
                      class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-gray-900"
                    />
                    <span class="text-gray-900 dark:text-white">{{ mod.label }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ mod.code }}</span>
                  </label>
                </div>
              </div>
              <div class="flex items-center gap-2 md:col-span-2">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-gray-900" />
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-200">Active</label>
              </div>
            </div>
            <div class="flex items-center justify-end gap-3">
              <button
                type="button"
                class="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-200 dark:bg-white/10 dark:text-white dark:hover:bg-white/20"
                @click="modalOpen = false"
              >
                Cancel
              </button>
              <button
                type="button"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                @click="savePlan"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
