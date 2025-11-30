<script setup>
import { ref, computed, onMounted } from 'vue'
import AppShell from '../layouts/AppShell.vue'
import { submitRenewal, requestRenewalQuote, fetchPlans, fetchRenewalQuotes, createRenewalQuote } from '../api'
import { useSession } from '../composables/useSession'

const slip = ref(null)
const notes = ref('')
const error = ref('')
const success = ref('')
const busy = ref(false)
const session = useSession()

const companyName = computed(() => session.currentCompany.value?.name || 'Your Company')
const plans = ref([])
const selectedPlan = ref(null)
const period = ref('monthly')
const quotes = ref([])
const loadingQuotes = ref(false)
const selectedQuote = ref(null)
const showQuoteModal = ref(false)

const selectedPlanObj = computed(() => plans.value.find((p) => p.id === selectedPlan.value) || null)
const subtotalDisplay = computed(() => {
  if (!selectedPlanObj.value) return null
  const price = period.value === 'yearly' ? selectedPlanObj.value.price_yearly : selectedPlanObj.value.price_monthly
  return Number(price || 0).toFixed(2)
})
const gstDisplay = computed(() => {
  if (!selectedPlanObj.value) return null
  const price = period.value === 'yearly' ? selectedPlanObj.value.price_yearly : selectedPlanObj.value.price_monthly
  const gstPercent = selectedPlanObj.value.gst_percent ?? 0
  return Number((price || 0) * (gstPercent / 100)).toFixed(2)
})
const totalDisplay = computed(() => {
  if (!selectedPlanObj.value) return null
  const price = period.value === 'yearly' ? selectedPlanObj.value.price_yearly : selectedPlanObj.value.price_monthly
  const gstPercent = selectedPlanObj.value.gst_percent ?? 0
  const gst = (price || 0) * (gstPercent / 100)
  return Number((price || 0) + gst).toFixed(2)
})

const loadPlans = async () => {
  try {
    const { data } = await fetchPlans()
    plans.value = data.data || []
    if (!selectedPlan.value && plans.value.length) {
      selectedPlan.value = plans.value[0].id
    }
  } catch (err) {
    console.error(err)
  }
}

const onFile = (e) => {
  slip.value = e.target.files?.[0] || null
}

const loadQuotes = async () => {
  loadingQuotes.value = true
  try {
    const { data } = await fetchRenewalQuotes()
    quotes.value = data.data || []
  } catch (err) {
    // ignore
  } finally {
    loadingQuotes.value = false
  }
}

const generateQuote = () => {
  error.value = ''
  if (!selectedPlan.value) {
    error.value = 'Select a plan first.'
    return
  }
  const form = { planId: selectedPlan.value, period: period.value }
  createRenewalQuote(form)
    .then((res) => {
      const inv = res.data?.data?.invoice
      if (inv) {
        selectedQuote.value = inv
        showQuoteModal.value = true
        // add to list if not present
        const exists = quotes.value.find((q) => q.id === inv.id)
        if (!exists) {
          quotes.value.unshift(inv)
        }
      }
    })
    .catch((err) => {
      error.value = err.response?.data?.message || 'Unable to generate quote.'
    })
}

const submit = async () => {
  error.value = ''
  success.value = ''
  if (!slip.value) {
    error.value = 'Please upload a payment slip (pdf/jpg/png).'
    return
  }
  if (!selectedQuote.value?.id) {
    error.value = 'Select a quote before submitting payment.'
    return
  }
  busy.value = true
  try {
    await submitRenewal({ slip: slip.value, notes: notes.value, quote_id: selectedQuote.value.id })
    success.value = 'Payment slip submitted. We will review and activate once verified.'
    slip.value = null
    notes.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to submit renewal.'
  } finally {
    busy.value = false
  }
}

const openQuote = () => {
  error.value = ''
  if (!selectedPlan.value) {
    error.value = 'Select a plan first.'
    return
  }
  generateQuote()
}

const openQuoteFromList = (q) => {
  selectedQuote.value = q
  showQuoteModal.value = true
}

onMounted(loadPlans)
onMounted(loadQuotes)
</script>

<template>
  <AppShell>
    <div class="space-y-4">
      <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900 dark:border-amber-900/30 dark:bg-amber-950/30 dark:text-amber-100">
        <p class="text-xs font-semibold uppercase tracking-wide">Subscription required</p>
        <h1 class="text-2xl font-semibold">Your subscription is inactive</h1>
        <p class="text-sm mt-1">Upload a payment slip to renew your plan. Access will be restored after verification.</p>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="space-y-3">
          <div class="grid gap-3 sm:grid-cols-2">
            <div>
              <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Plan</label>
              <select
                v-model="selectedPlan"
                class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              >
                <option v-for="p in plans" :key="p.id" :value="p.id">
                  {{ p.name }} — {{ p.price_monthly }} /mo
                </option>
              </select>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Period</label>
              <select
                v-model="period"
                class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white"
              >
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3">
            <button
              type="button"
              class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              :disabled="busy"
              @click="openQuote"
            >
              Generate quote
            </button>
            <div class="text-right space-y-1" v-if="subtotalDisplay">
              <div>
                <p class="text-2xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ subtotalDisplay }} {{ selectedPlanObj?.currency || 'MVR' }}
                </p>
              </div>
              <div>
                <p class="text-2xs uppercase tracking-wide text-gray-500 dark:text-gray-400">GST</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ gstDisplay }} {{ selectedPlanObj?.currency || 'MVR' }}
                </p>
              </div>
              <div>
                <p class="text-2xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ totalDisplay }} {{ selectedPlanObj?.currency || 'MVR' }}
                </p>
              </div>
            </div>
          </div>
          <span v-if="success" class="text-xs text-emerald-600 dark:text-emerald-300">{{ success }}</span>
          <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Generated quotes</h2>
          <span v-if="loadingQuotes" class="text-xs text-gray-500 dark:text-gray-400">Loading…</span>
        </div>
        <div class="mt-3 overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-300">
              <tr>
                <th class="px-3 py-2 text-left font-semibold">Quote #</th>
                <th class="px-3 py-2 text-left font-semibold">Plan</th>
                <th class="px-3 py-2 text-right font-semibold">Total</th>
                <th class="px-3 py-2 text-left font-semibold">Created</th>
                <th class="px-3 py-2 text-right font-semibold">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
              <tr v-for="q in quotes" :key="q.id" class="text-gray-900 dark:text-gray-100">
                <td class="px-3 py-2 font-medium">{{ q.number }}</td>
                <td class="px-3 py-2">{{ q.plan?.name }}</td>
                <td class="px-3 py-2 text-right">{{ Number(q.total_amount).toFixed(2) }} {{ q.currency || 'MVR' }}</td>
                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ new Date(q.created_at).toLocaleDateString() }}</td>
                <td class="px-3 py-2 text-right">
                  <button
                    type="button"
                    class="text-indigo-600 hover:text-indigo-500 text-sm font-semibold dark:text-indigo-300 dark:hover:text-indigo-200"
                    @click="openQuoteFromList(q)"
                  >
                    Open & submit payment
                  </button>
                </td>
              </tr>
              <tr v-if="!quotes.length && !loadingQuotes">
                <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                  No quotes yet. Generate one to see it here.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div
        v-if="selectedQuote && showQuoteModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
        @click.self="showQuoteModal = false"
      >
        <div class="w-full max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-gray-900">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quote</p>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ selectedQuote.number }}</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">For {{ companyName }}</p>
            </div>
            <button
              type="button"
              class="rounded-md px-3 py-1 text-sm font-semibold text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/5"
              @click="showQuoteModal = false"
            >
              Close
            </button>
          </div>

          <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-300">
                <tr>
                  <th class="px-3 py-2 text-left font-semibold">Item</th>
                  <th class="px-3 py-2 text-right font-semibold">Qty</th>
                  <th class="px-3 py-2 text-right font-semibold">Unit</th>
                  <th class="px-3 py-2 text-right font-semibold">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                <tr v-for="line in selectedQuote.lines || []" :key="line.id">
                  <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ line.description }}</td>
                  <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ line.qty }}</td>
                  <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">
                    {{ Number(line.unit_price).toFixed(2) }} {{ selectedQuote.currency || 'MVR' }}
                  </td>
                  <td class="px-3 py-2 text-right text-gray-900 dark:text-white">
                    {{ Number(line.total_amount).toFixed(2) }} {{ selectedQuote.currency || 'MVR' }}
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="px-3 pt-4 pb-1 text-right font-normal text-gray-700 dark:text-gray-300">Subtotal</th>
                  <td class="px-3 pt-4 pb-1 text-right font-semibold text-gray-900 dark:text-white">
                    {{ Number(selectedQuote.amount_ex_gst).toFixed(2) }} {{ selectedQuote.currency || 'MVR' }}
                  </td>
                </tr>
                <tr>
                  <th colspan="3" class="px-3 pb-1 text-right font-normal text-gray-700 dark:text-gray-300">GST</th>
                  <td class="px-3 pb-1 text-right font-semibold text-gray-900 dark:text-white">
                    {{ Number(selectedQuote.gst_amount).toFixed(2) }} {{ selectedQuote.currency || 'MVR' }}
                  </td>
                </tr>
                <tr>
                  <th colspan="3" class="px-3 pb-0 text-right font-semibold text-gray-900 dark:text-white">Total</th>
                  <td class="px-3 pb-0 text-right font-semibold text-gray-900 dark:text-white">
                    {{ Number(selectedQuote.total_amount).toFixed(2) }} {{ selectedQuote.currency || 'MVR' }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Payment slip (pdf/jpg/png)</label>
              <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm" @change="onFile" />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notes (optional)</label>
              <textarea v-model="notes" rows="3" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900 dark:text-white" placeholder="Include payment reference or contact info"></textarea>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-3">
            <button
              type="button"
              class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400"
              :disabled="busy"
              @click="submit"
            >
              {{ busy ? 'Submitting…' : 'Submit payment slip' }}
            </button>
            <span v-if="success" class="text-xs text-emerald-600 dark:text-emerald-300">{{ success }}</span>
            <span v-if="error" class="text-xs text-red-600 dark:text-red-300">{{ error }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
